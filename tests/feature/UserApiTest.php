<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\UserModel;

/**
 * Feature tests for Staff/User Management API
 */
class UserApiTest extends CIUnitTestCase
{
    use DatabaseTestTrait, FeatureTestTrait;

    protected $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new UserModel();
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

    public function testListUsers(): void
    {
        $token = $this->getAdminToken();

        $id1 = $this->userModel->insert([
            'name'          => 'Another Admin',
            'username'     => 'admin2',
            'password_hash' => password_hash('pass', PASSWORD_BCRYPT),
            'role'         => 'admin',
            'status'       => 'active',
        ]);
        $id2 = $this->userModel->insert([
            'name'          => 'Cashier',
            'username'     => 'cashier1',
            'password_hash' => password_hash('pass', PASSWORD_BCRYPT),
            'role'         => 'cashier',
            'status'       => 'active',
        ]);

        $response = $this->call('GET', '/api/v1/users', [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('success', $json->status);
        $this->assertCount(3, $json->data->users); // admin + admin2 + cashier
    }

    public function testListUsersWithPagination(): void
    {
        $token = $this->getAdminToken();

        // Create 5 cashiers
        for ($i = 1; $i <= 5; $i++) {
            $this->userModel->insert([
                'name'          => "Cashier $i",
                'username'     => "cashier$i",
                'password_hash' => password_hash('pass', PASSWORD_BCRYPT),
                'role'         => 'cashier',
                'status'       => 'active',
            ]);
        }

        $response = $this->call('GET', '/api/v1/users?per_page=2&page=1', [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertCount(2, $json->data->users);
        $this->assertEquals(1, $json->data->pager->currentPage);
    }

    public function testListUsersWithSearch(): void
    {
        $token = $this->getAdminToken();

        $this->userModel->insert([
            'name'          => 'John Smith',
            'username'     => 'johnsmith',
            'password_hash' => password_hash('pass', PASSWORD_BCRYPT),
            'role'         => 'cashier',
            'status'       => 'active',
        ]);
        $this->userModel->insert([
            'name'          => 'Jane Doe',
            'username'     => 'janedoe',
            'password_hash' => password_hash('pass', PASSWORD_BCRYPT),
            'role'         => 'cashier',
            'status'       => 'active',
        ]);

        $response = $this->call('GET', '/api/v1/users?search=John', [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertCount(1, $json->data->users);
        $this->assertEquals('John Smith', $json->data->users[0]->name);
    }

    public function testListUsersWithRoleFilter(): void
    {
        $token = $this->getAdminToken();

        $this->userModel->insert([
            'name'          => 'Admin 2',
            'username'     => 'admin2',
            'password_hash' => password_hash('pass', PASSWORD_BCRYPT),
            'role'         => 'admin',
            'status'       => 'active',
        ]);
        $this->userModel->insert([
            'name'          => 'Cashier 1',
            'username'     => 'cashier1',
            'password_hash' => password_hash('pass', PASSWORD_BCRYPT),
            'role'         => 'cashier',
            'status'       => 'active',
        ]);

        $response = $this->call('GET', '/api/v1/users?role=admin', [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertCount(2, $json->data->users); // original admin + admin2
        foreach ($json->data->users as $user) {
            $this->assertEquals('admin', $user->role);
        }
    }

    public function testGetUser(): void
    {
        $token = $this->getAdminToken();

        $user = $this->userModel->insert([
            'name'          => 'Test User',
            'username'     => 'testuser',
            'password_hash' => password_hash('pass', PASSWORD_BCRYPT),
            'role'         => 'cashier',
            'status'       => 'active',
        ]);

        $response = $this->call('GET', "/api/v1/users/{$user}", [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('Test User', $json->data->name);
    }

    public function testCreateUser(): void
    {
        $token = $this->getAdminToken();

        $response = $this->call('POST', '/api/v1/users', [
            'name'     => 'New Cashier',
            'username' => 'newcashier',
            'password' => 'password123',
            'role'     => 'cashier',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(201);
        $json = $response->getJSON();
        $this->assertEquals('success', $json->status);
        $this->assertEquals('New Cashier', $json->data->name);
        $this->assertEquals('cashier', $json->data->role);
        $this->assertNotEquals('password123', $json->data['password_hash'] ?? null);
    }

    public function testCreateUserRequiresFields(): void
    {
        $token = $this->getAdminToken();

        $response = $this->call('POST', '/api/v1/users', [
            'name' => 'Missing fields',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422);
    }

    public function testCreateUserRejectsDuplicateUsername(): void
    {
        $token = $this->getAdminToken();

        $this->userModel->insert([
            'name'          => 'Existing',
            'username'     => 'duplicate',
            'password_hash' => password_hash('pass', PASSWORD_BCRYPT),
            'role'         => 'cashier',
            'status'       => 'active',
        ]);

        $response = $this->call('POST', '/api/v1/users', [
            'name'     => 'New',
            'username' => 'duplicate',
            'password' => 'pass123',
            'role'     => 'cashier',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertContains($response->response()->getStatusCode(), [422, 500]);
    }

    public function testUpdateUser(): void
    {
        $token = $this->getAdminToken();

        $userId = $this->userModel->insert([
            'name'          => 'Original',
            'username'     => 'original',
            'password_hash' => password_hash('pass', PASSWORD_BCRYPT),
            'role'         => 'cashier',
            'status'       => 'active',
        ]);

        $response = $this->call('PUT', "/api/v1/users/{$userId}", [
            'name'  => 'Updated Name',
            'email' => 'updated@example.com',
            'role'  => 'admin',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('Updated Name', $json->data->name);
        $this->assertEquals('updated@example.com', $json->data->email);
        $this->assertEquals('admin', $json->data->role);
        $this->assertEquals('original', $json->data->username); // Unchanged
    }

    public function testUpdateUserCannotChangePassword(): void
    {
        $token = $this->getAdminToken();

        $userId = $this->userModel->insert([
            'name'          => 'Test',
            'username'     => 'test',
            'password_hash' => password_hash('originalpass', PASSWORD_BCRYPT),
            'role'         => 'cashier',
            'status'       => 'active',
        ]);

        $response = $this->call('PUT', "/api/v1/users/{$userId}", [
            'password' => 'newpassword',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();

        // Password should not be changed
        $user = $this->userModel->find($userId);
        $this->assertTrue(password_verify('originalpass', $user->password_hash));
    }

    public function testSetStatus(): void
    {
        $token = $this->getAdminToken();

        $userId = $this->userModel->insert([
            'name'          => 'Status Test',
            'username'     => 'statustest',
            'password_hash' => password_hash('pass', PASSWORD_BCRYPT),
            'role'         => 'cashier',
            'status'       => 'active',
        ]);

        $response = $this->call('PATCH', "/api/v1/users/{$userId}/status", [
            'status' => 'inactive',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('inactive', $json->data->status);
    }

    public function testSetStatusRejectsInvalidStatus(): void
    {
        $token = $this->getAdminToken();

        $userId = $this->userModel->insert([
            'name'          => 'Test',
            'username'     => 'test',
            'password_hash' => password_hash('pass', PASSWORD_BCRYPT),
            'role'         => 'cashier',
            'status'       => 'active',
        ]);

        $response = $this->call('PATCH', "/api/v1/users/{$userId}/status", [
            'status' => 'invalid',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422);
    }

    public function testCannotDeactivateLastAdmin(): void
    {
        $token = $this->getAdminToken();

        // There's only one admin (the one we logged in as)
        $response = $this->call('PATCH', '/api/v1/users/1/status', [
            'status' => 'inactive',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(409);
        $json = $response->getJSON();
        $this->assertStringContainsString('last active admin', $json->message);
    }

    public function testCanDeactivateAdminIfAnotherExists(): void
    {
        $token = $this->getAdminToken();

        // Create another admin
        $admin2 = $this->userModel->insert([
            'name'          => 'Admin 2',
            'username'     => 'admin2',
            'password_hash' => password_hash('pass', PASSWORD_BCRYPT),
            'role'         => 'admin',
            'status'       => 'active',
        ]);

        // Deactivate admin2 - should work
        $response = $this->call('PATCH', "/api/v1/users/{$admin2}/status", [
            'status' => 'inactive',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('inactive', $json->data->status);
    }

    public function testResetPassword(): void
    {
        $token = $this->getAdminToken();

        $userId = $this->userModel->insert([
            'name'          => 'Reset Test',
            'username'     => 'resettest',
            'password_hash' => password_hash('oldpass', PASSWORD_BCRYPT),
            'role'         => 'cashier',
            'status'       => 'active',
        ]);

        $response = $this->call('POST', "/api/v1/users/{$userId}/reset-password", [
            'password' => 'newpassword123',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('success', $json->status);

        // Verify password was changed
        $user = $this->userModel->find($userId);
        $this->assertTrue(password_verify('newpassword123', $user->password_hash));
        $this->assertFalse(password_verify('oldpass', $user->password_hash));
    }

    public function testResetPasswordRequiresPassword(): void
    {
        $token = $this->getAdminToken();

        $userId = $this->userModel->insert([
            'name'          => 'Test',
            'username'     => 'test',
            'password_hash' => password_hash('pass', PASSWORD_BCRYPT),
            'role'         => 'cashier',
            'status'       => 'active',
        ]);

        $response = $this->call('POST', "/api/v1/users/{$userId}/reset-password", [
            'password' => '',
        ], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422);
    }

    public function testUsersRequiresAdmin(): void
    {
        // Create cashier
        $this->userModel->insert([
            'name'          => 'Cashier',
            'username'     => 'cashier',
            'password_hash' => password_hash('pass', PASSWORD_BCRYPT),
            'role'         => 'cashier',
            'status'       => 'active',
        ]);

        $response = $this->call('POST', '/api/v1/auth/login', [
            'username' => 'cashier',
            'password' => 'pass',
        ]);
        $json = $response->getJSON();
        $cashierToken = $json->data->access_token;

        $response = $this->call('GET', '/api/v1/users', [], [], [
            'Authorization' => 'Bearer ' . $cashierToken,
        ]);

        $response->assertStatus(403);
    }

    public function testNoHardDeleteEndpoint(): void
    {
        $token = $this->getAdminToken();

        $userId = $this->userModel->insert([
            'name'          => 'To Delete',
            'username'     => 'todelete',
            'password_hash' => password_hash('pass', PASSWORD_BCRYPT),
            'role'         => 'cashier',
            'status'       => 'active',
        ]);

        try {
            $response = $this->call('DELETE', "/api/v1/users/{$userId}", [], [], [
                'Authorization' => 'Bearer ' . $token,
            ]);
            $response->assertStatus(404);
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            $this->assertTrue(true);
        }
    }
}