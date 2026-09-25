<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * CRUD for menu items, incl. image upload.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 6.5 / Module 4 (Menu Items)
 * Implement using that document's matching "Prompt" block (Section 8).
 * Keep this controller thin: translate HTTP <-> Service calls only.
 */
class MenuItemController extends BaseController
{
    /**
     * GET /api/v1/menu-items - List items (filter by category, search, paginate)
     */
    public function index(): ResponseInterface
    {
        $page      = max(1, (int) ($this->request->getGet('page') ?: 1));
        $perPage   = min(100, max(1, (int) ($this->request->getGet('per_page') ?: 20)));
        $search    = $this->request->getGet('search') ?? '';
        $category  = $this->request->getGet('category_id') ?? '';
        $status    = $this->request->getGet('status') ?? '';

        $model = new \App\Models\MenuItemModel();

        if ($search) {
            $model->groupStart()
                ->like('name', $search)
                ->orLike('description', $search)
                ->groupEnd();
        }
        if ($category) {
            $model->where('category_id', $category);
        }
        if ($status) {
            $model->where('status', $status);
        }

        $items = $model->orderBy('name', 'ASC')->paginate($perPage, 'default', $page);
        $pager = $model->pager;

        return $this->respond([
            'status' => 'success',
            'data'   => [
                'items' => $items,
                'pager' => $pager ? $pager->getDetails() : null,
            ],
        ]);
    }

    /**
     * GET /api/v1/menu-items/{id} - Get one item
     */
    public function show(int $id): ResponseInterface
    {
        $model = new \App\Models\MenuItemModel();
        // Use withDeleted() so historical items still show
        $item = $model->withDeleted()->find($id);

        if (! $item) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Menu item not found',
            ], 404);
        }

        return $this->respond([
            'status' => 'success',
            'data'   => $item,
        ]);
    }

    /**
     * POST /api/v1/menu-items - Create item (admin only)
     */
    public function create(): ResponseInterface
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        // Handle image upload
        $file = $this->request->getFile('image');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $validation = $this->validate([
                'image' => 'uploaded[image]|mime_in[image,jpg,jpeg,png,webp]|max_size[image,2048]',
            ]);
            if (! $validation) {
                return $this->respond([
                    'status'  => 'error',
                    'message' => 'Invalid image file. Allowed: JPEG, PNG, WebP. Max 2MB.',
                ], 422);
            }

            $newName = $file->getRandomName();
            $file->move('public/uploads/menu-items', $newName);
            $data['image'] = 'uploads/menu-items/' . $newName;
        }

        // Validate category exists
        if (empty($data['category_id'])) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Category ID is required',
            ], 422);
        }
        $catModel = new \App\Models\MenuCategoryModel();
        if (! $catModel->find($data['category_id'])) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Category not found',
            ], 422);
        }

        $model = new \App\Models\MenuItemModel();
        if (! $model->save($data)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $model->errors(),
            ], 422);
        }

        return $this->respond([
            'status'  => 'success',
            'data'    => $model->find($model->insertID()),
            'message' => 'Menu item created',
        ], 201);
    }

    /**
     * PUT /api/v1/menu-items/{id} - Update item (admin only)
     */
    public function update(int $id): ResponseInterface
    {
        $model = new \App\Models\MenuItemModel();
        $item = $model->withDeleted()->find($id);

        if (! $item) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Menu item not found',
            ], 404);
        }

        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        // Handle image upload
        $file = $this->request->getFile('image');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $validation = $this->validate([
                'image' => 'uploaded[image]|mime_in[image,jpg,jpeg,png,webp]|max_size[image,2048]',
            ]);
            if (! $validation) {
                return $this->respond([
                    'status'  => 'error',
                    'message' => 'Invalid image file. Allowed: JPEG, PNG, WebP. Max 2MB.',
                ], 422);
            }

            $newName = $file->getRandomName();
            $file->move('public/uploads/menu-items', $newName);
            $data['image'] = 'uploads/menu-items/' . $newName;
        }

        // Validate category if provided
        if (isset($data['category_id']) && $data['category_id']) {
            $catModel = new \App\Models\MenuCategoryModel();
            if (! $catModel->find($data['category_id'])) {
                return $this->respond([
                    'status'  => 'error',
                    'message' => 'Category not found',
                ], 422);
            }
        }

        if (! $model->update($id, $data)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $model->errors(),
            ], 422);
        }

        return $this->respond([
            'status'  => 'success',
            'data'    => $model->withDeleted()->find($id),
            'message' => 'Menu item updated',
        ]);
    }

    /**
     * DELETE /api/v1/menu-items/{id} - Soft delete (admin only)
     */
    public function delete(int $id): ResponseInterface
    {
        $model = new \App\Models\MenuItemModel();
        $item = $model->withDeleted()->find($id);

        if (! $item) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Menu item not found',
            ], 404);
        }

        if (! $model->delete($id)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Failed to delete menu item',
            ], 500);
        }

        return $this->respond([
            'status'  => 'success',
            'message' => 'Menu item deleted',
        ]);
    }

    /**
     * GET /api/v1/menu-categories/{id}/items - Items within a category
     */
    public function byCategory(int $categoryId): ResponseInterface
    {
        $page    = max(1, (int) ($this->request->getGet('page') ?: 1));
        $perPage = min(100, max(1, (int) ($this->request->getGet('per_page') ?: 20)));

        $catModel = new \App\Models\MenuCategoryModel();
        if (! $catModel->find($categoryId)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Category not found',
            ], 404);
        }

        $model = new \App\Models\MenuItemModel();
        $items = $model->where('category_id', $categoryId)
            ->where('status', 'active')
            ->orderBy('name', 'ASC')
            ->paginate($perPage, 'default', $page);

        $pager = $model->pager;

        return $this->respond([
            'status' => 'success',
            'data'   => [
                'items' => $items,
                'pager' => $pager ? $pager->getDetails() : null,
            ],
        ]);
    }

    protected function respond(array $data, int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON($data);
    }
}
