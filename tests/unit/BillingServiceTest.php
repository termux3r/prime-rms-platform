<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Services\BillingService;
use App\Models\BillModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\MenuItemModel;
use App\Models\MenuCategoryModel;
use App\Models\RestaurantTableModel;

/**
 * Unit tests for BillingService
 */
class BillingServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $billingService;
    protected $billModel;
    protected $orderModel;
    protected $orderItemModel;
    protected $menuItemModel;
    protected $catModel;
    protected $tableModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->billingService = new BillingService();
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
        $catId = $this->catModel->insert([
            'name'   => 'Test',
            'status' => 'active',
        ]);

        $itemId = $this->menuItemModel->insert([
            'category_id' => $catId,
            'name'        => 'Item',
            'price'       => 15.00,
            'status'      => 'active',
        ]);

        $tableId = $this->tableModel->insert([
            'table_number' => 'T30',
            'capacity'     => 2,
            'status'       => 'available',
        ]);

        return [$itemId, $tableId];
    }

    protected function createCompletedOrder(int $tableId): int
    {
        $orderId = $this->orderModel->insert([
            'table_id'     => $tableId,
            'cashier_id'   => 1,
            'status'       => 'completed',
            'total_amount' => 30.00,
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        $this->orderItemModel->insert([
            'order_id'     => $orderId,
            'menu_item_id' => 1,
            'quantity'     => 2,
            'unit_price'   => 15.00,
            'subtotal'     => 30.00,
        ]);

        return $orderId;
    }

    public function testGenerateBill(): void
    {
        [$itemId, $tableId] = $this->createTestData();
        $orderId = $this->createCompletedOrder($tableId);

        $bill = $this->billingService->generateBill($orderId);

        $this->assertNotNull($bill['id']);
        $this->assertEquals($orderId, $bill['order_id']);
        $this->assertEquals(30.00, $bill['total_amount']);
        $this->assertEquals('unpaid', $bill['payment_status']);
    }

    public function testGenerateBillFailsIfOrderNotCompleted(): void
    {
        [$itemId, $tableId] = $this->createTestData();

        // Create pending order
        $orderId = $this->orderModel->insert([
            'table_id'     => $tableId,
            'cashier_id'   => 1,
            'status'       => 'pending',
            'total_amount' => 0,
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(409);
        $this->billingService->generateBill($orderId);
    }

    public function testGenerateBillFailsIfAlreadyExists(): void
    {
        [$itemId, $tableId] = $this->createTestData();
        $orderId = $this->createCompletedOrder($tableId);

        $this->billingService->generateBill($orderId);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(409);
        $this->billingService->generateBill($orderId);
    }

    public function testRecordPayment(): void
    {
        [$itemId, $tableId] = $this->createTestData();
        $orderId = $this->createCompletedOrder($tableId);
        $bill = $this->billingService->generateBill($orderId);

        $paidBill = $this->billingService->recordPayment($bill['id'], 'cash');

        $this->assertEquals('paid', $paidBill['payment_status']);
        $this->assertEquals('cash', $paidBill['payment_method']);
        $this->assertNotNull($paidBill['paid_at']);

        // Order should be marked paid
        $order = $this->orderModel->find($orderId);
        $this->assertEquals('paid', $order['status']);

        // Table should be available
        $table = $this->tableModel->find($tableId);
        $this->assertEquals('available', $table['status']);
    }

    public function testRecordPaymentFailsIfAlreadyPaid(): void
    {
        [$itemId, $tableId] = $this->createTestData();
        $orderId = $this->createCompletedOrder($tableId);
        $bill = $this->billingService->generateBill($orderId);

        $this->billingService->recordPayment($bill['id'], 'cash');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(409);
        $this->billingService->recordPayment($bill['id'], 'card');
    }

    public function testRecordPaymentValidatesMethod(): void
    {
        [$itemId, $tableId] = $this->createTestData();
        $orderId = $this->createCompletedOrder($tableId);
        $bill = $this->billingService->generateBill($orderId);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(422);
        $this->billingService->recordPayment($bill['id'], 'bitcoin');
    }

    public function testBuildReceipt(): void
    {
        [$itemId, $tableId] = $this->createTestData();
        $orderId = $this->createCompletedOrder($tableId);
        $bill = $this->billingService->generateBill($orderId);
        $this->billingService->recordPayment($bill['id'], 'cash');

        $receipt = $this->billingService->buildReceipt($bill['id']);

        $this->assertArrayHasKey('restaurant', $receipt);
        $this->assertArrayHasKey('bill', $receipt);
        $this->assertArrayHasKey('order', $receipt);
        $this->assertArrayHasKey('items', $receipt);

        $this->assertEquals($bill['id'], $receipt['bill']['id']);
        $this->assertEquals('paid', $receipt['bill']['payment_status']);
        $this->assertEquals('cash', $receipt['bill']['payment_method']);
    }
}