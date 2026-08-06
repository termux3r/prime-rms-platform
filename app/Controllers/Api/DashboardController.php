<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\DashboardService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Summary counters for the dashboard.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 6.3 / Module 9 (Dashboard)
 * Implement using that document's matching "Prompt" block (Section 8).
 * Keep this controller thin: translate HTTP <-> Service calls only.
 */
class DashboardController extends BaseController
{
    protected DashboardService $dashboardService;

    public function __construct()
    {
        $this->dashboardService = new DashboardService();
    }

    /**
     * GET /api/v1/dashboard/summary - Dashboard summary (admin + cashier)
     */
    public function summary(): ResponseInterface
    {
        try {
            $summary = $this->dashboardService->summary();
            return $this->respond([
                'status' => 'success',
                'data'   => $summary,
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
