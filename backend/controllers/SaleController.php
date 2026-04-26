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
        // Pharmacist and manager can access; store keeper no.
        AuthMiddleware::requireRole(['manager', 'pharmacist']);
    }

    public function index()
    {
        $branchId = $_GET['branch_id'] ?? $_SESSION['branch_id'];
        
        if ($_SESSION['role'] === 'pharmacist') {
            $branchId = $_SESSION['branch_id'];
        }
        
        $sales = $this->saleModel->getAll($branchId);
        sendSuccess($sales);
    }

    public function show($id)
    {
        $sale = $this->saleModel->findById($id);
        if (!$sale) {
            sendError('Sale not found', 404);
            return;
        }
        sendSuccess($sale);
    }

    public function create()
    {
        // Only pharmacist can create sales (manager can also but typically not)
        if ($_SESSION['role'] !== 'pharmacist' && $_SESSION['role'] !== 'manager') {
            sendError('Only pharmacists can process sales', 403);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $customerName = $data['customer_name'] ?? 'Walk-in customer';
        $items = $data['items'] ?? []; // array of {drug_id, quantity}
        $discount = $data['discount_amount'] ?? 0;
        $prescriptionRef = $data['prescription_ref'] ?? null;
        $paymentMethod = $data['payment_method'] ?? 'Cash';
        $branchId = $data['branch_id'] ?? $_SESSION['branch_id'];
        
        // Security: Pharmacists can ONLY create sales in their own branch
        if ($_SESSION['role'] === 'pharmacist') {
            $branchId = $_SESSION['branch_id'];
        }

        if (empty($items)) {
            sendError('No items in sale', 400);
            return;
        }

        $total = 0;
        $totalCost = 0;
        $saleItems = [];

        // Validate stock and calculate total
        foreach ($items as $item) {
            $drug = $this->drugModel->findById($item['drug_id']);
            if (!$drug || $drug['branch_id'] != $branchId) {
                sendError("Drug ID {$item['drug_id']} not found in this branch", 400);
                return;
            }
            if ($drug['stock'] < $item['quantity']) {
                sendError("Insufficient stock for {$drug['name']}", 400);
                return;
            }
            $total += $drug['price'] * $item['quantity'];
            $totalCost += $drug['cost_price'] * $item['quantity'];
            $saleItems[] = [
                'drug_id' => $drug['id'],
                'quantity' => $item['quantity'],
                'price' => $drug['price']
            ];
        }

        // Generate invoice number
        $invoiceNo = 'INV-' . strtoupper(uniqid());

        // Apply discount to total
        $finalTotal = $total - $discount;
        if ($finalTotal < 0) $finalTotal = 0;

        $saleId = $this->saleModel->create($invoiceNo, $customerName, $finalTotal, $discount, $totalCost, $prescriptionRef, $paymentMethod, $_SESSION['user_id'], $branchId);

        // Add sale items and deduct stock
        foreach ($saleItems as $item) {
            $this->saleModel->addItem($saleId, $item['drug_id'], $item['quantity'], $item['price']);
            $this->drugModel->updateStock($item['drug_id'], null, -$item['quantity'], $_SESSION['user_id'], 'sale'); // decrease stock
        }

        sendSuccess(['sale_id' => $saleId, 'invoice_no' => $invoiceNo], 'Sale completed successfully');
    }
}
