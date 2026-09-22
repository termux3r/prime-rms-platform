<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\UserModel;

/**
 * Feature tests for Authentication API
 */
class AuthApiTest extends CIUnitTestCase
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
        $db = \Config\Database::connect('tests');
        $db->table('refresh_tokens')->where('id >', 0)->delete();
        $this->userModel->where('id >', 0)->delete();
        parent::tearDown();
    }

    public function testLoginSuccess(): void
    {
        $this->userModel->insert([
            'name'          => 'Test Admin',
            'username'     => 'testadmin',
            'email'        => 'admin@test.com',
            'password_hash' => password_hash('secret123', PASSWORD_BCRYPT),
            'role'         => 'admin',
            'status'       => 'active',
        ]);

        $response = $this->call('POST', '/api/v1/auth/login', [
            'username' => 'testadmin',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('success', $json->status);
        $this->assertNotNull($json->data->access_token);
        $this->assertNotNull($json->data->refresh_token);
    }

    public function testLoginFailsWithWrongPassword(): void
    {
        $this->userModel->insert([
            'name'          => 'Test Admin',
            'username'     => 'testadmin2',
            'password_hash' => password_hash('secret123', PASSWORD_BCRYPT),
            'role'         => 'admin',
            'status'       => 'active',
        ]);

        $response = $this->call('POST', '/api/v1/auth/login', [
            'username' => 'testadmin2',
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(401);
        $json = $response->getJSON();
        $this->assertEquals('error', $json->status);
    }

    public function testLoginFailsWithInactiveUser(): void
    {
        $this->userModel->insert([
            'name'          => 'Inactive User',
            'username'     => 'inactive',
            'password_hash' => password_hash('secret123', PASSWORD_BCRYPT),
            'role'         => 'cashier',
            'status'       => 'inactive',
        ]);

        $response = $this->call('POST', '/api/v1/auth/login', [
            'username' => 'inactive',
            'password' => 'secret123',
        ]);

        $response->assertStatus(403);
        $json = $response->getJSON();
        $this->assertEquals('error', $json->status);
    }

    public function testRefreshToken(): void
    {
        $this->userModel->insert([
            'name'          => 'Refresh User',
            'username'     => 'refreshuser',
            'password_hash' => password_hash('secret123', PASSWORD_BCRYPT),
            'role'         => 'cashier',
            'status'       => 'active',
        ]);

        // First login to get tokens
        $loginResponse = $this->call('POST', '/api/v1/auth/login', [
            'username' => 'refreshuser',
            'password' => 'secret123',
        ]);
        $loginJson = $loginResponse->getJSON();
        $refreshToken = $loginJson->data->refresh_token;

        // Use refresh token
        $response = $this->call('POST', '/api/v1/auth/refresh', [
            'refresh_token' => $refreshToken,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('success', $json->status);
        $this->assertNotNull($json->data->access_token);
        $this->assertNotNull($json->data->refresh_token);
    }

    public function testRefreshTokenRotation(): void
    {
        $this->userModel->insert([
            'name'          => 'Rotation User',
            'username'     => 'rotationuser',
            'password_hash' => password_hash('secret123', PASSWORD_BCRYPT),
            'role'         => 'cashier',
            'status'       => 'active',
        ]);

        // First login
        $loginResponse = $this->call('POST', '/api/v1/auth/login', [
            'username' => 'rotationuser',
            'password' => 'secret123',
        ]);
        $loginJson = $loginResponse->getJSON();
        $refreshToken1 = $loginJson->data->refresh_token;

        // Refresh once
        $refreshResponse = $this->call('POST', '/api/v1/auth/refresh', [
            'refresh_token' => $refreshToken1,
        ]);
        $refreshJson = $refreshResponse->getJSON();
        $refreshToken2 = $refreshJson->data->refresh_token;

        // New token should work
        $response = $this->call('POST', '/api/v1/auth/refresh', [
            'refresh_token' => $refreshToken2,
        ]);
        $response->assertStatus(200);

        // Old refresh token should be revoked (and triggers reuse detection)
        $reuseResponse = $this->call('POST', '/api/v1/auth/refresh', [
            'refresh_token' => $refreshToken1,
        ]);
        $reuseResponse->assertStatus(401);
    }

    public function testLogout(): void
    {
        $this->userModel->insert([
            'name'          => 'Logout User',
            'username'     => 'logoutuser',
            'password_hash' => password_hash('secret123', PASSWORD_BCRYPT),
            'role'         => 'cashier',
            'status'       => 'active',
        ]);

        $loginResponse = $this->call('POST', '/api/v1/auth/login', [
            'username' => 'logoutuser',
            'password' => 'secret123',
        ]);
        $loginJson = $loginResponse->getJSON();
        $accessToken = $loginJson->data->access_token;

        $response = $this->call('POST', '/api/v1/auth/logout', [], [], [
            'Authorization' => 'Bearer ' . $accessToken,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('success', $json->status);

        // Access token should be revoked
        $meResponse = $this->call('GET', '/api/v1/auth/me', [], [], [
            'Authorization' => 'Bearer ' . $accessToken,
        ]);
        $meResponse->assertStatus(401);
    }

    public function testMeEndpoint(): void
    {
        $this->userModel->insert([
            'name'          => 'Me User',
            'username'     => 'meuser',
            'email'        => 'me@test.com',
            'password_hash' => password_hash('secret123', PASSWORD_BCRYPT),
            'role'         => 'admin',
            'status'       => 'active',
        ]);

        $loginResponse = $this->call('POST', '/api/v1/auth/login', [
            'username' => 'meuser',
            'password' => 'secret123',
        ]);
        $loginJson = $loginResponse->getJSON();
        $accessToken = $loginJson->data->access_token;

        $response = $this->call('GET', '/api/v1/auth/me', [], [], [
            'Authorization' => 'Bearer ' . $accessToken,
        ]);

        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('success', $json->status);
        $this->assertEquals('meuser', $json->data->username);
        $this->assertEquals('admin', $json->data->role);
        $this->assertNull($json->data->password_hash ?? null); // Should be hidden
    }

    public function testHealthEndpoint(): void
    {
        $response = $this->call('GET', '/api/v1/health');
        $response->assertStatus(200);
        $json = $response->getJSON();
        $this->assertEquals('success', $json->status);
        $this->assertEquals('ok', $json->data->status);
    }
}