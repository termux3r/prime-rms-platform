<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\BillingService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Generate bill, record payment, receipt.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 6.8 / Module 7 (Billing)
 * Implement using that document's matching "Prompt" block (Section 8).
 * Keep this controller thin: translate HTTP <-> Service calls only.
 */
class BillingController extends BaseController
{
    protected BillingService $billingService;

    public function __construct()
    {
        $this->billingService = new BillingService();
    }

    /**
     * POST /api/v1/orders/{id}/bill - Generate bill from completed order (cashier)
     */
    public function generate(int $orderId): ResponseInterface
    {
        try {
            $bill = $this->billingService->generateBill($orderId);
            return $this->respond([
                'status' => 'success',
                'data'   => $bill,
                'message' => 'Bill generated',
            ], 201);
        } catch (\RuntimeException $e) {
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * GET /api/v1/bills/{id} - Get bill detail
     */
    public function show(int $id): ResponseInterface
    {
        $model = new \App\Models\BillModel();
        $bill = $model->find($id);

        if (! $bill) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Bill not found',
            ], 404);
        }

        return $this->respond([
            'status' => 'success',
            'data'   => $bill,
        ]);
    }

    /**
     * POST /api/v1/bills/{id}/pay - Record payment (cashier)
     */
    public function pay(int $id): ResponseInterface
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        $method = $data['payment_method'] ?? null;
        if (! $method || ! in_array($method, ['cash', 'card', 'mobile'], true)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Valid payment_method (cash, card, mobile) is required',
            ], 422);
        }

        try {
            $bill = $this->billingService->recordPayment($id, $method);
            return $this->respond([
                'status' => 'success',
                'data'   => $bill,
                'message' => 'Payment recorded',
            ]);
        } catch (\RuntimeException $e) {
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * GET /api/v1/bills/{id}/receipt - Printable receipt payload
     */
    public function receipt(int $id): ResponseInterface
    {
        try {
            $receipt = $this->billingService->buildReceipt($id);
            return $this->respond([
                'status' => 'success',
                'data'   => $receipt,
            ]);
        } catch (\RuntimeException $e) {
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 404);
        }
    }

    protected function respond(array $data, int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON($data);
    }
}
