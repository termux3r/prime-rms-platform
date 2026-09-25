<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Services\OrderService;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\MenuItemModel;
use App\Models\MenuCategoryModel;
use App\Models\RestaurantTableModel;
use App\Models\BillModel;

/**
 * Unit tests for OrderService
 */
class OrderServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $orderService;
    protected $orderModel;
    protected $orderItemModel;
    protected $menuItemModel;
    protected $catModel;
    protected $tableModel;
    protected $billModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService  = new OrderService();
        $this->orderModel    = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->menuItemModel = new MenuItemModel();
        $this->catModel      = new MenuCategoryModel();
        $this->tableModel    = new RestaurantTableModel();
        $this->billModel     = new BillModel();
    }

    protected function tearDown(): void
    {
        $this->billModel->where('id >', 0)->delete();
        $this->orderItemModel->where('id >', 0)->delete();
        $this->orderModel->where('id >', 0)->delete();
        (new \App\Models\AuditLogModel())->where('id >', 0)->delete();
        $this->menuItemModel->where('id >', 0)->delete();
        $this->menuItemModel->purgeDeleted();
        $this->catModel->where('id >', 0)->delete();
        $this->catModel->purgeDeleted();
        $this->tableModel->where('id >', 0)->delete();
        $this->tableModel->purgeDeleted();
        (new \App\Models\UserModel())->where('id >', 0)->delete();
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
            'name'   => 'Test',
            'status' => 'active',
        ]);

        // Menu items
        $burgerId = $this->menuItemModel->insert([
            'category_id' => $catId,
            'name'        => 'Burger',
            'price'       => 12.00,
            'status'      => 'active',
        ]);

        $friesId = $this->menuItemModel->insert([
            'category_id' => $catId,
            'name'        => 'Fries',
            'price'       => 5.00,
            'status'      => 'active',
        ]);

        // Inactive item
        $inactiveId = $this->menuItemModel->insert([
            'category_id' => $catId,
            'name'        => 'Inactive Item',
            'price'       => 20.00,
            'status'      => 'inactive',
        ]);

        // Table
        $tableId = $this->tableModel->insert([
            'table_number' => 'O1',
            'capacity'     => 4,
            'status'       => 'available',
        ]);

        return [$burgerId, $friesId, $inactiveId, $tableId];
    }

    public function testCreateOrder(): void
    {
        [$burgerId, $friesId, $inactiveId, $tableId] = $this->createTestData();

        $order = $this->orderService->createOrder($tableId, 1);

        $this->assertNotNull($order['id']);
        $this->assertEquals($tableId, $order['table_id']);
        $this->assertEquals(1, $order['cashier_id']);
        $this->assertEquals('pending', $order['status']);
        $this->assertEquals(0, $order['total_amount']);

        // Table should be occupied
        $table = $this->tableModel->find($tableId);
        $this->assertEquals('occupied', $table['status']);
    }

    public function testCreateOrderFailsIfTableOccupied(): void
    {
        [$burgerId, $friesId, $inactiveId, $tableId] = $this->createTestData();

        $this->orderService->createOrder($tableId, 1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(409);
        $this->orderService->createOrder($tableId, 1);
    }

    public function testAddItem(): void
    {
        [$burgerId, $friesId, $inactiveId, $tableId] = $this->createTestData();
        $order = $this->orderService->createOrder($tableId, 1);

        $updated = $this->orderService->addItem($order['id'], $burgerId, 2);

        $this->assertCount(1, $updated['items']);
        $this->assertEquals(2, $updated['items'][0]['quantity']);
        $this->assertEquals(12.00, $updated['items'][0]['unit_price']);
        $this->assertEquals(24.00, $updated['items'][0]['subtotal']);
        $this->assertEquals(24.00, $updated['total_amount']);
    }

    public function testAddItemRejectsInactiveMenuItem(): void
    {
        [$burgerId, $friesId, $inactiveId, $tableId] = $this->createTestData();
        $order = $this->orderService->createOrder($tableId, 1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(422);
        $this->orderService->addItem($order['id'], $inactiveId, 1);
    }

    public function testAddItemRecalculatesTotal(): void
    {
        [$burgerId, $friesId, $inactiveId, $tableId] = $this->createTestData();
        $order = $this->orderService->createOrder($tableId, 1);

        $this->orderService->addItem($order['id'], $burgerId, 2); // 24.00
        $updated = $this->orderService->addItem($order['id'], $friesId, 1); // +5.00 = 29.00

        $this->assertEquals(29.00, $updated['total_amount']);
    }

    public function testUpdateItem(): void
    {
        [$burgerId, $friesId, $inactiveId, $tableId] = $this->createTestData();
        $order = $this->orderService->createOrder($tableId, 1);
        $order = $this->orderService->addItem($order['id'], $burgerId, 2);
        $itemId = $order['items'][0]['id'];

        $updated = $this->orderService->updateItem($order['id'], $itemId, 5);

        $this->assertEquals(5, $updated['items'][0]['quantity']);
        $this->assertEquals(60.00, $updated['items'][0]['subtotal']);
        $this->assertEquals(60.00, $updated['total_amount']);
    }

    public function testRemoveItem(): void
    {
        [$burgerId, $friesId, $inactiveId, $tableId] = $this->createTestData();
        $order = $this->orderService->createOrder($tableId, 1);
        $this->orderService->addItem($order['id'], $burgerId, 2);
        $order = $this->orderService->addItem($order['id'], $friesId, 1);
        $itemId = $order['items'][0]['id'];

        $updated = $this->orderService->removeItem($order['id'], $itemId);

        $this->assertEquals(5.00, $updated['total_amount']);
        $this->assertCount(1, $updated['items']);
    }

    public function testCompleteOrder(): void
    {
        [$burgerId, $friesId, $inactiveId, $tableId] = $this->createTestData();
        $order = $this->orderService->createOrder($tableId, 1);
        $this->orderService->addItem($order['id'], $burgerId, 1);

        $completed = $this->orderService->completeOrder($order['id']);

        $this->assertEquals('completed', $completed['status']);
    }

    public function testCompleteOrderFailsIfNoItems(): void
    {
        [$burgerId, $friesId, $inactiveId, $tableId] = $this->createTestData();
        $order = $this->orderService->createOrder($tableId, 1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(409);
        $this->orderService->completeOrder($order['id']);
    }

    public function testCancelOrder(): void
    {
        [$burgerId, $friesId, $inactiveId, $tableId] = $this->createTestData();
        $order = $this->orderService->createOrder($tableId, 1);
        $this->orderService->addItem($order['id'], $burgerId, 1);

        $cancelled = $this->orderService->cancelOrder($order['id'], 'Customer left', 1);

        $this->assertEquals('cancelled', $cancelled['status']);

        // Table should be available
        $table = $this->tableModel->find($tableId);
        $this->assertEquals('available', $table['status']);
    }

    public function testCancelOrderFailsIfPaid(): void
    {
        [$burgerId, $friesId, $inactiveId, $tableId] = $this->createTestData();
        $order = $this->orderService->createOrder($tableId, 1);
        $this->orderService->addItem($order['id'], $burgerId, 1);

        // Complete and create bill
        $this->orderService->completeOrder($order['id']);
        $bill = new \App\Services\BillingService();
        $bill->generateBill($order['id']);
        $bill->recordPayment($order['id'], 'cash'); // This will mark order as paid

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(409);
        $this->orderService->cancelOrder($order['id'], 'test', 1);
    }

    public function testCancelOrderFailsIfBillExists(): void
    {
        [$burgerId, $friesId, $inactiveId, $tableId] = $this->createTestData();
        $order = $this->orderService->createOrder($tableId, 1);
        $this->orderService->addItem($order['id'], $burgerId, 1);
        $this->orderService->completeOrder($order['id']);

        // Create unpaid bill
        $this->billModel->insert([
            'order_id'       => $order['id'],
            'total_amount'   => $order['total_amount'],
            'payment_status' => 'unpaid',
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(409);
        $this->orderService->cancelOrder($order['id'], 'test', 1);
    }

    public function testOrderImmutableAfterPaid(): void
    {
        [$burgerId, $friesId, $inactiveId, $tableId] = $this->createTestData();
        $order = $this->orderService->createOrder($tableId, 1);
        $this->orderService->addItem($order['id'], $burgerId, 1);
        $this->orderService->completeOrder($order['id']);

        // Create bill and pay
        $billService = new \App\Services\BillingService();
        $bill = $billService->generateBill($order['id']);
        $billService->recordPayment($bill['id'], 'cash');

        // Try to add item after paid
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(409);
        $this->orderService->addItem($order['id'], $friesId, 1);
    }

    public function testGetOrderWithItemsSingleJoin(): void
    {
        [$burgerId, $friesId, $inactiveId, $tableId] = $this->createTestData();
        $order = $this->orderService->createOrder($tableId, 1);
        $this->orderService->addItem($order['id'], $burgerId, 2);
        $this->orderService->addItem($order['id'], $friesId, 1);

        $orderWithItems = $this->orderService->getOrderWithItems($order['id']);

        $this->assertCount(2, $orderWithItems['items']);
        $this->assertArrayHasKey('menu_item_name', $orderWithItems['items'][0]);
        $this->assertEquals('Burger', $orderWithItems['items'][0]['menu_item_name']);
    }

    public function testListOrdersWithFilters(): void
    {
        [$burgerId, $friesId, $inactiveId, $tableId] = $this->createTestData();

        $table2Id = $this->tableModel->insert([
            'table_number' => 'O2',
            'capacity'     => 4,
            'status'       => 'available',
        ]);

        $o1 = $this->orderService->createOrder($tableId, 1);
        $this->orderService->addItem($o1['id'], $burgerId, 1);

        $o2 = $this->orderService->createOrder($table2Id, 1);
        $this->orderService->addItem($o2['id'], $friesId, 2);

        $result = $this->orderService->listOrders(['status' => 'pending'], 1, 10);
        $this->assertCount(2, $result['orders']);

        $result = $this->orderService->listOrders(['table_id' => $tableId], 1, 10);
        $this->assertCount(1, $result['orders']);
    }
}