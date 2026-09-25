<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * CRUD for menu categories.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 6.4 / Module 3 (Menu Categories)
 * Implement using that document's matching "Prompt" block (Section 8).
 * Keep this controller thin: translate HTTP <-> Service calls only.
 */
class MenuCategoryController extends BaseController
{
    /**
     * GET /api/v1/menu-categories - List categories (paginated, searchable)
     */
    public function index(): ResponseInterface
    {
        $page      = max(1, (int) ($this->request->getGet('page') ?: 1));
        $perPage   = min(100, max(1, (int) ($this->request->getGet('per_page') ?: 20)));
        $search    = $this->request->getGet('search') ?? '';
        $status    = $this->request->getGet('status') ?? '';

        $model = new \App\Models\MenuCategoryModel();

        if ($search) {
            $model->like('name', $search);
        }
        if ($status) {
            $model->where('status', $status);
        }

        $categories = $model->orderBy('name', 'ASC')->paginate($perPage, 'default', $page);
        $pager = $model->pager;

        return $this->respond([
            'status' => 'success',
            'data'   => [
                'categories' => $categories,
                'pager'      => $pager ? $pager->getDetails() : null,
            ],
        ]);
    }

    /**
     * GET /api/v1/menu-categories/{id} - Get one category
     */
    public function show(int $id): ResponseInterface
    {
        $model = new \App\Models\MenuCategoryModel();
        $category = $model->find($id);

        if (! $category) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Category not found',
            ], 404);
        }

        return $this->respond([
            'status' => 'success',
            'data'   => $category,
        ]);
    }

    /**
     * POST /api/v1/menu-categories - Create category (admin only)
     */
    public function create(): ResponseInterface
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        if (empty($data['name'])) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Category name is required',
            ], 422);
        }

        $model = new \App\Models\MenuCategoryModel();
        // Instead of calling $this->menuCategoryModel->getAllowedFields():
$data = $this->request->getJSON(true);

        if (! $model->insert($data)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $model->errors(),
            ], 422);
        }

        $category = $model->find($model->insertID());
        return $this->respond([
            'status' => 'success',
            'data'   => $category,
            'message' => 'Category created',
        ], 201);
    }

    /**
     * PUT /api/v1/menu-categories/{id} - Update category (admin only)
     */
    public function update(int $id): ResponseInterface
    {
        $model = new \App\Models\MenuCategoryModel();
        $category = $model->find($id);

        if (! $category) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Category not found',
            ], 404);
        }

        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        $data = $this->request->getJSON(true);

        if (empty($data)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'No valid fields to update',
            ], 422);
        }

        if (! $model->update($id, $data)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $model->errors(),
            ], 422);
        }

        $category = $model->find($id);
        return $this->respond([
            'status' => 'success',
            'data'   => $category,
            'message' => 'Category updated',
        ]);
    }

    /**
     * DELETE /api/v1/menu-categories/{id} - Soft delete category (admin only)
     */
    public function delete(int $id): ResponseInterface
    {
        $model = new \App\Models\MenuCategoryModel();
        $category = $model->find($id);

        if (! $category) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Category not found',
            ], 404);
        }

        if (! $model->delete($id)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Failed to delete category',
            ], 500);
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Category deleted',
        ]);
    }

    protected function respond(array $data, int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON($data);
    }
}
