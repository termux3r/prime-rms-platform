<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\UserModel;
use App\Models\MenuCategoryModel;
use App\Models\MenuItemModel;

/**
 * Feature tests for Menu Items API
 */
class MenuItemApiTest extends CIUnitTestCase
{
    use DatabaseTestTrait, FeatureTestTrait;

    protected $userModel;
    protected $catModel;
    protected $itemModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel  = new UserModel();
        $this->catModel   = new MenuCategoryModel();
        $this->itemModel  = new MenuItemModel();
    }

    protected function tearDown(): void
    {
        $this->itemModel->where('id >', 0)->delete();
        $this->catModel->where('id >', 0)->delete();
        $this->userModel->where('id >', 0)->delete();
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

    public function testListItems(): void
    {
        $token = $this->getCashierToken();

        $catId = $this->catModel->insert(['name' => 'Drinks', 'status' => 'active']);
        $this->itemModel->insertBatch([
            ['category_id' => $catId, 'name' => 'Coffee', 'price' => 5.00, 'status' => 'active'],
            ['category_id' => $catId, 'name' => 'Tea', 'price' => 3.00, 'status' => 'active'],
        ]);

        $response = $this->call('GET', '/api/v1/menu-items', [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('success', $json->status);
        $this->assertCount(2, $json->data->items);
    }

    public function testCreateItemRequiresAdmin(): void
    {
        $token = $this->getCashierToken();

        $catId = $this->catModel->insert(['name' => 'Test', 'status' => 'active']);

        $response = $this->call('POST', '/api/v1/menu-items', [
            'category_id' => $catId,
            'name'        => 'Unauthorized Item',
            'price'       => 10.00,
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(403);
    }

    public function testCreateItemAsAdmin(): void
    {
        $token = $this->getAdminToken();

        $catId = $this->catModel->insert(['name' => 'Food', 'status' => 'active']);

        $response = $this->call('POST', '/api/v1/menu-items', [
            'category_id' => $catId,
            'name'        => 'Burger',
            'price'       => 12.00,
            'status'      => 'active',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(201);
        $json = $response->getJSON();
        $this->assertEquals('success', $json->status);
        $this->assertEquals('Burger', $json->data->name);
    }

    public function testCreateItemValidatesCategory(): void
    {
        $token = $this->getAdminToken();

        $response = $this->call('POST', '/api/v1/menu-items', [
            'category_id' => 99999,
            'name'        => 'Invalid',
            'price'       => 10.00,
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422);
    }

    public function testGetItem(): void
    {
        $token = $this->getCashierToken();

        $catId = $this->catModel->insert(['name' => 'Test', 'status' => 'active']);
        $itemId = $this->itemModel->insert([
            'category_id' => $catId,
            'name'        => 'Get Item',
            'price'       => 8.00,
            'status'      => 'active',
        ]);

        $response = $this->call('GET', "/api/v1/menu-items/{$itemId}", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('Get Item', $json->data->name);
    }

    public function testUpdateItem(): void
    {
        $token = $this->getAdminToken();

        $catId = $this->catModel->insert(['name' => 'Test', 'status' => 'active']);
        $itemId = $this->itemModel->insert([
            'category_id' => $catId,
            'name'        => 'Original',
            'price'       => 10.00,
            'status'      => 'active',
        ]);

        $response = $this->call('PUT', "/api/v1/menu-items/{$itemId}", [
            'name'   => 'Updated',
            'price'  => 15.00,
            'status' => 'inactive',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('Updated', $json->data->name);
        $this->assertEquals(15.00, $json->data->price);
        $this->assertEquals('inactive', $json->data->status);
    }

    public function testDeleteItem(): void
    {
        $token = $this->getAdminToken();

        $catId = $this->catModel->insert(['name' => 'Test', 'status' => 'active']);
        $itemId = $this->itemModel->insert([
            'category_id' => $catId,
            'name'        => 'To Delete',
            'price'       => 5.00,
            'status'      => 'active',
        ]);

        $response = $this->call('DELETE', "/api/v1/menu-items/{$itemId}", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $item = $this->itemModel->find($itemId);
        $this->assertNull($item);
    }

    public function testItemsByCategory(): void
    {
        $token = $this->getCashierToken();

        $cat1 = $this->catModel->insert(['name' => 'Cat 1', 'status' => 'active']);
        $cat2 = $this->catModel->insert(['name' => 'Cat 2', 'status' => 'active']);

        $this->itemModel->insertBatch([
            ['category_id' => $cat1, 'name' => 'Item A', 'price' => 10.00, 'status' => 'active'],
            ['category_id' => $cat1, 'name' => 'Item B', 'price' => 20.00, 'status' => 'active'],
            ['category_id' => $cat2, 'name' => 'Item C', 'price' => 30.00, 'status' => 'active'],
        ]);

        $response = $this->call('GET', "/api/v1/menu-categories/{$cat1}/items", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertCount(2, $json->data->items);
    }
}