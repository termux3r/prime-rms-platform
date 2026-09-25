<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\UserModel;
use App\Models\MenuCategoryModel;
use App\Models\MenuItemModel;

/**
 * Feature tests for Menu Categories API
 */
class MenuCategoryApiTest extends CIUnitTestCase
{
    use DatabaseTestTrait, FeatureTestTrait;

    protected $userModel;
    protected $catModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new UserModel();
        $this->catModel = new MenuCategoryModel();
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

    public function testListCategoriesEmpty(): void
    {
        $token = $this->getAdminToken();

        $response = $this->call('GET', '/api/v1/menu-categories', [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('success', $json->status);
        $this->assertEmpty($json->data->categories);
    }

    public function testCreateCategory(): void
    {
        $token = $this->getAdminToken();

        $response = $this->call('POST', '/api/v1/menu-categories', [
            'name'        => 'Appetizers',
            'description' => 'Starters',
            'status'      => 'active',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(201);
        $json = $response->getJSON();
        $this->assertEquals('success', $json->status);
        $this->assertEquals('Appetizers', $json->data->name);
    }

    public function testCreateCategoryRequiresName(): void
    {
        $token = $this->getAdminToken();

        $response = $this->call('POST', '/api/v1/menu-categories', [
            'description' => 'Missing name',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422);
    }

    public function testCreateCategoryRequiresAdmin(): void
    {
        // Create cashier user
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
        $cashierToken = $json->data->access_token;

        $response = $this->call('POST', '/api/v1/menu-categories', [
            'name' => 'Unauthorized',
        ], [], [
            'Authorization' => 'Bearer ' . $cashierToken,
        ]);

        $response->assertStatus(403);
    }

    public function testGetCategory(): void
    {
        $token = $this->getAdminToken();

        $catId = $this->catModel->insert([
            'name'   => 'Desserts',
            'status' => 'active',
        ]);

        $response = $this->call('GET', "/api/v1/menu-categories/{$catId}", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('Desserts', $json->data->name);
    }

    public function testUpdateCategory(): void
    {
        $token = $this->getAdminToken();

        $catId = $this->catModel->insert([
            'name'   => 'Original',
            'status' => 'active',
        ]);

        $response = $this->call('PUT', "/api/v1/menu-categories/{$catId}", [
            'name'   => 'Updated Name',
            'status' => 'inactive',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('Updated Name', $json->data->name);
        $this->assertEquals('inactive', $json->data->status);
    }

    public function testDeleteCategory(): void
    {
        $token = $this->getAdminToken();

        $catId = $this->catModel->insert([
            'name'   => 'To Delete',
            'status' => 'active',
        ]);

        $response = $this->call('DELETE', "/api/v1/menu-categories/{$catId}", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('success', $json->status);

        // Category should be soft deleted
        $cat = $this->catModel->find($catId);
        $this->assertNull($cat);
    }

    public function testListCategoriesWithSearch(): void
    {
        $token = $this->getAdminToken();

        $this->catModel->insertBatch([
            ['name' => 'Breakfast', 'status' => 'active'],
            ['name' => 'Lunch', 'status' => 'active'],
            ['name' => 'Dinner', 'status' => 'active'],
        ]);

        $response = $this->call('GET', '/api/v1/menu-categories?search=Lunch', [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertCount(1, $json->data->categories);
        $this->assertEquals('Lunch', $json->data->categories[0]->name);
    }
}