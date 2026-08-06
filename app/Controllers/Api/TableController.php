<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\TableStateService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * CRUD + status toggling for tables.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 6.6 / Module 5 (Restaurant Tables)
 * Implement using that document's matching "Prompt" block (Section 8).
 * Keep this controller thin: translate HTTP <-> Service calls only.
 */
class TableController extends BaseController
{
    protected TableStateService $tableStateService;

    public function __construct()
    {
        $this->tableStateService = new TableStateService();
    }

    /**
     * GET /api/v1/tables - List all tables with status
     */
    public function index(): ResponseInterface
    {
        $model = new \App\Models\RestaurantTableModel();
        $tables = $model->findAll();

        return $this->respond([
            'status' => 'success',
            'data'   => $tables,
        ]);
    }

    /**
     * GET /api/v1/tables/{id} - Get one table
     */
    public function show(int $id): ResponseInterface
    {
        $model = new \App\Models\RestaurantTableModel();
        $table = $model->find($id);

        if (! $table) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Table not found',
            ], 404);
        }

        return $this->respond([
            'status' => 'success',
            'data'   => $table,
        ]);
    }

    /**
     * POST /api/v1/tables - Add table (admin only)
     */
    public function create(): ResponseInterface
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        $model = new \App\Models\RestaurantTableModel();


        if (! $model->insert($data)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $model->errors(),
            ], 422);
        }

        $table = $model->find($model->insertID());
        return $this->respond([
            'status' => 'success',
            'data'   => $table,
            'message' => 'Table created',
        ], 201);
    }

    /**
     * PUT /api/v1/tables/{id} - Edit table (admin only)
     */
    public function update(int $id): ResponseInterface
    {
        $model = new \App\Models\RestaurantTableModel();
        $table = $model->find($id);

        if (! $table) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Table not found',
            ], 404);
        }

        $data = $this->request->getJSON(true) ?? $this->request->getPost();


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

        $table = $model->find($id);
        return $this->respond([
            'status' => 'success',
            'data'   => $table,
            'message' => 'Table updated',
        ]);
    }

    /**
     * DELETE /api/v1/tables/{id} - Soft delete table (admin only)
     * Blocked if the table currently has an active order
     */
    public function delete(int $id): ResponseInterface
    {
        $model = new \App\Models\RestaurantTableModel();
        $table = $model->find($id);

        if (! $table) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Table not found',
            ], 404);
        }

        // Check for active order on this table
        $orderModel = new \App\Models\OrderModel();
        $activeOrder = $orderModel
            ->where('table_id', $id)
            ->whereIn('status', ['pending', 'completed'])
            ->first();

        if ($activeOrder) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Cannot delete table with an active order',
            ], 409);
        }

        if (! $model->delete($id)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Failed to delete table',
            ], 500);
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Table deleted',
        ]);
    }

    /**
     * PATCH /api/v1/tables/{id}/status - Toggle Available/Occupied via TableStateService (admin + cashier)
     */
    public function updateStatus(int $id): ResponseInterface
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        $status = $data['status'] ?? '';

        if (! in_array($status, ['available', 'occupied'], true)) {
            return $this->respond([
                'status'  => 'error',
                'message' => "Invalid status. Must be 'available' or 'occupied'",
            ], 422);
        }

        try {
            if ($status === 'occupied') {
                $this->tableStateService->markOccupied($id);
            } else {
                $this->tableStateService->markAvailable($id);
            }
        } catch (\RuntimeException $e) {
            $code = $e->getCode();
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $code ?: 409);
        }

        $model = new \App\Models\RestaurantTableModel();
        $table = $model->find($id);

        return $this->respond([
            'status' => 'success',
            'data'   => $table,
            'message' => "Table marked as {$status}",
        ]);
    }

    protected function respond(array $data, int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON($data);
    }
}
