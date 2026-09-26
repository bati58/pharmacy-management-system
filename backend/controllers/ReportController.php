<?php
require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/Drug.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/response.php';

class ReportController
{
    private $saleModel;
    private $drugModel;

    public function __construct()
    {
        global $pdo;
        $this->saleModel = new Sale($pdo);
        $this->drugModel = new Drug($pdo);
        AuthMiddleware::check();
    }

    public function salesReport()
    {
        AuthMiddleware::requireRole(['manager', 'pharmacist']);
        $filters = $this->getReportFilters();
        if ($filters === null) return;
        $pharmacistId = null;

        if ($_SESSION['role'] === 'pharmacist') {
            $filters['branch_id'] = $_SESSION['branch_id'];
            $pharmacistId = $_SESSION['user_id'];
        }

        $data = $this->saleModel->getSalesReport(
            $filters['period'],
            $filters['branch_id'],
            $filters['start_date'],
            $filters['end_date'],
            $pharmacistId
        );
        sendSuccess($data);
    }

    public function revenueByBranch()
    {
        AuthMiddleware::requireRole(['manager']);
        $filters = $this->getReportFilters();
        if ($filters === null) return;
        $data = $this->saleModel->getRevenueByBranch(
            $filters['period'],
            $filters['branch_id'],
            $filters['start_date'],
            $filters['end_date']
        );
        sendSuccess($data);
    }

    public function revenueByPharmacist()
    {
        AuthMiddleware::requireRole(['manager']);
        $filters = $this->getReportFilters();
        if ($filters === null) return;
        $data = $this->saleModel->getRevenueByPharmacist(
            $filters['period'],
            $filters['branch_id'],
            $filters['start_date'],
            $filters['end_date']
        );
        sendSuccess($data);
    }

    public function topDrugs()
    {
        AuthMiddleware::requireRole(['manager']);
        $filters = $this->getReportFilters();
        if ($filters === null) return;
        $limit = max(1, min(50, (int)($_GET['limit'] ?? 10)));
        $data = $this->saleModel->getTopDrugs(
            $limit,
            $filters['period'],
            $filters['branch_id'],
            $filters['start_date'],
            $filters['end_date']
        );
        sendSuccess($data);
    }

    public function slowMovingDrugs()
    {
        AuthMiddleware::requireRole(['manager']);
        $filters = $this->getReportFilters();
        if ($filters === null) return;
        $limit = max(1, min(50, (int)($_GET['limit'] ?? 10)));
        $data = $this->drugModel->getSlowMoving(
            $limit,
            $filters['period'],
            $filters['branch_id'],
            $filters['start_date'],
            $filters['end_date']
        );
        sendSuccess($data);
    }

    private function getReportFilters()
    {
        $period = $_GET['period'] ?? 'weekly';
        if (!in_array($period, ['daily', 'weekly', 'monthly', 'all'], true)) {
            sendError('Invalid report period', 400);
            return null;
        }

        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        if ((bool)$startDate !== (bool)$endDate) {
            sendError('Both start and end dates are required', 400);
            return null;
        }
        if ($startDate && ($startDate > $endDate || !strtotime($startDate) || !strtotime($endDate))) {
            sendError('Invalid report date range', 400);
            return null;
        }

        $branchId = $_GET['branch_id'] ?? null;
        return [
            'period' => $period,
            'branch_id' => $branchId !== '' ? $branchId : null,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
    }
}
