<?php
require_once __DIR__ . '/../models/Transfer.php';
require_once __DIR__ . '/../models/Drug.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/response.php';

class TransferController
{
    private $transferModel;
    private $drugModel;

    public function __construct()
    {
        global $pdo;
        $this->transferModel = new Transfer($pdo);
        $this->drugModel = new Drug($pdo);
        $this->notificationModel = new Notification($pdo);
        $this->userModel = new User($pdo);
        AuthMiddleware::check();
        // Store keeper can create transfers, manager can view all, pharmacist read-only?
    }

    public function index()
    {
        $branchId = $_GET['branch_id'] ?? $_SESSION['branch_id'];
        
        // Security: Store keepers and pharmacists can ONLY view transfers in their own branch
        if (in_array($_SESSION['role'], ['store_keeper', 'pharmacist'])) {
            $branchId = $_SESSION['branch_id'];
        }
        
        $transfers = $this->transferModel->getAll($branchId);
        sendSuccess($transfers);
    }

    public function create()
    {
        AuthMiddleware::requireRole(['store_keeper']);
        $data = json_decode(file_get_contents('php://input'), true);
        $drugId = $data['drug_id'] ?? null;
        $quantity = $data['quantity'] ?? 0;
        $fromLocation = $data['from_location'] ?? 'store';
        $toLocation = $data['to_location'] ?? 'dispensary';
        $branchId = $data['branch_id'] ?? $_SESSION['branch_id'];

        // Security: Store keepers can ONLY create transfers in their own branch
        if ($_SESSION['role'] === 'store_keeper') {
            $branchId = $_SESSION['branch_id'];
        }

        if (!$drugId || $quantity <= 0) {
            sendError('Drug ID and positive quantity required', 400);
            return;
        }

        // Check stock availability in source location
        $drug = $this->drugModel->findById($drugId);
        if (!$drug || $drug['branch_id'] != $branchId) {
            sendError('Drug not found in this branch', 404);
            return;
        }

        if ($fromLocation === 'store') {
            // Assuming 'store' stock is the main stock; we deduct from drug.stock
            if ($drug['stock'] < $quantity) {
                sendError('Insufficient stock in store', 400);
                return;
            }
            $newStock = $drug['stock'] - $quantity;
            $this->drugModel->updateStock($drugId, $newStock, null, $_SESSION['user_id'], 'transfer');
        }

        // For dispensary stock, we might have a separate table; but for simplicity, we don't track dispensary stock separately.
        // We'll just record the transfer.

        $transferId = $this->transferModel->create($drugId, $quantity, $fromLocation, $toLocation, $branchId, $_SESSION['user_id']);
        
        // Notify Pharmacists in the destination branch
        $allUsers = $this->userModel->getAll();
        $message = "Incoming Stock Transfer: {$quantity} units of {$drug['name']} sent to {$toLocation}.";
        foreach ($allUsers as $user) {
            if ($user['status'] === 'active' && $user['role'] === 'pharmacist' && $user['branch_id'] == $branchId) {
                $this->notificationModel->create($user['id'], 'system', $message);
            }
        }

        sendSuccess(['id' => $transferId], 'Stock transferred successfully');
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

        $updated = $this->transferModel->updateStatus($id, $status);
        if ($updated) {
            $transfer = $this->transferModel->findById($id);
            if ($transfer && $transfer['created_by'] != $_SESSION['user_id']) {
                $message = "Your stock transfer request status has been updated to: {$status}.";
                $this->notificationModel->create($transfer['created_by'], 'system', $message);
            }
            sendSuccess(null, 'Transfer status updated');
        } else {
            sendError('Transfer not found', 404);
        }
    }
}
