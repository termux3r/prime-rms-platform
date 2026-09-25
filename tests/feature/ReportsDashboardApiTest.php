<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\UserModel;
use App\Models\MenuCategoryModel;
use App\Models\MenuItemModel;
use App\Models\RestaurantTableModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\BillModel;

/**
 * Feature tests for Reports and Dashboard APIs
 */
class ReportsDashboardApiTest extends CIUnitTestCase
{
    use DatabaseTestTrait, FeatureTestTrait;

    protected $userModel;
    protected $catModel;
    protected $itemModel;
    protected $tableModel;
    protected $orderModel;
    protected $orderItemModel;
    protected $billModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel      = new UserModel();
        $this->catModel       = new MenuCategoryModel();
        $this->itemModel      = new MenuItemModel();
        $this->tableModel     = new RestaurantTableModel();
        $this->orderModel     = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->billModel      = new BillModel();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function getAdminToken(): string
    {
        $this->userModel->insert([
            'name'          => 'Admin',
            'username'     => 'admin',
            'password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
            'role'         => 'admin',
            'status'       => 'active',
        ]);

        $response = $this->call('POST', '/api/v1/auth/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);
        $json = $response->getJSON();
        return $json->data->access_token;
    }

    protected function getCashierToken(): string
    {
        $this->userModel->insert([
            'name'          => 'Cashier',
            'username'     => 'cashier',
            'password_hash' => password_hash('cashier123', PASSWORD_BCRYPT),
            'role'         => 'cashier',
            'status'       => 'active',
        ]);

        $response = $this->call('POST', '/api/v1/auth/login', [
            'username' => 'cashier',
            'password' => 'cashier123',
        ]);
        $json = $response->getJSON();
        return $json->data->access_token;
    }

    protected function setupTestData(): array
    {
        $catId = $this->catModel->insert([
            'name'   => 'Test Category',
            'status' => 'active',
        ]);

        $coffeeId = $this->itemModel->insert([
            'category_id' => $catId,
            'name'        => 'Coffee',
            'price'       => 5.00,
            'status'      => 'active',
        ]);

        $teaId = $this->itemModel->insert([
            'category_id' => $catId,
            'name'        => 'Tea',
            'price'       => 3.00,
            'status'      => 'active',
        ]);

        $tableId = $this->tableModel->insert([
            'table_number' => 'R1',
            'capacity'     => 2,
            'status'       => 'available',
        ]);

        return [$coffeeId, $teaId, $tableId];
    }

    protected function createPaidOrder(array $data): int
    {
        $orderId = $this->orderModel->insert([
            'table_id'     => $data['table_id'],
            'cashier_id'   => $data['cashier_id'] ?? 1,
            'status'       => 'paid',
            'total_amount' => $data['total'] ?? 0,
            'created_at'   => $data['created_at'] ?? date('Y-m-d H:i:s'),
            'updated_at'   => $data['updated_at'] ?? date('Y-m-d H:i:s'),
        ]);

        $this->billModel->insert([
            'order_id'       => $orderId,
            'total_amount'   => $data['total'] ?? 0,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'paid_at'        => $data['paid_at'] ?? date('Y-m-d H:i:s'),
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        return $orderId;
    }

    public function testDailyReport(): void
    {
        $token = $this->getAdminToken();
        [$coffeeId, $teaId, $tableId] = $this->setupTestData();

        // Create paid orders for today
        $today = date('Y-m-d');

        $this->createPaidOrder([
            'table_id' => $tableId,
            'total'    => 10.00,
            'paid_at'  => "$today 10:00:00",
        ]);
        $this->orderItemModel->insert([
            'order_id'     => $this->orderModel->getInsertID(),
            'menu_item_id' => $coffeeId,
            'quantity'     => 2,
            'unit_price'   => 5.00,
            'subtotal'     => 10.00,
        ]);

        $this->createPaidOrder([
            'table_id' => $tableId,
            'total'    => 15.00,
            'paid_at'  => "$today 14:00:00",
        ]);
        $this->orderItemModel->insert([
            'order_id'     => $this->orderModel->getInsertID(),
            'menu_item_id' => $teaId,
            'quantity'     => 5,
            'unit_price'   => 3.00,
            'subtotal'     => 15.00,
        ]);

        $response = $this->call('GET', "/api/v1/reports/daily?date=$today", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('success', $json->status);
        $this->assertEquals(2, $json->data->total_orders);
        $this->assertEquals(25.00, $json->data->total_revenue);
        $this->assertEquals(12.50, $json->data->avg_order_value);
        $this->assertCount(2, $json->data->top_items);
    }

    public function testDailyReportExcludesUnpaidBills(): void
    {
        $token = $this->getAdminToken();
        [$coffeeId, $teaId, $tableId] = $this->setupTestData();
        $today = date('Y-m-d');

        // Paid order
        $this->createPaidOrder([
            'table_id' => $tableId,
            'total'    => 20.00,
            'paid_at'  => "$today 10:00:00",
        ]);
        $this->orderItemModel->insert([
            'order_id'     => $this->orderModel->getInsertID(),
            'menu_item_id' => $coffeeId,
            'quantity'     => 4,
            'unit_price'   => 5.00,
            'subtotal'     => 20.00,
        ]);

        // Unpaid bill (should NOT count)
        $orderId = $this->orderModel->insert([
            'table_id'     => $tableId,
            'cashier_id'   => 1,
            'status'       => 'completed',
            'total_amount' => 50.00,
            'created_at'   => "$today 12:00:00",
        ]);
        $this->billModel->insert([
            'order_id'       => $orderId,
            'total_amount'   => 50.00,
            'payment_status' => 'unpaid',
            'created_at'     => "$today 12:00:00",
        ]);

        $response = $this->call('GET', "/api/v1/reports/daily?date=$today", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $json = $response->getJSON();
        $this->assertEquals(1, $json->data->total_orders);
        $this->assertEquals(20.00, $json->data->total_revenue);
    }

    public function testMonthlyReport(): void
    {
        $token = $this->getAdminToken();
        [$coffeeId, $teaId, $tableId] = $this->setupTestData();

        $year  = date('Y');
        $month = date('m');

        // Order on day 1
        $this->createPaidOrder([
            'table_id' => $tableId,
            'total'    => 10.00,
            'paid_at'  => "$year-$month-01 10:00:00",
        ]);
        $this->orderItemModel->insert([
            'order_id'     => $this->orderModel->getInsertID(),
            'menu_item_id' => $coffeeId,
            'quantity'     => 2,
            'unit_price'   => 5.00,
            'subtotal'     => 10.00,
        ]);

        // Order on day 15
        $this->createPaidOrder([
            'table_id' => $tableId,
            'total'    => 20.00,
            'paid_at'  => "$year-$month-15 14:00:00",
        ]);
        $this->orderItemModel->insert([
            'order_id'     => $this->orderModel->getInsertID(),
            'menu_item_id' => $teaId,
            'quantity'     => 5,
            'unit_price'   => 4.00,
            'subtotal'     => 20.00,
        ]);

        // Order next month (should NOT count)
        $nextMonth = $month + 1;
        if ($nextMonth > 12) { $nextMonth = 1; $year++; }
        $this->createPaidOrder([
            'table_id' => $tableId,
            'total'    => 100.00,
            'paid_at'  => "$year-" . sprintf('%02d', $nextMonth) . "-01 10:00:00",
        ]);

        $response = $this->call('GET', "/api/v1/reports/monthly?year=$year&month=$month", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('success', $json->status);
        $this->assertEquals(2, $json->data->total_orders);
        $this->assertEquals(30.00, $json->data->total_revenue);
        $this->assertCount(2, $json->data->daily);
    }

    public function testDailyPrintEndpoint(): void
    {
        $token = $this->getAdminToken();
        $today = date('Y-m-d');

        $response = $this->call('GET', "/api/v1/reports/daily/print?date=$today", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('success', $json->status);
        $this->assertEquals('Daily Sales Report', $json->data->report_type);
        $this->assertArrayHasKey('summary', $json->data);
        $this->assertArrayHasKey('top_items', $json->data);
    }

    public function testMonthlyPrintEndpoint(): void
    {
        $token = $this->getAdminToken();
        $year  = date('Y');
        $month = date('m');

        $response = $this->call('GET', "/api/v1/reports/monthly/print?year=$year&month=$month", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('success', $json->status);
        $this->assertEquals('Monthly Sales Report', $json->data->report_type);
        $this->assertArrayHasKey('summary', $json->data);
        $this->assertArrayHasKey('daily', $json->data);
    }

    public function testDashboardSummary(): void
    {
        $token = $this->getCashierToken();
        [$coffeeId, $teaId, $tableId] = $this->setupTestData();

        // Create some active menu items
        $this->itemModel->insert([
            'category_id' => 1,
            'name'        => 'Active Item',
            'price'       => 10.00,
            'status'      => 'active',
        ]);

        // Create paid order for today
        $today = date('Y-m-d');
        $this->createPaidOrder([
            'table_id' => $tableId,
            'total'    => 25.00,
            'paid_at'  => "$today 10:00:00",
        ]);
        $this->orderItemModel->insert([
            'order_id'     => $this->orderModel->getInsertID(),
            'menu_item_id' => $coffeeId,
            'quantity'     => 5,
            'unit_price'   => 5.00,
            'subtotal'     => 25.00,
        ]);

        $response = $this->call('GET', '/api/v1/dashboard/summary', [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('success', $json->status);
        $this->assertArrayHasKey('total_menu_items', $json->data);
        $this->assertArrayHasKey('total_tables', $json->data);
        $this->assertArrayHasKey('today_orders', $json->data);
        $this->assertArrayHasKey('today_sales', $json->data);
        $this->assertGreaterThanOrEqual(1, $json->data->total_menu_items);
        $this->assertGreaterThanOrEqual(1, $json->data->total_tables);
        $this->assertGreaterThanOrEqual(1, $json->data->today_orders);
        $this->assertEquals(25.00, $json->data->today_sales);
    }

    public function testReportsRequireAdmin(): void
    {
        $token = $this->getCashierToken();

        $response = $this->call('GET', '/api/v1/reports/daily', [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(403);
    }

    public function testDashboardAllowsCashier(): void
    {
        $token = $this->getCashierToken();

        $response = $this->call('GET', '/api/v1/dashboard/summary', [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
    }
}