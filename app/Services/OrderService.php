<?php

namespace App\Services;

use CodeIgniter\Config\Services;
use RuntimeException;

/**
 * Order + line-item lifecycle, business rules.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Module 6 (Order Management)
 * Implement the methods listed in that document's matching "Prompt" block (Section 8).
 * Wrap every multi-table write in $this->db->transStart() / transComplete().
 */
class OrderService
{
    protected $db;
    protected $orderModel;
    protected $orderItemModel;
    protected $menuItemModel;
    protected $tableStateService;
    protected $billModel;
    protected $auditService;

    public function __construct()
    {
        $this->db                = db_connect();
        $this->orderModel        = new \App\Models\OrderModel();
        $this->orderItemModel    = new \App\Models\OrderItemModel();
        $this->menuItemModel     = new \App\Models\MenuItemModel();
        $this->tableStateService = new \App\Services\TableStateService();
        $this->billModel         = new \App\Models\BillModel();
        $this->auditService      = new \App\Services\AuditService();
    }

    /**
     * Create a new order for a table, mark table as occupied.
     */
    public function createOrder(int $tableId, int $cashierId): array
    {
        $this->db->transStart();

        // Check if table already has an active order
        $existingOrder = $this->orderModel
            ->where('table_id', $tableId)
            ->whereIn('status', ['pending', 'completed'])
            ->first();

        if ($existingOrder) {
            $this->db->transRollback();
            throw new RuntimeException('Table already has an active order', 409);
        }

        $orderId = $this->orderModel->insert([
            'table_id'     => $tableId,
            'cashier_id'   => $cashierId,
            'status'       => 'pending',
            'total_amount' => 0.00,
        ]);

        if (! $orderId) {
            $this->db->transRollback();
            throw new RuntimeException('Failed to create order', 500);
        }

        // Mark table as occupied
        $this->tableStateService->markOccupied($tableId);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Failed to create order', 500);
        }

