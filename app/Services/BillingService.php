<?php

namespace App\Services;

use CodeIgniter\Config\Services;
use RuntimeException;

/**
 * Bill generation, payment recording, receipts.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Module 7 (Billing)
 * Implement the methods listed in that document's matching "Prompt" block (Section 8).
 * Wrap every multi-table write in $this->db->transStart() / transComplete().
 */
class BillingService
{
    /** Standard VAT rate applied to the subtotal. */
    public const TAX_RATE = 0.15;

    protected $db;
    protected $billModel;
    protected $orderModel;
    protected $orderItemModel;
    protected $tableStateService;
    protected $auditService;

    public function __construct()
    {
        $this->db                = db_connect();
        $this->billModel         = new \App\Models\BillModel();
        $this->orderModel        = new \App\Models\OrderModel();
        $this->orderItemModel    = new \App\Models\OrderItemModel();
        $this->tableStateService = new \App\Services\TableStateService();
        $this->auditService      = new \App\Services\AuditService();
    }

    /**
     * Generate a bill from a completed order.
     * Reject if order is not completed or if a bill already exists.
     */
    public function generateBill(int $orderId): array
    {
        $this->db->transStart();

        $order = $this->orderModel->find($orderId);
        if (! $order) {
            $this->db->transRollback();
            throw new RuntimeException('Order not found', 404);
        }

        if ($order['status'] !== 'completed') {
            $this->db->transRollback();
            throw new RuntimeException('Can only generate bill for completed orders', 409);
        }

        // Check if bill already exists
        $existingBill = $this->billModel->where('order_id', $orderId)->first();
        if ($existingBill) {
            $this->db->transRollback();
            throw new RuntimeException('Bill already exists for this order', 409);
        }

        $billId = $this->billModel->insert([
            'order_id'       => $orderId,
            'total_amount'   => $order['total_amount'],
            'payment_status' => 'unpaid',
        ]);

        if (! $billId) {
            $this->db->transRollback();
            throw new RuntimeException('Failed to generate bill', 500);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Failed to generate bill', 500);
        }

        return $this->billModel->find($billId);
    }

    /**
     * Build a full itemized bill summary for an order (read-only).
     * Computes subtotal from all items, applies the standard VAT rate, and
     * returns the grand total. Works whether or not a bills row exists yet.
     */
    public function summarizeOrder(int $orderId): array
    {
        $order = $this->orderModel->find($orderId);
        if (! $order) {
            throw new RuntimeException('Order not found', 404);
        }

        $items = $this->orderItemModel
            ->select('order_items.*, menu_items.name as menu_item_name')
            ->join('menu_items', 'menu_items.id = order_items.menu_item_id', 'left')
            ->where('order_items.order_id', $orderId)
            ->findAll();

        $subtotal = 0.0;
        foreach ($items as &$item) {
            $lineTotal       = (float) $item['unit_price'] * (int) $item['quantity'];
            $item['subtotal'] = round($lineTotal, 2);
            $subtotal        += $lineTotal;
        }
        unset($item);

        $subtotal   = round($subtotal, 2);
        $tax        = round($subtotal * self::TAX_RATE, 2);
        $grandTotal = round($subtotal + $tax, 2);

        return [
            'order_id'     => $orderId,
            'table_id'     => $order['table_id'],
            'order_status' => $order['status'],
            'items'        => $items,
            'subtotal'     => $subtotal,
            'tax_rate'     => self::TAX_RATE,
            'tax'          => $tax,
            'grand_total'  => $grandTotal,
        ];
    }

    /**
     * Process final payment for an order (POST /orders/{id}/pay).
     *
     * Ensures a bill exists (creating it if needed with the tax-inclusive
     * grand total), records the payment, flips the order to 'paid', then
     * marks the table available. All in one transaction.
     */
    public function payOrder(int $orderId, string $method, ?int $cashierId = null): array
    {
        $this->db->transStart();

        $order = $this->orderModel->find($orderId);
        if (! $order) {
            $this->db->transRollback();
            throw new RuntimeException('Order not found', 404);
        }

        if (! in_array($method, ['cash', 'card', 'mobile'], true)) {
            $this->db->transRollback();
            throw new RuntimeException('Invalid payment method', 422);
        }

        $summary = $this->summarizeOrder($orderId);
        $grandTotal = $summary['grand_total'];

        // Find or create the bill for this order
        $bill = $this->billModel->where('order_id', $orderId)->first();
        if (! $bill) {
            $billId = $this->billModel->insert([
                'order_id'       => $orderId,
                'total_amount'   => $grandTotal,
                'payment_status' => 'unpaid',
            ]);
            if (! $billId) {
                $this->db->transRollback();
                throw new RuntimeException('Failed to generate bill', 500);
            }
            $bill = $this->billModel->find($billId);
        }

        if ($bill['payment_status'] === 'paid') {
            $this->db->transRollback();
            throw new RuntimeException('Bill has already been paid', 409);
        }

        $now = date('Y-m-d H:i:s');

        // Persist the final tax-inclusive total on the bill
        $this->billModel->update($bill['id'], [
            'total_amount'   => $grandTotal,
            'payment_status' => 'paid',
            'payment_method' => $method,
            'paid_at'        => $now,
        ]);

        // Flip order to paid
        $this->orderModel->update($orderId, ['status' => 'paid', 'total_amount' => $grandTotal]);

        // Mark table available
        $this->tableStateService->markAvailable($order['table_id'], $orderId);

        $this->auditService->log(
            $cashierId ?? $this->getCurrentUserId(),
            'bill.paid',
            'bill',
            $bill['id'],
            ['order_id' => $orderId, 'amount' => $grandTotal, 'method' => $method]
        );

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Failed to record payment', 500);
        }

