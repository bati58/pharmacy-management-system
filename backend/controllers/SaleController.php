<?php
require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/Drug.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/StockMovement.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/response.php';

class SaleController
{
    private $saleModel;
    private $drugModel;
    private $inventoryModel;
    private $stockMovementModel;

    public function __construct()
    {
        global $pdo;
        $this->saleModel = new Sale($pdo);
        $this->drugModel = new Drug($pdo);
        $this->inventoryModel = new Inventory($pdo);
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
        if (($_SESSION['role'] ?? '') !== 'pharmacist') {
            sendError('Only pharmacists can process sales', 403);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $customerName = $data['customer_name'] ?? 'Walk-in customer';
        $prescriptionReference = trim((string)($data['prescription_reference'] ?? ''));
        $items = $data['items'] ?? [];
        $paymentMethod = $data['payment_method'] ?? 'Cash';
        $discountAmount = (float)($data['discount_amount'] ?? 0);
        $branchId = $_SESSION['branch_id'] ?? null;

        if (empty($items)) {
            sendError('No items in sale', 400);
            return;
        }
        if (!$branchId) {
            sendError('User branch is required to process sales', 400);
            return;
        }
        if (!in_array($paymentMethod, ['Cash', 'Card', 'Mobile Money'], true)) {
            sendError('Invalid payment method', 400);
            return;
        }

        global $pdo;
        $subTotal = 0;
        $saleItems = [];
        $itemsByDrug = [];

        foreach ($items as $item) {
            $itemDrugId = (int)($item['drug_id'] ?? 0);
            $itemQuantity = (int)($item['quantity'] ?? 0);
            if ($itemDrugId <= 0 || $itemQuantity <= 0) {
                sendError('Each sale item must include a drug and positive quantity', 400);
                return;
            }
            if (!isset($itemsByDrug[$itemDrugId])) {
                $itemsByDrug[$itemDrugId] = 0;
            }
            $itemsByDrug[$itemDrugId] += $itemQuantity;
        }

        try {
            $pdo->beginTransaction();

            foreach ($itemsByDrug as $drugId => $quantity) {
                $drug = $this->drugModel->findById($drugId);
                if (!$drug || (int)$drug['branch_id'] !== (int)$branchId) {
                    $pdo->rollBack();
                    sendError("Drug ID {$drugId} not found in this branch", 400);
                    return;
                }
                if ($drug['requires_prescription'] && empty($prescriptionReference)) {
                    $pdo->rollBack();
                    sendError("Drug {$drug['name']} requires a prescription. Please provide a reference.", 400);
                    return;
                }
                if ($drug['expiry_date'] < date('Y-m-d')) {
                    $pdo->rollBack();
                    sendError("Cannot sell expired drug: {$drug['name']}", 400);
                    return;
                }
                $dispensaryStock = $this->inventoryModel->getQuantity($drugId, $branchId, 'dispensary', true);
                if ($dispensaryStock < $quantity) {
                    $pdo->rollBack();
                    sendError("Insufficient stock for {$drug['name']} in dispensary", 400);
                    return;
                }
                $subTotal += $drug['price'] * $quantity;
                $saleItems[] = [
                    'drug_id' => $drug['id'],
                    'quantity' => $quantity,
                    'price' => $drug['price']
                ];
            }

            if ($discountAmount < 0) {
                $pdo->rollBack();
                sendError('Discount cannot be negative', 400);
                return;
            }
            if ($discountAmount > $subTotal) {
                $pdo->rollBack();
                sendError('Discount cannot exceed subtotal', 400);
                return;
            }
            $total = $subTotal - $discountAmount;

            $invoiceNo = 'INV-' . strtoupper(uniqid());

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
                $deducted = $this->inventoryModel->adjustQuantity($item['drug_id'], $branchId, 'dispensary', -$item['quantity']);
                if (!$deducted) {
                    $pdo->rollBack();
                    sendError('Sale failed: dispensary stock changed before checkout. Please refresh and try again.', 409);
                    return;
                }
                $this->stockMovementModel->create(
                    (int)$item['drug_id'],
                    (int)$item['quantity'] * -1,
                    'sale:' . $invoiceNo,
                    (int)($_SESSION['user_id'] ?? 0),
                    (int)$branchId,
                    'dispensary'
                );

                // Check for low stock alert
                $drug = $this->drugModel->findById($item['drug_id']);
                if ($drug && $drug['dispensary_stock'] <= 5) {
                    require_once __DIR__ . '/../models/Notification.php';
                    require_once __DIR__ . '/../models/User.php';
                    $notifModel = new Notification($pdo);
                    $userModel = new User($pdo);
                    $users = $userModel->getAll();
                    
                    $msg = "Low stock alert: {$drug['name']} (Batch: {$drug['batch']}) has only {$drug['dispensary_stock']} units left in the dispensary.";
                    foreach ($users as $user) {
                        if (in_array($user['role'], ['manager', 'store_keeper']) && (int)$user['branch_id'] === (int)$branchId) {
                            $notifModel->create($user['id'], 'low_stock', $msg);
                        }
                    }
                }
            }

            $pdo->commit();
        } catch (Throwable $e) {
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
