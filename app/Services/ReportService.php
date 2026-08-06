<?php

namespace App\Services;

use CodeIgniter\Config\Services;

/**
 * Daily/monthly aggregates.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Module 8 (Sales Reports)
 * Implement the methods listed in that document's matching "Prompt" block (Section 8).
 * Wrap every multi-table write in $this->db->transStart() / transComplete().
 */
class ReportService
{
    protected $db;
    protected $billModel;
    protected $orderModel;
    protected $orderItemModel;
    protected $timezone;

    public function __construct()
    {
        $this->db          = db_connect();
        $this->billModel   = new \App\Models\BillModel();
        $this->orderModel  = new \App\Models\OrderModel();
        $this->orderItemModel = new \App\Models\OrderItemModel();
        $this->timezone    = getenv('APP_TIMEZONE') ?: 'Africa/Addis_Ababa';
    }

    /**
     * Daily report for a specific date (timezone-aware).
     * Returns: total orders, total revenue (paid bills), top 5 items by quantity, average order value.
     */
    public function dailyReport(string $date): array
    {
        // Validate date format
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new \RuntimeException('Invalid date format. Use YYYY-MM-DD', 422);
        }

        // Use timezone-aware bucketing for paid_at
        $tz = $this->timezone;

        // Total orders for the day (orders.created_at in local timezone)
        $totalOrders = $this->db->query("
            SELECT COUNT(*) as count
            FROM orders
            WHERE (created_at AT TIME ZONE ?)::date = ?
        ", [$tz, $date])->getRow()->count;

        // Total revenue from PAID bills only (bills.paid_at in local timezone)
        $revenue = $this->db->query("
            SELECT COALESCE(SUM(total_amount), 0) as total
            FROM bills
            WHERE payment_status = 'paid'
              AND (paid_at AT TIME ZONE ?)::date = ?
        ", [$tz, $date])->getRow()->total;

        // Average order value
        $avgOrder = $totalOrders > 0 ? (float) $revenue / (float) $totalOrders : 0;

        // Top 5 items by quantity sold (join through order_items -> orders -> bills paid)
        // Use withDeleted() equivalent - join menu_items even if soft-deleted
        $topItems = $this->db->query("
            SELECT mi.id, mi.name, SUM(oi.quantity) as total_quantity, SUM(oi.subtotal) as total_revenue
            FROM order_items oi
            JOIN orders o ON o.id = oi.order_id
            JOIN menu_items mi ON mi.id = oi.menu_item_id
            JOIN bills b ON b.order_id = o.id
            WHERE b.payment_status = 'paid'
              AND (b.paid_at AT TIME ZONE ?)::date = ?
            GROUP BY mi.id, mi.name
            ORDER BY total_quantity DESC
            LIMIT 5
        ", [$tz, $date])->getResult();

        return [
            'date'           => $date,
            'total_orders'   => (int) $totalOrders,
            'total_revenue'  => (float) $revenue,
            'avg_order_value'=> round($avgOrder, 2),
            'top_items'      => $topItems,
        ];
    }

    /**
     * Monthly report for a specific year/month (timezone-aware).
     * Returns: daily breakdown + monthly totals.
     */
    public function monthlyReport(int $year, int $month): array
    {
        if ($month < 1 || $month > 12) {
            throw new \RuntimeException('Invalid month', 422);
        }

        $tz = $this->timezone;
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end   = date('Y-m-t', strtotime($start)); // last day of month

        // Daily breakdown: orders count, revenue, avg order value per day
        $daily = $this->db->query("
            SELECT
                (b.paid_at AT TIME ZONE ?)::date as day,
                COUNT(DISTINCT o.id) as orders_count,
                COALESCE(SUM(b.total_amount), 0) as revenue
            FROM bills b
            JOIN orders o ON o.id = b.order_id
            WHERE b.payment_status = 'paid'
              AND (b.paid_at AT TIME ZONE ?)::date BETWEEN ? AND ?
            GROUP BY day
            ORDER BY day
        ", [$tz, $tz, $start, $end])->getResult();

        // Monthly totals
        $totals = $this->db->query("
            SELECT
                COUNT(DISTINCT o.id) as total_orders,
                COALESCE(SUM(b.total_amount), 0) as total_revenue
            FROM bills b
            JOIN orders o ON o.id = b.order_id
            WHERE b.payment_status = 'paid'
              AND (b.paid_at AT TIME ZONE ?)::date BETWEEN ? AND ?
        ", [$tz, $start, $end])->getRow();

        // Top items for the month
        $topItems = $this->db->query("
            SELECT mi.id, mi.name, SUM(oi.quantity) as total_quantity, SUM(oi.subtotal) as total_revenue
            FROM order_items oi
            JOIN orders o ON o.id = oi.order_id
            JOIN menu_items mi ON mi.id = oi.menu_item_id
            JOIN bills b ON b.order_id = o.id
            WHERE b.payment_status = 'paid'
              AND (b.paid_at AT TIME ZONE ?)::date BETWEEN ? AND ?
            GROUP BY mi.id, mi.name
            ORDER BY total_quantity DESC
            LIMIT 5
        ", [$tz, $start, $end])->getResult();

        return [
            'year'         => $year,
            'month'        => $month,
            'daily'        => $daily,
            'total_orders' => (int) $totals->total_orders,
            'total_revenue'=> (float) $totals->total_revenue,
            'top_items'    => $topItems,
        ];
    }
}
