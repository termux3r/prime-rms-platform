<?php

namespace App\Services;

use CodeIgniter\Config\Services;
use RuntimeException;

/**
 * Create/update staff, last-admin protection.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Module 2 (Staff / User Management)
 * Implement the methods listed in that document's matching "Prompt" block (Section 8).
 * Wrap every multi-table write in $this->db->transStart() / transComplete().
 */
class UserService
{
    protected $db;
    protected $userModel;

    public function __construct()
    {
        $this->db        = db_connect();
        $this->userModel = new \App\Models\UserModel();
    }

    /**
     * Create a new cashier or admin account.
     */
    public function createUser(array $data): \App\Entities\User
    {
        $this->db->transStart();

        $data['password_hash'] = password_hash($data['password'] ?? 'ChangeMe123!', PASSWORD_BCRYPT);
        unset($data['password']);

        $insertId = $this->userModel->insert($data);

        if ($insertId === false) {
            $errors = $this->userModel->errors();
            $errorMsg = empty($errors) ? 'Failed to create user (validation/db error)' : implode(', ', $errors);
            throw new RuntimeException($errorMsg, 422);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Failed to commit user creation', 500);
        }

        $user = $this->userModel->find($insertId);
        if (! $user) {
            throw new RuntimeException('User created but not found', 500);
        }

        return $user;
    }

    /**
     * Update user's name, email, role (not password).
     */
    public function updateUser(int $id, array $data): \App\Entities\User
    {
        $user = $this->userModel->find($id);
        if (! $user) {
            throw new RuntimeException('User not found', 404);
        }

        // Only allow updating these fields
        $allowed = ['name', 'email', 'role'];
        $filtered = array_intersect_key($data, array_flip($allowed));

        if (empty($filtered)) {
            throw new RuntimeException('No valid fields to update', 422);
        }

        $this->userModel->update($id, $filtered);

        return $this->userModel->find($id);
    }

    /**
     * Activate/deactivate a user.
     * Reject if this would deactivate the last active admin.
     */
    public function setStatus(int $id, string $status): \App\Entities\User
    {
        $user = $this->userModel->find($id);
        if (! $user) {
            throw new RuntimeException('User not found', 404);
        }

        if ($status !== 'active' && $status !== 'inactive') {
            throw new RuntimeException('Invalid status value', 422);
        }

        if ($user->status === $status) {
            return $user; // No change needed
        }

        // Prevent deactivating the last active admin
        if ($status === 'inactive' && $user->role === 'admin') {
            $activeAdmins = $this->userModel
                ->where('role', 'admin')
                ->where('status', 'active')
                ->countAllResults();

            if ($activeAdmins <= 1) {
                throw new RuntimeException('Cannot deactivate the last active admin account', 409);
            }
        }

        $this->userModel->update($id, ['status' => $status]);

        return $this->userModel->find($id);
    }

    /**
     * Admin sets a new password directly.
     */
    public function resetPassword(int $id, string $newPassword): void
    {
        $user = $this->userModel->find($id);
        if (! $user) {
            throw new RuntimeException('User not found', 404);
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->userModel->update($id, ['password_hash' => $hash]);
    }
}
