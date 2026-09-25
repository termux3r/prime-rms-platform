<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\UserModel;
use App\Models\MenuCategoryModel;
use App\Models\MenuItemModel;
use App\Models\RestaurantTableModel;

/**
 * Feature tests for Orders API
 */
class OrderApiTest extends CIUnitTestCase
{
    use DatabaseTestTrait, FeatureTestTrait;

    protected $userModel;
    protected $catModel;
    protected $itemModel;
    protected $tableModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel  = new UserModel();
        $this->catModel   = new MenuCategoryModel();
        $this->itemModel  = new MenuItemModel();
        $this->tableModel = new RestaurantTableModel();
    }

    protected function tearDown(): void
    {
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

    protected function setupTestData(): array
    {
        $catId = $this->catModel->insert([
            'name'   => 'Food',
            'status' => 'active',
        ]);

        $burgerId = $this->itemModel->insert([
            'category_id' => $catId,
            'name'        => 'Burger',
            'price'       => 12.00,
            'status'      => 'active',
        ]);

        $friesId = $this->itemModel->insert([
            'category_id' => $catId,
            'name'        => 'Fries',
            'price'       => 5.00,
            'status'      => 'active',
        ]);

        $tableId = $this->tableModel->insert([
            'table_number' => 'T1',
            'capacity'     => 4,
            'status'       => 'available',
        ]);

        return [$burgerId, $friesId, $tableId];
    }

    public function testCreateOrder(): void
    {
        $token = $this->getCashierToken();
        [$burgerId, $friesId, $tableId] = $this->setupTestData();

        $response = $this->call('POST', '/api/v1/orders', [
            'table_id' => $tableId,
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(201);
        $json = $response->getJSON();
        $this->assertEquals('pending', $json->data->status);
        $this->assertEquals(0, $json->data->total_amount);
        $this->assertEquals($tableId, $json->data->table_id);
    }

    public function testAddItemToOrder(): void
    {
        $token = $this->getCashierToken();
        [$burgerId, $friesId, $tableId] = $this->setupTestData();

        // Create order
        $createResponse = $this->call('POST', '/api/v1/orders', [
            'table_id' => $tableId,
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $orderId = $createResponse->getJSON()->data->id;

        // Add item
        $response = $this->call('POST', "/api/v1/orders/{$orderId}/items", [
            'menu_item_id' => $burgerId,
            'quantity'     => 2,
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals(1, count($json->data->items));
        $this->assertEquals(2, $json->data->items[0]->quantity);
        $this->assertEquals(24.00, $json->data->total_amount);
    }

    public function testAddItemRejectsInactiveItem(): void
    {
        $token = $this->getCashierToken();
        [$burgerId, $friesId, $tableId] = $this->setupTestData();

        // Deactivate item
        $this->itemModel->update($burgerId, ['status' => 'inactive']);

        $createResponse = $this->call('POST', '/api/v1/orders', [
            'table_id' => $tableId,
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $orderId = $createResponse->getJSON()->data->id;

        $response = $this->call('POST', "/api/v1/orders/{$orderId}/items", [
            'menu_item_id' => $burgerId,
            'quantity'     => 1,
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422);
    }

    public function testUpdateItemQuantity(): void
    {
        $token = $this->getCashierToken();
        [$burgerId, $friesId, $tableId] = $this->setupTestData();

        $createResponse = $this->call('POST', '/api/v1/orders', [
            'table_id' => $tableId,
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $orderId = $createResponse->getJSON()->data->id;

        $this->call('POST', "/api/v1/orders/{$orderId}/items", [
            'menu_item_id' => $burgerId,
            'quantity'     => 2,
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $order = $this->call('GET', "/api/v1/orders/{$orderId}", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ])->getJSON();
        $itemId = $order->data->items[0]->id;

        $response = $this->call('PUT', "/api/v1/orders/{$orderId}/items/{$itemId}", [
            'quantity' => 5,
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals(5, $json->data->items[0]->quantity);
        $this->assertEquals(60.00, $json->data->total_amount);
    }

    public function testRemoveItem(): void
    {
        $token = $this->getCashierToken();
        [$burgerId, $friesId, $tableId] = $this->setupTestData();

        $createResponse = $this->call('POST', '/api/v1/orders', [
            'table_id' => $tableId,
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $orderId = $createResponse->getJSON()->data->id;

        $this->call('POST', "/api/v1/orders/{$orderId}/items", [
            'menu_item_id' => $burgerId,
            'quantity'     => 2,
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $this->call('POST', "/api/v1/orders/{$orderId}/items", [
            'menu_item_id' => $friesId,
            'quantity'     => 1,
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $order = $this->call('GET', "/api/v1/orders/{$orderId}", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ])->getJSON();
        $itemToRemove = $order->data->items[0]->id;

        $response = $this->call('DELETE', "/api/v1/orders/{$orderId}/items/{$itemToRemove}", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals(5.00, $json->data->total_amount);
    }

    public function testCompleteOrder(): void
    {
        $token = $this->getCashierToken();
        [$burgerId, $friesId, $tableId] = $this->setupTestData();

        $createResponse = $this->call('POST', '/api/v1/orders', [
            'table_id' => $tableId,
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $orderId = $createResponse->getJSON()->data->id;

        $this->call('POST', "/api/v1/orders/{$orderId}/items", [
            'menu_item_id' => $burgerId,
            'quantity'     => 1,
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response = $this->call('POST', "/api/v1/orders/{$orderId}/complete", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('completed', $json->data->status);
    }

    public function testCompleteOrderFailsIfNoItems(): void
    {
        $token = $this->getCashierToken();
        [$burgerId, $friesId, $tableId] = $this->setupTestData();

        $createResponse = $this->call('POST', '/api/v1/orders', [
            'table_id' => $tableId,
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $orderId = $createResponse->getJSON()->data->id;

        $response = $this->call('POST', "/api/v1/orders/{$orderId}/complete", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(409);
    }

    public function testCancelOrder(): void
    {
        $token = $this->getCashierToken();
        [$burgerId, $friesId, $tableId] = $this->setupTestData();

        $createResponse = $this->call('POST', '/api/v1/orders', [
            'table_id' => $tableId,
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $orderId = $createResponse->getJSON()->data->id;

        $this->call('POST', "/api/v1/orders/{$orderId}/items", [
            'menu_item_id' => $burgerId,
            'quantity'     => 1,
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response = $this->call('POST', "/api/v1/orders/{$orderId}/cancel", [
            'reason' => 'Customer left',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('cancelled', $json->data->status);
    }

    public function testCancelOrderFailsIfBillExists(): void
    {
        $token = $this->getCashierToken();
        [$burgerId, $friesId, $tableId] = $this->setupTestData();

        $createResponse = $this->call('POST', '/api/v1/orders', [
            'table_id' => $tableId,
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $orderId = $createResponse->getJSON()->data->id;

        $this->call('POST', "/api/v1/orders/{$orderId}/items", [
            'menu_item_id' => $burgerId,
            'quantity'     => 1,
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $billModel = new \App\Models\BillModel();
        $billModel->insert([
            'order_id'       => $orderId,
            'total_amount'   => 15.00,
            'payment_status' => 'unpaid',
        ]);

        $response = $this->call('POST', "/api/v1/orders/{$orderId}/cancel", [
            'reason' => 'Customer wants to cancel',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(409);
    }

    public function testListOrdersWithFilters(): void
    {
        $token = $this->getCashierToken();
        [$burgerId, $friesId, $tableId] = $this->setupTestData();

        $createResponse = $this->call('POST', '/api/v1/orders', [
            'table_id' => $tableId,
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $orderId = $createResponse->getJSON()->data->id;

        $response = $this->call('GET', '/api/v1/orders?status=pending', [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertCount(1, $json->data->orders);
    }
}