<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\ReportService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Daily/monthly sales reports.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 6.9 / Module 8 (Sales Reports)
 * Implement using that document's matching "Prompt" block (Section 8).
 * Keep this controller thin: translate HTTP <-> Service calls only.
 */
class ReportController extends BaseController
{
    protected ReportService $reportService;

    public function __construct()
    {
        $this->reportService = new ReportService();
    }

    /**
     * GET /api/v1/reports/daily?date=YYYY-MM-DD - Daily report (admin only)
     */
    public function daily(): ResponseInterface
    {
        $date = $this->request->getGet('date') ?? date('Y-m-d');

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Invalid date format. Use YYYY-MM-DD',
            ], 422);
        }

        try {
            $report = $this->reportService->dailyReport($date);
            return $this->respond([
                'status' => 'success',
                'data'   => $report,
            ]);
        } catch (\RuntimeException $e) {
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * GET /api/v1/reports/monthly?year=YYYY&month=MM - Monthly report (admin only)
     */
    public function monthly(): ResponseInterface
    {
        $year  = $this->request->getGet('year') ?? date('Y');
        $month = $this->request->getGet('month') ?? date('m');

        if (! is_numeric($year) || ! is_numeric($month)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Year and month must be numeric',
            ], 422);
        }

        try {
            $report = $this->reportService->monthlyReport((int) $year, (int) $month);
            return $this->respond([
                'status' => 'success',
                'data'   => $report,
            ]);
        } catch (\RuntimeException $e) {
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * GET /api/v1/reports/daily/print?date=YYYY-MM-DD - Print-friendly daily report (admin only)
     */
    public function dailyPrint(): ResponseInterface
    {
        $date = $this->request->getGet('date') ?? date('Y-m-d');

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Invalid date format. Use YYYY-MM-DD',
            ], 422);
        }

        try {
            $report = $this->reportService->dailyReport($date);

            // Transform for print-friendly output
            $printData = [
                'report_type' => 'Daily Sales Report',
                'date'        => $date,
                'generated_at'=> date('Y-m-d H:i:s'),
                'summary'     => [
                    'Total Orders'     => $report['total_orders'],
                    'Total Revenue'    => number_format($report['total_revenue'], 2),
                    'Avg Order Value'  => number_format($report['avg_order_value'], 2),
                ],
                'top_items'   => $report['top_items'],
            ];

            return $this->respond([
                'status' => 'success',
                'data'   => $printData,
            ]);
        } catch (\RuntimeException $e) {
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * GET /api/v1/reports/monthly/print?year=YYYY&month=MM - Print-friendly monthly report (admin only)
     */
    public function monthlyPrint(): ResponseInterface
    {
        $year  = $this->request->getGet('year') ?? date('Y');
        $month = $this->request->getGet('month') ?? date('m');

        if (! is_numeric($year) || ! is_numeric($month)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Year and month must be numeric',
            ], 422);
        }

        try {
            $report = $this->reportService->monthlyReport((int) $year, (int) $month);

            // Transform for print-friendly output
            $printData = [
                'report_type' => 'Monthly Sales Report',
                'year'        => (int) $year,
                'month'       => (int) $month,
                'generated_at'=> date('Y-m-d H:i:s'),
                'summary'     => [
                    'Total Orders'     => $report['total_orders'],
                    'Total Revenue'    => number_format($report['total_revenue'], 2),
                ],
                'daily'       => $report['daily'],
                'top_items'   => $report['top_items'],
            ];

            return $this->respond([
                'status' => 'success',
                'data'   => $printData,
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
