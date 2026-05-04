<?php
require_once __DIR__ . '/../models/Transfer.php';
require_once __DIR__ . '/../models/Drug.php';
require_once __DIR__ . '/../models/StockMovement.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/response.php';

class TransferController
{
    private $transferModel;
    private $drugModel;
    private $stockMovementModel;

    public function __construct()
    {
        global $pdo;
        $this->transferModel = new Transfer($pdo);
        $this->drugModel = new Drug($pdo);
        $this->stockMovementModel = new StockMovement($pdo);
        AuthMiddleware::check();
        // Store keeper can create transfers, manager can view all, pharmacist read-only?
    }

    public function index()
    {
        if (($_SESSION['role'] ?? '') === 'manager') {
            $branchId = $_GET['branch_id'] ?? $_SESSION['branch_id'];
        } else {
            $branchId = $_SESSION['branch_id'] ?? null;
        }
        $transfers = $this->transferModel->getAll($branchId);
        sendSuccess($transfers);
    }

    public function create()
    {
        AuthMiddleware::requireRole(['store_keeper']);
        $data = json_decode(file_get_contents('php://input'), true);
        $drugId = $data['drug_id'] ?? null;
        $quantity = (int)($data['quantity'] ?? 0);
        $fromLocation = $data['from_location'] ?? 'store';
        $toLocation = $data['to_location'] ?? 'dispensary';
        $branchId = $data['branch_id'] ?? $_SESSION['branch_id'];

        if (!$drugId || $quantity <= 0) {
            sendError('Drug ID and positive quantity required', 400);
            return;
        }
        if ($fromLocation === $toLocation) {
            sendError('Source and destination cannot be the same', 400);
            return;
        }

        // Check stock availability in source location
        $drug = $this->drugModel->findById($drugId);
        if (!$drug || $drug['branch_id'] != $branchId) {
            sendError('Drug not found in this branch', 404);
            return;
        }

        if ($fromLocation === 'store') {
            if ($drug['stock'] < $quantity) {
                sendError('Insufficient stock in store', 400);
                return;
            }
        } else {
            if ($drug['dispensary_stock'] < $quantity) {
                sendError('Insufficient stock in dispensary', 400);
                return;
            }
        }

        // Just create the pending transfer. Stock is moved on approval (completed status).
        $transferId = $this->transferModel->create($drugId, $quantity, $fromLocation, $toLocation, $branchId, $_SESSION['user_id'], 'pending');
        sendSuccess(['id' => $transferId], 'Transfer request created and pending manager approval');
    }

    public function updateStatus($id)
    {
        AuthMiddleware::requireRole(['manager']);
        $data = json_decode(file_get_contents('php://input'), true);
        $status = $data['status'] ?? '';

        if (!in_array($status, ['pending', 'completed', 'cancelled'])) {
            sendError('Invalid status', 400);
            return;
        }

        $transfer = $this->transferModel->findById($id);
        if (!$transfer) {
            sendError('Transfer not found', 404);
            return;
        }

        if ($transfer['status'] !== 'pending' && $status !== 'pending') {
            sendError('Only pending transfers can be updated', 400);
            return;
        }

        if ($status === 'completed') {
            $drug = $this->drugModel->findById($transfer['drug_id']);
            if (!$drug) {
                sendError('Drug not found', 404);
                return;
            }

            // Perform movement
            if ($transfer['from_location'] === 'store' && $transfer['to_location'] === 'dispensary') {
                if ($drug['stock'] < $transfer['quantity']) {
                    sendError('Insufficient stock in store to complete transfer', 400);
                    return;
                }
                $this->drugModel->updateStock($drug['id'], null, -$transfer['quantity'], 'stock');
                $this->drugModel->updateStock($drug['id'], null, $transfer['quantity'], 'dispensary');
                $this->stockMovementModel->create($drug['id'], -$transfer['quantity'], 'transfer:store_to_shelf', $_SESSION['user_id']);
            } elseif ($transfer['from_location'] === 'dispensary' && $transfer['to_location'] === 'store') {
                if ($drug['dispensary_stock'] < $transfer['quantity']) {
                    sendError('Insufficient stock in dispensary to complete transfer', 400);
                    return;
                }
                $this->drugModel->updateStock($drug['id'], null, $transfer['quantity'], 'stock');
                $this->drugModel->updateStock($drug['id'], null, -$transfer['quantity'], 'dispensary');
                $this->stockMovementModel->create($drug['id'], $transfer['quantity'], 'transfer:shelf_to_store', $_SESSION['user_id']);
            }
        }

        $updated = $this->transferModel->updateStatus($id, $status);
        if ($updated) {
            sendSuccess(null, 'Transfer status updated successfully');
        } else {
            sendError('Failed to update status', 500);
        }
    }
}
