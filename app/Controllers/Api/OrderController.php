<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\OrderService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Order lifecycle: create, items, complete, cancel.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 6.7 / Module 6 (Order Management)
 * Implement using that document's matching "Prompt" block (Section 8).
 * Keep this controller thin: translate HTTP <-> Service calls only.
 */
class OrderController extends BaseController
{
    protected OrderService $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService();
    }

    /**
     * GET /api/v1/orders - List orders with filters and pagination
     */
    public function index(): ResponseInterface
    {
        $page      = max(1, (int) ($this->request->getGet('page') ?: 1));
        $perPage   = min(100, max(1, (int) ($this->request->getGet('per_page') ?: 20)));
        $status    = $this->request->getGet('status') ?? '';
        $tableId   = $this->request->getGet('table_id') ?? '';
        $dateFrom  = $this->request->getGet('date_from') ?? '';
        $dateTo    = $this->request->getGet('date_to') ?? '';

        $filters = [];
        if ($status) $filters['status'] = $status;
        if ($tableId) $filters['table_id'] = $tableId;
        if ($dateFrom) $filters['date_from'] = $dateFrom;
        if ($dateTo) $filters['date_to'] = $dateTo;

        try {
            $result = $this->orderService->listOrders($filters, $page, $perPage);
            return $this->respond([
                'status' => 'success',
                'data'   => $result,
            ]);
        } catch (\RuntimeException $e) {
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * GET /api/v1/orders/{id} - Get order detail with items (single join, no N+1)
     */
    public function show(int $id): ResponseInterface
    {
        try {
            $order = $this->orderService->getOrderWithItems($id);
            return $this->respond([
                'status' => 'success',
                'data'   => $order,
            ]);
        } catch (\RuntimeException $e) {
            $code = $e->getCode();
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $code ?: 404);
        }
    }

    /**
     * POST /api/v1/orders - Create order for a table (cashier)
     */
    public function create(): ResponseInterface
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        $tableId   = $data['table_id'] ?? null;
        $cashierId = $this->request->userId ?? $this->request->user_id ?? $data['cashier_id'] ?? null;

        if (! $tableId || ! $cashierId) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'table_id and cashier_id are required',
            ], 422);
        }

        try {
            $order = $this->orderService->createOrder((int) $tableId, (int) $cashierId);
            return $this->respond([
                'status' => 'success',
                'data'   => $order,
                'message' => 'Order created',
            ], 201);
        } catch (\RuntimeException $e) {
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * POST /api/v1/orders/{id}/items - Add menu item + quantity (cashier)
     */
    public function addItem(int $id): ResponseInterface
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        $menuItemId = $data['menu_item_id'] ?? null;
        $quantity   = $data['quantity'] ?? null;

        if (! $menuItemId || ! $quantity) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'menu_item_id and quantity are required',
            ], 422);
        }

        try {
            $order = $this->orderService->addItem($id, (int) $menuItemId, (int) $quantity);
            return $this->respond([
                'status' => 'success',
                'data'   => $order,
                'message' => 'Item added to order',
            ]);
        } catch (\RuntimeException $e) {
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * PUT /api/v1/orders/{id}/items/{itemId} - Update item quantity (cashier)
     */
    public function updateItem(int $id, int $itemId): ResponseInterface
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        $quantity = $data['quantity'] ?? null;

        if (! $quantity || (int) $quantity <= 0) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Valid quantity is required',
            ], 422);
        }

        try {
            $order = $this->orderService->updateItem($id, $itemId, (int) $quantity);
            return $this->respond([
                'status' => 'success',
                'data'   => $order,
                'message' => 'Item updated',
            ]);
        } catch (\RuntimeException $e) {
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * DELETE /api/v1/orders/{id}/items/{itemId} - Remove item (cashier)
     */
    public function removeItem(int $id, int $itemId): ResponseInterface
    {
        try {
            $order = $this->orderService->removeItem($id, $itemId);
            return $this->respond([
                'status' => 'success',
                'data'   => $order,
                'message' => 'Item removed',
            ]);
        } catch (\RuntimeException $e) {
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * POST /api/v1/orders/{id}/complete - Lock order, ready for billing (cashier)
     */
    public function complete(int $id): ResponseInterface
    {
        try {
            $order = $this->orderService->completeOrder($id);
            return $this->respond([
                'status' => 'success',
                'data'   => $order,
                'message' => 'Order completed',
            ]);
        } catch (\RuntimeException $e) {
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * POST /api/v1/orders/{id}/cancel - Cancel order (cashier + admin)
     */
    public function cancel(int $id): ResponseInterface
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        $reason = $data['reason'] ?? null;
        $userId = $this->request->userId ?? $this->request->user_id ?? 0;

        try {
            $order = $this->orderService->cancelOrder($id, $reason, $userId);
            return $this->respond([
                'status' => 'success',
                'data'   => $order,
                'message' => 'Order cancelled',
            ]);
        } catch (\RuntimeException $e) {
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    protected function respond(array $data, int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON($data);
    }
}
