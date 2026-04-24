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
        AuthMiddleware::requireRole(['manager']); // Only manager
    }

    public function salesReport()
    {
        $period = $_GET['period'] ?? 'daily'; // daily, weekly, monthly, custom
        $branchId = $_GET['branch_id'] ?? null;
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;

        $data = $this->saleModel->getSalesReport($period, $branchId, $startDate, $endDate);
        sendSuccess($data);
    }

    public function revenueByBranch()
    {
        $data = $this->saleModel->getRevenueByBranch();
        sendSuccess($data);
    }

    public function revenueByPharmacist()
    {
        $data = $this->saleModel->getRevenueByPharmacist();
        sendSuccess($data);
    }

    public function topDrugs()
    {
        $limit = $_GET['limit'] ?? 10;
        $data = $this->saleModel->getTopDrugs($limit);
        sendSuccess($data);
    }

    public function slowMovingDrugs()
    {
        $limit = $_GET['limit'] ?? 10;
        $data = $this->drugModel->getSlowMoving($limit);
        sendSuccess($data);
    }
}
