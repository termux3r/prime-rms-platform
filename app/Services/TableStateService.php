<?php

namespace App\Services;

use CodeIgniter\Config\Services;
use RuntimeException;

/**
 * The ONLY place that changes restaurant_tables.status -- called by OrderService and BillingService, never set directly by either.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Module 5 (Restaurant Tables)
 * Implement the methods listed in that document's matching "Prompt" block (Section 8).
 * Wrap every multi-table write in $this->db->transStart() / transComplete().
 */
class TableStateService
{
    protected $db;
    protected $tableModel;

    public function __construct()
    {
        $this->db        = db_connect();
        $this->tableModel = new \App\Models\RestaurantTableModel();
    }

    /**
     * Mark a table as occupied.
     * Called when an order is created for this table.
     */
    public function markOccupied(int $tableId): void
    {
        $this->db->transStart();

        $table = $this->tableModel->find($tableId);
        if (! $table) {
            throw new RuntimeException('Table not found', 404);
        }

        if ($table['status'] === 'occupied') {
            throw new RuntimeException('Table is already occupied', 409);
        }

        $this->tableModel->update($tableId, ['status' => 'occupied']);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Failed to mark table as occupied', 500);
        }
    }

    /**
     * Mark a table as available.
     * Called when an order is completed, cancelled, or paid.
     * Rejects if an active order still exists for this table.
     */
    public function markAvailable(int $tableId): void
    {
        $this->db->transStart();

        $table = $this->tableModel->find($tableId);
        if (! $table) {
            throw new RuntimeException('Table not found', 404);
        }

        // Check for any active order on this table (pending or completed)
        $orderModel = new \App\Models\OrderModel();
        $activeOrder = $orderModel
            ->where('table_id', $tableId)
            ->whereIn('status', ['pending', 'completed'])
            ->first();

        if ($activeOrder) {
            throw new RuntimeException('Cannot mark table available while an active order exists', 409);
        }

        $this->tableModel->update($tableId, ['status' => 'available']);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Failed to mark table as available', 500);
        }
    }
}
