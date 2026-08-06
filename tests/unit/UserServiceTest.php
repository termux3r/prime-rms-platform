<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Services\UserService;
use App\Models\UserModel;

/**
 * Unit tests for UserService
 */
class UserServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $userService;
    protected $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = new UserService();
        $this->userModel   = new UserModel();
    }

    protected function tearDown(): void
    {
        $this->userModel->where('id >', 0)->delete();
        parent::tearDown();
    }

    public function testCreateUserHashesPassword(): void
    {
        $user = $this->userService->createUser([
            'name'     => 'New Cashier',
            'username' => 'newcashier',
            'email'    => 'new@example.com',
            'password' => 'plainpass123',
            'role'     => 'cashier',
            'status'   => 'active',
        ]);

        $this->assertNotNull($user->id);
        $this->assertEquals('New Cashier', $user->name);
        $this->assertEquals('newcashier', $user->username);

        // Password should be hashed
        $dbUser = $this->userModel->find($user->id);
        $this->assertNotEquals('plainpass123', $dbUser->password_hash);
        $this->assertTrue(password_verify('plainpass123', $dbUser->password_hash));
    }

    public function testCreateUserSetsDefaults(): void
    {
        $user = $this->userService->createUser([
            'name'     => 'Minimal User',
            'username' => 'minimal',
            'password' => 'pass123',
            'role'     => 'cashier',
        ]);

        $this->assertEquals('active', $user->status);
        $this->assertEquals('cashier', $user->role);
    }

    public function testUpdateUser(): void
    {
        $user = $this->userService->createUser([
            'name'     => 'Original',
            'username' => 'original',
            'password' => 'pass123',
            'role'     => 'cashier',
            'status'   => 'active',
        ]);

        $updated = $this->userService->updateUser($user->id, [
            'name'  => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $this->assertEquals('Updated Name', $updated->name);
        $this->assertEquals('updated@example.com', $updated->email);
        $this->assertEquals('original', $updated->username); // Unchanged
        $this->assertEquals('cashier', $updated->role); // Unchanged
    }

    public function testUpdateUserIgnoresPassword(): void
    {
        $user = $this->userService->createUser([
            'name'     => 'Test',
            'username' => 'testuser',
            'password' => 'originalpass',
            'role'     => 'cashier',
            'status'   => 'active',
        ]);

        // Try to update password via updateUser (should be ignored)
        $updated = $this->userService->updateUser($user->id, [
            'password' => 'newpassword',
        ]);

        $dbUser = $this->userModel->find($user->id);
        $this->assertTrue(password_verify('originalpass', $dbUser->password_hash));
        $this->assertFalse(password_verify('newpassword', $dbUser->password_hash));
    }

    public function testSetStatus(): void
    {
        $user = $this->userService->createUser([
            'name'     => 'Test',
            'username' => 'testuser',
            'password' => 'pass123',
            'role'     => 'cashier',
            'status'   => 'active',
        ]);

        $updated = $this->userService->setStatus($user->id, 'inactive');
        $this->assertEquals('inactive', $updated->status);

        $updated = $this->userService->setStatus($user->id, 'active');
        $this->assertEquals('active', $updated->status);
    }

    public function testSetStatusThrowsOnLastAdmin(): void
    {
        // Create admin
        $admin = $this->userService->createUser([
            'name'     => 'Admin',
            'username' => 'admin1',
            'password' => 'pass123',
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        // Try to deactivate - should fail
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(409);
        $this->userService->setStatus($admin->id, 'inactive');
    }

    public function testSetStatusAllowsDeactivateIfAnotherAdminExists(): void
    {
        // Create two admins
        $admin1 = $this->userService->createUser([
            'name'     => 'Admin 1',
            'username' => 'admin1',
            'password' => 'pass123',
            'role'     => 'admin',
            'status'   => 'active',
        ]);
        $admin2 = $this->userService->createUser([
            'name'     => 'Admin 2',
            'username' => 'admin2',
            'password' => 'pass123',
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        // Deactivate one - should work
        $updated = $this->userService->setStatus($admin1->id, 'inactive');
        $this->assertEquals('inactive', $updated->status);
    }

    public function testResetPassword(): void
    {
        $user = $this->userService->createUser([
            'name'     => 'Test',
            'username' => 'testuser',
            'password' => 'oldpass',
            'role'     => 'cashier',
            'status'   => 'active',
        ]);

        $this->userService->resetPassword($user->id, 'newpass');

        $dbUser = $this->userModel->find($user->id);
        $this->assertTrue(password_verify('newpass', $dbUser->password_hash));
        $this->assertFalse(password_verify('oldpass', $dbUser->password_hash));
    }
}