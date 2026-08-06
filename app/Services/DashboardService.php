<?php

namespace App\Services;

use CodeIgniter\Config\Services;

/**
 * Dashboard summary metrics.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Module 9 (Dashboard)
 * Implement the methods listed in that document's matching "Prompt" block (Section 8).
 */
class DashboardService
{
    protected $db;
    protected $menuItemModel;
    protected $tableModel;
    protected $orderModel;
    protected $billModel;
    protected $timezone;

    public function __construct()
    {
        $this->db            = db_connect();
        $this->menuItemModel = new \App\Models\MenuItemModel();
        $this->tableModel    = new \App\Models\RestaurantTableModel();
        $this->orderModel    = new \App\Models\OrderModel();
        $this->billModel     = new \App\Models\BillModel();
        $this->timezone      = getenv('APP_TIMEZONE') ?: 'Africa/Addis_Ababa';
    }

    /**
     * Returns { total_menu_items, total_tables, today_orders, today_sales }
     * All timezone-aware using APP_TIMEZONE.
     */
    public function summary(): array
    {
        $tz = $this->timezone;

        // Total active menu items (soft-deleted already excluded by default)
        $totalMenuItems = $this->menuItemModel->where('status', 'active')->countAllResults();

        // Total tables (including soft-deleted? architecture says total_tables - let's count all)
        $totalTables = $this->tableModel->countAllResults();

        // Today's orders (timezone-aware)
        $todayOrders = $this->db->query("
            SELECT COUNT(*) as count
            FROM orders
            WHERE (created_at AT TIME ZONE ?)::date = CURRENT_DATE
        ", [$tz])->getRow()->count;

        // Today's sales (paid bills only, timezone-aware)
        $todaySales = $this->db->query("
            SELECT COALESCE(SUM(total_amount), 0) as total
            FROM bills
            WHERE payment_status = 'paid'
              AND (paid_at AT TIME ZONE ?)::date = CURRENT_DATE
        ", [$tz])->getRow()->total;

        return [
            'total_menu_items' => (int) $totalMenuItems,
            'total_tables'     => (int) $totalTables,
            'today_orders'     => (int) $todayOrders,
            'today_sales'      => (float) $todaySales,
        ];
    }
}