        return $this->orderModel->find($orderId);
    }

    /**
     * Add a menu item to an order.
     * Reject if menu item is not active.
     * Snapshot current price, compute subtotal, recalc order total via SQL.
     */
    public function addItem(int $orderId, int $menuItemId, int $quantity): array
    {
        $this->db->transStart();

        $order = $this->orderModel->find($orderId);
        if (! $order) {
            $this->db->transRollback();
            throw new RuntimeException('Order not found', 404);
        }

        if ($order['status'] !== 'pending') {
            $this->db->transRollback();
            throw new RuntimeException('Cannot add items to a non-pending order', 409);
        }

        // Check if bill exists and is paid (immutability)
        $bill = $this->billModel->where('order_id', $orderId)->first();
        if ($bill && $bill['payment_status'] === 'paid') {
            $this->db->transRollback();
            throw new RuntimeException('Cannot modify an order that has been paid', 409);
        }

        $menuItem = $this->menuItemModel->find($menuItemId);
        if (! $menuItem) {
            $this->db->transRollback();
            throw new RuntimeException('Menu item not found', 404);
        }

        if ($menuItem['status'] !== 'active') {
            $this->db->transRollback();
            throw new RuntimeException('Cannot add inactive menu item to order', 422);
        }

        $unitPrice = $menuItem['price'];
        $subtotal  = $unitPrice * $quantity;

        $this->orderItemModel->insert([
            'order_id'     => $orderId,
            'menu_item_id' => $menuItemId,
            'quantity'     => $quantity,
            'unit_price'   => $unitPrice,
            'subtotal'     => $subtotal,
        ]);

        // Recalculate order total via SQL
        $this->recalculateTotal($orderId);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Failed to add item to order', 500);
        }

        return $this->getOrderWithItems($orderId);
    }

    /**
     * Update quantity of an order item.
     */
    public function updateItem(int $orderId, int $orderItemId, int $quantity): array
    {
        $this->db->transStart();

        $order = $this->orderModel->find($orderId);
        if (! $order) {
            $this->db->transRollback();
            throw new RuntimeException('Order not found', 404);
        }

        if ($order['status'] !== 'pending') {
            $this->db->transRollback();
            throw new RuntimeException('Cannot modify items on a non-pending order', 409);
        }

        $bill = $this->billModel->where('order_id', $orderId)->first();
        if ($bill && $bill['payment_status'] === 'paid') {
            $this->db->transRollback();
            throw new RuntimeException('Cannot modify an order that has been paid', 409);
        }

        $item = $this->orderItemModel->find($orderItemId);
        if (! $item || (int) $item['order_id'] !== $orderId) {
            $this->db->transRollback();
            throw new RuntimeException('Order item not found', 404);
        }

        $subtotal = $item['unit_price'] * $quantity;

        $this->orderItemModel->update($orderItemId, [
            'quantity' => $quantity,
            'subtotal' => $subtotal,
        ]);

        $this->recalculateTotal($orderId);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Failed to update order item', 500);
        }

        return $this->getOrderWithItems($orderId);
    }

    /**
     * Remove an item from an order.
     */
    public function removeItem(int $orderId, int $orderItemId): array
    {
        $this->db->transStart();

        $order = $this->orderModel->find($orderId);
        if (! $order) {
            $this->db->transRollback();
            throw new RuntimeException('Order not found', 404);
        }

        if ($order['status'] !== 'pending') {
            $this->db->transRollback();
            throw new RuntimeException('Cannot remove items from a non-pending order', 409);
        }

        $bill = $this->billModel->where('order_id', $orderId)->first();
        if ($bill && $bill['payment_status'] === 'paid') {
            $this->db->transRollback();
            throw new RuntimeException('Cannot modify an order that has been paid', 409);
        }

        $item = $this->orderItemModel->find($orderItemId);
        if (! $item || (int) $item['order_id'] !== $orderId) {
            $this->db->transRollback();
            throw new RuntimeException('Order item not found', 404);
        }

        $this->orderItemModel->delete($orderItemId);

        $this->recalculateTotal($orderId);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Failed to remove order item', 500);
        }

        return $this->getOrderWithItems($orderId);
    }

    /**
     * Complete an order (ready for billing).
     * Reject if order has zero items.
     */
    public function completeOrder(int $orderId): array
    {
        $this->db->transStart();

        $order = $this->orderModel->find($orderId);
        if (! $order) {
            $this->db->transRollback();
            throw new RuntimeException('Order not found', 404);
        }

        if ($order['status'] !== 'pending') {
            $this->db->transRollback();
            throw new RuntimeException('Order is not in pending status', 409);
        }

        // Check if order has at least one item
        $itemCount = $this->orderItemModel->where('order_id', $orderId)->countAllResults();
        if ($itemCount === 0) {
            $this->db->transRollback();
            throw new RuntimeException('Cannot complete an order with no items', 409);
        }

        $this->orderModel->update($orderId, ['status' => 'completed']);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Failed to complete order', 500);
        }

        return $this->getOrderWithItems($orderId);
    }

    /**
     * Cancel an order.
     * Reject if order is already paid OR if any bill exists for this order (paid or unpaid).
     * Mark table as available, write audit log.
     */
    public function cancelOrder(int $orderId, ?string $reason = null, int $userId = 0): array
    {
        $this->db->transStart();

        $order = $this->orderModel->find($orderId);
        if (! $order) {
            $this->db->transRollback();
            throw new RuntimeException('Order not found', 404);
        }

        if ($order['status'] === 'paid') {
            $this->db->transRollback();
            throw new RuntimeException('Cannot cancel a paid order', 409);
        }

        // Block cancellation if ANY bill exists (paid or unpaid)
        $bill = $this->billModel->where('order_id', $orderId)->first();
        if ($bill) {
            $this->db->transRollback();
            throw new RuntimeException('Cannot cancel an order that has a bill (paid or unpaid)', 409);
        }

        $tableId = $order['table_id'];
        $this->orderModel->update($orderId, ['status' => 'cancelled']);

        // Mark table as available
        $this->tableStateService->markAvailable($tableId);

        // Audit log
        $this->auditService->log(
            $userId,
            'order.cancelled',
            'order',
            $orderId,
            ['reason' => $reason ?? 'No reason provided']
        );

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Failed to cancel order', 500);
        }

        return $this->getOrderWithItems($orderId);
    }

    /**
     * Recalculate order total_amount using SQL SUM of order_items.subtotal.
     */
    protected function recalculateTotal(int $orderId): void
    {
        $sql = '
            UPDATE orders
            SET total_amount = COALESCE((
                SELECT SUM(subtotal) FROM order_items WHERE order_id = ?
            ), 0)
            WHERE id = ?
        ';
        $this->db->query($sql, [$orderId, $orderId]);
    }

    /**
     * Get order with items (joined with menu_item names) - single query, no N+1.
     */
    public function getOrderWithItems(int $orderId): array
    {
        $order = $this->orderModel->find($orderId);
        if (! $order) {
            throw new RuntimeException('Order not found', 404);
        }

        // Single join query for items + menu item names
        $items = $this->orderItemModel
            ->select('order_items.*, menu_items.name as menu_item_name')
            ->join('menu_items', 'menu_items.id = order_items.menu_item_id')
            ->where('order_items.order_id', $orderId)
            ->findAll();

        $order['items'] = $items;
        return $order;
    }

    /**
     * List orders with filters (status, table_id, date range) and pagination.
     */
    public function listOrders(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $builder = $this->orderModel->builder();

        if (! empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }
        if (! empty($filters['table_id'])) {
            $builder->where('table_id', $filters['table_id']);
        }
        if (! empty($filters['date_from'])) {
            $builder->where('created_at >=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $builder->where('created_at <=', $filters['date_to'] . ' 23:59:59');
        }

        $builder->orderBy('created_at', 'DESC');

        $orders = $this->orderModel->paginate($perPage, 'default', $page);
        $pager  = $this->orderModel->pager;

        return [
            'orders' => $orders,
            'pager'  => $pager ? $pager->getDetails() : null,
        ];
    }
}
