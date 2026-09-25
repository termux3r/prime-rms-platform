<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Services\ReportService;
use App\Models\BillModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\MenuItemModel;
use App\Models\MenuCategoryModel;
use App\Models\RestaurantTableModel;

/**
 * Unit tests for ReportService - timezone boundary tests
 */
class ReportServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $reportService;
    protected $billModel;
    protected $orderModel;
    protected $orderItemModel;
    protected $menuItemModel;
    protected $catModel;
    protected $tableModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportService  = new ReportService();
        $this->billModel      = new BillModel();
        $this->orderModel     = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->menuItemModel  = new MenuItemModel();
        $this->catModel       = new MenuCategoryModel();
        $this->tableModel     = new RestaurantTableModel();
    }

    protected function tearDown(): void
    {
        $this->billModel->where('id >', 0)->delete();
        $this->orderModel->where('id >', 0)->delete();
        $this->orderItemModel->where('id >', 0)->delete();
        $this->menuItemModel->where('id >', 0)->delete();
        $this->catModel->where('id >', 0)->delete();
        $this->tableModel->where('id >', 0)->delete();
        parent::tearDown();
    }

    protected function createTestData(): array
    {
        $db = \Config\Database::connect('tests');
        if (! $db->table('users')->where('id', 1)->get()->getRow()) {
            $db->table('users')->insert([
                'id'            => 1,
                'name'          => 'Test Cashier',
                'username'      => 'cashier1',
                'email'         => 'cashier1@example.com',
                'password_hash' => password_hash('pass123', PASSWORD_BCRYPT),
                'role'          => 'cashier',
                'status'        => 'active',
            ]);
        }

        // Category
        $catId = $this->catModel->insert([
            'name'   => 'Drinks',
            'status' => 'active',
        ]);

        // Menu items
        $coffeeId = $this->menuItemModel->insert([
            'category_id' => $catId,
            'name'        => 'Coffee',
            'price'       => 5.00,
            'status'      => 'active',
        ]);

        $teaId = $this->menuItemModel->insert([
            'category_id' => $catId,
            'name'        => 'Tea',
            'price'       => 3.00,
            'status'      => 'active',
        ]);

        // Table
        $tableId = $this->tableModel->insert([
            'table_number' => 'R1',
            'capacity'     => 2,
            'status'       => 'available',
        ]);

        return [$coffeeId, $teaId, $tableId];
    }

    protected function createPaidBill(int $orderId, string $paidAt): int
    {
        $order = $this->orderModel->find($orderId);
        $amount = $order ? (float) $order['total_amount'] : 50.00;

        if (! str_contains($paidAt, '+') && ! str_contains($paidAt, 'Z')) {
            $paidAt .= '+03:00';
        }

        $billId = $this->billModel->insert([
            'order_id'       => $orderId,
            'total_amount'   => $amount,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'paid_at'        => $paidAt,
            'created_at'     => $paidAt,
        ]);

        $this->orderModel->update($orderId, ['status' => 'paid']);

        return $billId;
    }

    public function testDailyReportTimezoneBoundaryMidnight(): void
    {
        // This test verifies that a bill paid at 23:00 local time
        // on 2026-07-27 is counted in 2026-07-27's report
        // but a bill paid at 01:00 local time on 2026-07-28
        // is counted in 2026-07-28's report (not 2026-07-27)

        [$coffeeId, $teaId, $tableId] = $this->createTestData();

        // Order 1: paid at 2026-07-27 23:00 Africa/Addis_Ababa
        // This is 2026-07-27 20:00 UTC (Ethiopia is UTC+3)
        $order1 = $this->orderModel->insert([
            'table_id'     => $tableId,
            'cashier_id'   => 1,
            'status'       => 'completed',
            'total_amount' => 10.00,
            'created_at'   => '2026-07-27 20:00:00',
        ]);
        $this->orderItemModel->insert([
            'order_id'     => $order1,
            'menu_item_id' => $coffeeId,
            'quantity'     => 2,
            'unit_price'   => 5.00,
            'subtotal'     => 10.00,
        ]);
        $this->createPaidBill($order1, '2026-07-27 23:00:00'); // Local time

        // Order 2: paid at 2026-07-28 01:00 Africa/Addis_Ababa (next day)
        // This is 2026-07-27 22:00 UTC
        $order2 = $this->orderModel->insert([
            'table_id'     => $tableId,
            'cashier_id'   => 1,
            'status'       => 'completed',
            'total_amount' => 15.00,
            'created_at'   => '2026-07-27 22:00:00',
        ]);
        $this->orderItemModel->insert([
            'order_id'     => $order2,
            'menu_item_id' => $teaId,
            'quantity'     => 5,
            'unit_price'   => 3.00,
            'subtotal'     => 15.00,
        ]);
        $this->createPaidBill($order2, '2026-07-28 01:00:00'); // Local time

        // Daily report for 2026-07-27 should only include order1
        $report27 = $this->reportService->dailyReport('2026-07-27');
        $this->assertEquals(1, $report27['total_orders']);
        $this->assertEquals(10.00, $report27['total_revenue']);

        // Daily report for 2026-07-28 should only include order2
        $report28 = $this->reportService->dailyReport('2026-07-28');
        $this->assertEquals(1, $report28['total_orders']);
        $this->assertEquals(15.00, $report28['total_revenue']);
    }

    public function testDailyReportOnlyCountsPaidBills(): void
    {
        [$coffeeId, $teaId, $tableId] = $this->createTestData();

        // Order 1: paid
        $order1 = $this->orderModel->insert([
            'table_id'     => $tableId,
            'cashier_id'   => 1,
            'status'       => 'completed',
            'total_amount' => 20.00,
            'created_at'   => '2026-07-27 12:00:00',
        ]);
        $this->orderItemModel->insert([
            'order_id'     => $order1,
            'menu_item_id' => $coffeeId,
            'quantity'     => 4,
            'unit_price'   => 5.00,
            'subtotal'     => 20.00,
        ]);
        $this->createPaidBill($order1, '2026-07-27 12:00:00');

        // Order 2: unpaid bill (should NOT count)
        $order2 = $this->orderModel->insert([
            'table_id'     => $tableId,
            'cashier_id'   => 1,
            'status'       => 'completed',
            'total_amount' => 30.00,
            'created_at'   => '2026-07-27 14:00:00',
        ]);
        $this->orderItemModel->insert([
            'order_id'     => $order2,
            'menu_item_id' => $teaId,
            'quantity'     => 10,
            'unit_price'   => 3.00,
            'subtotal'     => 30.00,
        ]);
        // Create unpaid bill
        $this->billModel->insert([
            'order_id'       => $order2,
            'total_amount'   => 30.00,
            'payment_status' => 'unpaid',
            'payment_method' => null,
            'paid_at'        => null,
            'created_at'     => '2026-07-27 14:00:00',
        ]);

        $report = $this->reportService->dailyReport('2026-07-27');
        $this->assertEquals(1, $report['total_orders']);
        $this->assertEquals(20.00, $report['total_revenue']);
    }

    public function testMonthlyReport(): void
    {
        [$coffeeId, $teaId, $tableId] = $this->createTestData();

        // July 1
        $o1 = $this->orderModel->insert([
            'table_id'     => $tableId,
            'cashier_id'   => 1,
            'status'       => 'completed',
            'total_amount' => 10.00,
            'created_at'   => '2026-07-01 10:00:00',
        ]);
        $this->orderItemModel->insert([
            'order_id'     => $o1,
            'menu_item_id' => $coffeeId,
            'quantity'     => 2,
            'unit_price'   => 5.00,
            'subtotal'     => 10.00,
        ]);
        $this->createPaidBill($o1, '2026-07-01 10:00:00');

        // July 15
        $o2 = $this->orderModel->insert([
            'table_id'     => $tableId,
            'cashier_id'   => 1,
            'status'       => 'completed',
            'total_amount' => 20.00,
            'created_at'   => '2026-07-15 12:00:00',
        ]);
        $this->orderItemModel->insert([
            'order_id'     => $o2,
            'menu_item_id' => $teaId,
            'quantity'     => 5,
            'unit_price'   => 4.00,
            'subtotal'     => 20.00,
        ]);
        $this->createPaidBill($o2, '2026-07-15 12:00:00');

        // August 1 (should NOT be in July report)
        $o3 = $this->orderModel->insert([
            'table_id'     => $tableId,
            'cashier_id'   => 1,
            'status'       => 'completed',
            'total_amount' => 50.00,
            'created_at'   => '2026-08-01 10:00:00',
        ]);
        $this->orderItemModel->insert([
            'order_id'     => $o3,
            'menu_item_id' => $coffeeId,
            'quantity'     => 10,
            'unit_price'   => 5.00,
            'subtotal'     => 50.00,
        ]);
        $this->createPaidBill($o3, '2026-08-01 10:00:00');

        $report = $this->reportService->monthlyReport(2026, 7);

        $this->assertEquals(2, $report['total_orders']);
        $this->assertEquals(30.00, $report['total_revenue']);
        $this->assertCount(2, $report['daily']);
    }
}