        return [
            'bill'     => $this->billModel->find($bill['id']),
            'summary'  => $summary,
        ];
    }

    /**
     * Record payment for a bill.
     * Reject with 409 if already paid (idempotency guard).
     * Flip order status to 'paid', mark table available, write audit log.
     */
    public function recordPayment(int $billId, string $method): array
    {
        $this->db->transStart();

        $bill = $this->billModel->find($billId);
        if (! $bill) {
            $this->db->transRollback();
            throw new RuntimeException('Bill not found', 404);
        }

        if ($bill['payment_status'] === 'paid') {
            $this->db->transRollback();
            throw new RuntimeException('Bill has already been paid', 409);
        }

        if (! in_array($method, ['cash', 'card', 'mobile'], true)) {
            $this->db->transRollback();
            throw new RuntimeException('Invalid payment method', 422);
        }

        $now = date('Y-m-d H:i:s');

        $this->billModel->update($billId, [
            'payment_status' => 'paid',
            'payment_method' => $method,
            'paid_at'        => $now,
        ]);

        // Update order status to 'paid'
        $this->orderModel->update($bill['order_id'], ['status' => 'paid']);

        // Mark table as available
        $order = $this->orderModel->find($bill['order_id']);
        if ($order) {
            $this->tableStateService->markAvailable($order['table_id']);
        }

        // Audit log
        $this->auditService->log(
            $this->getCurrentUserId(),
            'bill.paid',
            'bill',
            $billId,
            ['amount' => $bill['total_amount'], 'method' => $method]
        );

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Failed to record payment', 500);
        }

        return $this->billModel->find($billId);
    }

    /**
     * Build printable receipt payload.
     */
    public function buildReceipt(int $billId): array
    {
        $bill = $this->billModel->find($billId);
        if (! $bill) {
            throw new RuntimeException('Bill not found', 404);
        }

        $order = $this->orderModel->find($bill['order_id']);
        if (! $order) {
            throw new RuntimeException('Associated order not found', 404);
        }

        // Get order items with menu item names
        $items = $this->orderItemModel
            ->select('order_items.*, menu_items.name as menu_item_name')
            ->join('menu_items', 'menu_items.id = order_items.menu_item_id', 'left')
            ->where('order_items.order_id', $order['id'])
            ->findAll();

        // Restaurant info (could come from config)
        $restaurant = [
            'name'    => env('APP_NAME') ?: 'Restaurant Management System',
            'address' => env('RESTAURANT_ADDRESS') ?: '',
            'phone'   => env('RESTAURANT_PHONE') ?: '',
        ];

        return [
            'restaurant'    => $restaurant,
            'bill'          => [
                'id'              => $bill['id'],
                'order_id'        => $bill['order_id'],
                'total_amount'    => $bill['total_amount'],
                'payment_status'  => $bill['payment_status'],
                'payment_method'  => $bill['payment_method'],
                'paid_at'         => $bill['paid_at'],
                'created_at'      => $bill['created_at'],
            ],
            'order'         => [
                'id'           => $order['id'],
                'table_id'      => $order['table_id'],
                'cashier_id'    => $order['cashier_id'],
                'status'        => $order['status'],
                'total_amount'  => $order['total_amount'],
                'created_at'    => $order['created_at'],
            ],
            'items'         => $items,
        ];
    }

    /**
     * Get current user ID from auth context.
     * In a real app, this would come from the request context.
     */
    protected function getCurrentUserId(): ?int
    {
        // This is a simplified approach - in reality you'd get this from the JWT filter
        // For now, we'll try to get it from the request if available
        $request = service('request');
        return $request->userId ?? null;
    }
}
