<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Health check endpoint.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 6.0 (System Health)
 * GET /api/v1/health - Public, checks DB connectivity.
 */
class HealthController extends BaseController
{
    public function index(): ResponseInterface
    {
        try {
            $db = \Config\Database::connect();
            $db->query('SELECT 1');
            return $this->respond([
                'status' => 'success',
                'data'   => ['status' => 'ok'],
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Health check failed: ' . $e->getMessage());
            return $this->respond([
                'status'  => 'error',
                'message' => 'Database unavailable',
            ], 503);
        }
    }

    protected function respond(array $data, int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON($data);
    }
}