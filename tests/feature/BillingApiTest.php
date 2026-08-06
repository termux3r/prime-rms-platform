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
 * Feature tests for Billing API
 */
class BillingApiTest extends CIUnitTestCase
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
        $this->billModel->where('id >', 0)->delete();
        $this->orderModel->where('id >', 0)->delete();
        $this->orderItemModel->where('id >', 0)->delete();
        $this->itemModel->where('id >', 0)->delete();
        $this->catModel->where('id >', 0)->delete();
        $this->tableModel->where('id >', 0)->delete();
        $this->userModel->where('id >', 0)->delete();
        parent::tearDown();
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

    protected function setupCompletedOrder(): int
    {
        $catId = $this->catModel->insert([
            'name'   => 'Food',
            'status' => 'active',
        ]);

        $itemId = $this->itemModel->insert([
            'category_id' => $catId,
            'name'        => 'Pizza',
            'price'       => 15.00,
            'status'      => 'active',
        ]);

        $tableId = $this->tableModel->insert([
            'table_number' => 'T1',
            'capacity'     => 4,
            'status'       => 'occupied',
        ]);

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
            'menu_item_id' => $itemId,
            'quantity'     => 2,
            'unit_price'   => 15.00,
            'subtotal'     => 30.00,
        ]);

        return $orderId;
    }

    public function testGenerateBill(): void
    {
        $token = $this->getCashierToken();
        $orderId = $this->setupCompletedOrder();

        $response = $this->call('POST', "/api/v1/orders/{$orderId}/bill", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(201);
        $json = $response->getJSON();
        $this->assertEquals('success', $json->status);
        $this->assertEquals(30.00, $json->data->total_amount);
        $this->assertEquals('unpaid', $json->data->payment_status);
    }

    public function testGenerateBillFailsIfNotCompleted(): void
    {
        $token = $this->getCashierToken();

        $catId = $this->catModel->insert(['name' => 'Test', 'status' => 'active']);
        $itemId = $this->itemModel->insert([
            'category_id' => $catId, 'name' => 'Item', 'price' => 10.00, 'status' => 'active'
        ]);
        $tableId = $this->tableModel->insert([
            'table_number' => 'T2', 'capacity' => 4, 'status' => 'occupied'
        ]);

        $orderId = $this->orderModel->insert([
            'table_id'     => $tableId,
            'cashier_id'   => 1,
            'status'       => 'pending',
            'total_amount' => 0,
        ]);

        $response = $this->call('POST', "/api/v1/orders/{$orderId}/bill", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(409);
    }

    public function testRecordPayment(): void
    {
        $token = $this->getCashierToken();
        $orderId = $this->setupCompletedOrder();

        // Generate bill first
        $billResponse = $this->call('POST', "/api/v1/orders/{$orderId}/bill", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $billId = $billResponse->getJSON()->data->id;

        // Record payment
        $response = $this->call('POST', "/api/v1/bills/{$billId}/pay", [
            'payment_method' => 'cash',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('paid', $json->data->payment_status);
        $this->assertEquals('cash', $json->data->payment_method);
    }

    public function testRecordPaymentFailsIfAlreadyPaid(): void
    {
        $token = $this->getCashierToken();
        $orderId = $this->setupCompletedOrder();

        $billResponse = $this->call('POST', "/api/v1/orders/{$orderId}/bill", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $billId = $billResponse->getJSON()->data->id;

        // First payment
        $this->call('POST', "/api/v1/bills/{$billId}/pay", [
            'payment_method' => 'cash',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        // Second payment should fail
        $response = $this->call('POST', "/api/v1/bills/{$billId}/pay", [
            'payment_method' => 'card',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(409);
    }

    public function testRecordPaymentValidatesMethod(): void
    {
        $token = $this->getCashierToken();
        $orderId = $this->setupCompletedOrder();

        $billResponse = $this->call('POST', "/api/v1/orders/{$orderId}/bill", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $billId = $billResponse->getJSON()->data->id;

        $response = $this->call('POST', "/api/v1/bills/{$billId}/pay", [
            'payment_method' => 'bitcoin',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422);
    }

    public function testGetBill(): void
    {
        $token = $this->getCashierToken();
        $orderId = $this->setupCompletedOrder();

        $billResponse = $this->call('POST', "/api/v1/orders/{$orderId}/bill", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $billId = $billResponse->getJSON()->data->id;

        $response = $this->call('GET', "/api/v1/bills/{$billId}", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals($billId, $json->data->id);
    }

    public function testGetReceipt(): void
    {
        $token = $this->getCashierToken();
        $orderId = $this->setupCompletedOrder();

        $billResponse = $this->call('POST', "/api/v1/orders/{$orderId}/bill", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $billId = $billResponse->getJSON()->data->id;

        $this->call('POST', "/api/v1/bills/{$billId}/pay", [
            'payment_method' => 'cash',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response = $this->call('GET', "/api/v1/bills/{$billId}/receipt", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertArrayHasKey('restaurant', $json->data);
        $this->assertArrayHasKey('bill', $json->data);
        $this->assertArrayHasKey('order', $json->data);
        $this->assertArrayHasKey('items', $json->data);
    }
}