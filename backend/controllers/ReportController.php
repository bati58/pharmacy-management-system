<?php
require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/Drug.php';
require_once __DIR__ . '/../models/StockMovement.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/response.php';

class ReportController
{
    private $saleModel;
    private $drugModel;
    private $stockMovementModel;

    public function __construct()
    {
        global $pdo;
        $this->saleModel = new Sale($pdo);
        $this->drugModel = new Drug($pdo);
        $this->stockMovementModel = new StockMovement($pdo);
        AuthMiddleware::check();
    }

    /**
     * Sales aggregates — all roles; non-managers are limited to their branch.
     */
    public function salesReport()
    {
        AuthMiddleware::requireRole(['manager', 'pharmacist', 'store_keeper']);

        $period = $_GET['period'] ?? 'daily';
        $branchId = $_GET['branch_id'] ?? null;
        $pharmacistId = $_GET['pharmacist_id'] ?? null;

        if (($_SESSION['role'] ?? '') === 'pharmacist') {
            $branchId = $_SESSION['branch_id'] ?? null;
            $pharmacistId = $_SESSION['user_id'];
        } elseif (($_SESSION['role'] ?? '') !== 'manager') {
            $branchId = $_SESSION['branch_id'] ?? null;
        }

        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;

        $data = $this->saleModel->getSalesReport($period, $branchId, $startDate, $endDate, $pharmacistId);
        sendSuccess($data);
    }

    public function revenueByBranch()
    {
        AuthMiddleware::requireRole(['manager']);
        $data = $this->saleModel->getRevenueByBranch();
        sendSuccess($data);
    }

    public function revenueByPharmacist()
    {
        AuthMiddleware::requireRole(['manager']);
        $data = $this->saleModel->getRevenueByPharmacist();
        sendSuccess($data);
    }

    public function topDrugs()
    {
        AuthMiddleware::requireRole(['manager']);
        $limit = $_GET['limit'] ?? 10;
        $data = $this->saleModel->getTopDrugs($limit);
        sendSuccess($data);
    }

    public function slowMovingDrugs()
    {
        AuthMiddleware::requireRole(['manager']);
        $limit = $_GET['limit'] ?? 10;
        $data = $this->drugModel->getSlowMoving($limit);
        sendSuccess($data);
    }

    public function profitReport()
    {
        AuthMiddleware::requireRole(['manager']);
        $branchId = $_GET['branch_id'] ?? null;
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $data = $this->saleModel->getProfitReport($branchId, $startDate, $endDate);
        sendSuccess($data);
    }

    public function inventoryHistory()
    {
        AuthMiddleware::requireRole(['manager', 'store_keeper']);
        $branchId = $_GET['branch_id'] ?? null;
        if (($_SESSION['role'] ?? '') !== 'manager') {
            $branchId = $_SESSION['branch_id'] ?? null;
        }
        $reason = $_GET['reason'] ?? null;
        $data = $this->stockMovementModel->getAll($branchId, $reason);
        sendSuccess($data);
    }
}
