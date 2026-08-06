<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\UserService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Admin-only staff account management.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 6.2 / Module 2 (Staff / User Management)
 * Implement using that document's matching "Prompt" block (Section 8).
 * Keep this controller thin: translate HTTP <-> Service calls only.
 */
class UserController extends BaseController
{
    protected UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    /**
     * GET /api/v1/users - List staff accounts (paginated, searchable)
     */
    public function index(): ResponseInterface
    {
        $page      = max(1, (int) $this->request->getGet('page') ?? 1);
        $perPage   = min(100, max(1, (int) $this->request->getGet('per_page') ?? 20));
        $search    = $this->request->getGet('search') ?? '';
        $status    = $this->request->getGet('status') ?? '';
        $role      = $this->request->getGet('role') ?? '';

        $model = new \App\Models\UserModel();
        $builder = $model->builder();

        if ($search) {
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('username', $search)
                ->orLike('email', $search)
                ->groupEnd();
        }
        if ($status) {
            $builder->where('status', $status);
        }
        if ($role) {
            $builder->where('role', $role);
        }

        $users = $model->paginate($perPage, 'default', $page);
        $pager = $model->pager;

        return $this->respond([
            'status' => 'success',
            'data'   => [
                'users' => $users,
                'pager' => $pager ? $pager->getDetails() : null,
            ],
        ]);
    }

    /**
     * GET /api/v1/users/{id} - Get one account
     */
    public function show(int $id): ResponseInterface
    {
        $model = new \App\Models\UserModel();
        $user = $model->find($id);

        if (! $user) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'User not found',
            ], 404);
        }

        return $this->respond([
            'status' => 'success',
            'data'   => $user,
        ]);
    }

    /**
     * POST /api/v1/users - Create a cashier/admin account
     */
    public function create(): ResponseInterface
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        $required = ['name', 'username', 'password', 'role'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->respond([
                    'status'  => 'error',
                    'message' => "Field '$field' is required",
                ], 422);
            }
        }

        try {
            $user = $this->userService->createUser($data);
            return $this->respond([
                'status' => 'success',
                'data'   => $user,
                'message' => 'User created',
            ], 201);
        } catch (\RuntimeException $e) {
            $code = $e->getCode();
            if ($code === 409 || $code === 422) {
                return $this->respond([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], $code);
            }
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PUT /api/v1/users/{id} - Update name/email/role
     */
    public function update(int $id): ResponseInterface
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        try {
            $user = $this->userService->updateUser($id, $data);
            return $this->respond([
                'status' => 'success',
                'data'   => $user,
                'message' => 'User updated',
            ]);
        } catch (\RuntimeException $e) {
            $code = $e->getCode();
            if ($code === 404 || $code === 422) {
                return $this->respond([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], $code);
            }
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PATCH /api/v1/users/{id}/status - Activate/deactivate
     */
    public function setStatus(int $id): ResponseInterface
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        $status = $data['status'] ?? '';

        if (! in_array($status, ['active', 'inactive'], true)) {
            return $this->respond([
                'status'  => 'error',
                'message' => "Invalid status. Must be 'active' or 'inactive'",
            ], 422);
        }

        try {
            $user = $this->userService->setStatus($id, $status);
            return $this->respond([
                'status' => 'success',
                'data'   => $user,
                'message' => 'Status updated',
            ]);
        } catch (\RuntimeException $e) {
            $code = $e->getCode();
            if ($code === 404 || $code === 409) {
                return $this->respond([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], $code);
            }
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/v1/users/{id}/reset-password - Admin sets new password
     */
    public function resetPassword(int $id): ResponseInterface
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        $newPassword = $data['password'] ?? '';

        if (empty($newPassword)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Password is required',
            ], 422);
        }

        try {
            $this->userService->resetPassword($id, $newPassword);
            return $this->respond([
                'status' => 'success',
                'message' => 'Password reset successful',
            ]);
        } catch (\RuntimeException $e) {
            $code = $e->getCode();
            if ($code === 404) {
                return $this->respond([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], $code);
            }
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    protected function respond(array $data, int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON($data);
    }
}
