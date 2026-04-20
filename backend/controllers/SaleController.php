<?php
require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/Drug.php';
require_once __DIR__ . '/../models/StockMovement.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/response.php';

class SaleController
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
        $prescriptionReference = trim((string)($data['prescription_reference'] ?? ''));
        $items = $data['items'] ?? [];
        $paymentMethod = $data['payment_method'] ?? 'Cash';
        $discountAmount = (float)($data['discount_amount'] ?? 0);
        $branchId = $data['branch_id'] ?? $_SESSION['branch_id'];
        if (($_SESSION['role'] ?? '') !== 'manager') {
            $branchId = $_SESSION['branch_id'];
        }

        if (empty($items)) {
            sendError('No items in sale', 400);
            return;
        }

        global $pdo;
        $subTotal = 0;
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
            $subTotal += $drug['price'] * $item['quantity'];
            $saleItems[] = [
                'drug_id' => $drug['id'],
                'quantity' => $item['quantity'],
                'price' => $drug['price']
            ];
        }

        if ($discountAmount < 0) {
            sendError('Discount cannot be negative', 400);
            return;
        }
        if ($discountAmount > $subTotal) {
            sendError('Discount cannot exceed subtotal', 400);
            return;
        }
        $total = $subTotal - $discountAmount;

        $invoiceNo = 'INV-' . strtoupper(uniqid());

        try {
            $pdo->beginTransaction();

            $saleId = $this->saleModel->create(
                $invoiceNo,
                $customerName,
                $total,
                $paymentMethod,
                $_SESSION['user_id'],
                $branchId,
                $discountAmount,
                $prescriptionReference !== '' ? $prescriptionReference : null
            );

            foreach ($saleItems as $item) {
                $this->saleModel->addItem($saleId, $item['drug_id'], $item['quantity'], $item['price']);
                $this->drugModel->updateStock($item['drug_id'], null, -$item['quantity']);
                $this->stockMovementModel->create(
                    (int)$item['drug_id'],
                    (int)$item['quantity'] * -1,
                    'sale:' . $invoiceNo,
                    (int)($_SESSION['user_id'] ?? 0)
                );
            }

            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            sendError('Sale failed: ' . $e->getMessage(), 500);
            return;
        }

        sendSuccess([
            'sale_id' => $saleId,
            'invoice_no' => $invoiceNo,
            'subtotal' => $subTotal,
            'discount_amount' => $discountAmount,
            'net_total' => $total
        ], 'Sale completed successfully');
    }
}
