<?php
require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/Drug.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/response.php';

class SaleController
{
    private $saleModel;
    private $drugModel;

    public function __construct()
    {
        global $pdo;
        $this->saleModel = new Sale($pdo);
        $this->drugModel = new Drug($pdo);
        AuthMiddleware::check();
        AuthMiddleware::requireRole(['manager', 'pharmacist']);
    }

    public function index()
    {
        if (($_SESSION['role'] ?? '') === 'manager') {
            $branchId = isset($_GET['branch_id']) && $_GET['branch_id'] !== ''
                ? $_GET['branch_id']
                : null;
        } else {
            $branchId = $_SESSION['branch_id'] ?? null;
        }

        $pharmacistId = null;
        if (($_SESSION['role'] ?? '') === 'pharmacist') {
            $pharmacistId = $_SESSION['user_id'];
        }

        $sales = $this->saleModel->getAll($branchId, $pharmacistId);
        sendSuccess($sales);
    }

    public function show($id)
    {
        $sale = $this->saleModel->findById($id);
        if (!$sale) {
            sendError('Sale not found', 404);
            return;
        }
        if (($_SESSION['role'] ?? '') === 'pharmacist') {
            if ((int)$sale['pharmacist_id'] !== (int)$_SESSION['user_id']) {
                sendError('Forbidden', 403);
                return;
            }
        }
        sendSuccess($sale);
    }

    public function create()
    {
        if ($_SESSION['role'] !== 'pharmacist' && $_SESSION['role'] !== 'manager') {
            sendError('Only pharmacists can process sales', 403);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $customerName = $data['customer_name'] ?? 'Walk-in customer';
        $items = $data['items'] ?? [];
        $paymentMethod = $data['payment_method'] ?? 'Cash';
        $branchId = $data['branch_id'] ?? $_SESSION['branch_id'];
        if (($_SESSION['role'] ?? '') !== 'manager') {
            $branchId = $_SESSION['branch_id'];
        }

        if (empty($items)) {
            sendError('No items in sale', 400);
            return;
        }

        global $pdo;
        $total = 0;
        $saleItems = [];

        foreach ($items as $item) {
            $drug = $this->drugModel->findById($item['drug_id']);
            if (!$drug || (int)$drug['branch_id'] !== (int)$branchId) {
                sendError("Drug ID {$item['drug_id']} not found in this branch", 400);
                return;
            }
            if ($drug['expiry_date'] < date('Y-m-d')) {
                sendError("Cannot sell expired drug: {$drug['name']}", 400);
                return;
            }
            if ($drug['stock'] < $item['quantity']) {
                sendError("Insufficient stock for {$drug['name']}", 400);
                return;
            }
            $total += $drug['price'] * $item['quantity'];
            $saleItems[] = [
                'drug_id' => $drug['id'],
                'quantity' => $item['quantity'],
                'price' => $drug['price']
            ];
        }

        $invoiceNo = 'INV-' . strtoupper(uniqid());

        try {
            $pdo->beginTransaction();

            $saleId = $this->saleModel->create($invoiceNo, $customerName, $total, $paymentMethod, $_SESSION['user_id'], $branchId);

            foreach ($saleItems as $item) {
                $this->saleModel->addItem($saleId, $item['drug_id'], $item['quantity'], $item['price']);
                $this->drugModel->updateStock($item['drug_id'], null, -$item['quantity']);
            }

            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            sendError('Sale failed: ' . $e->getMessage(), 500);
            return;
        }

        sendSuccess(['sale_id' => $saleId, 'invoice_no' => $invoiceNo], 'Sale completed successfully');
    }
}
