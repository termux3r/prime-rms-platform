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
