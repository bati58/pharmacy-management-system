<?php
require_once __DIR__ . '/../models/Transfer.php';
require_once __DIR__ . '/../models/Drug.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/StockMovement.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/response.php';

class TransferController
{
    private $transferModel;
    private $drugModel;
    private $inventoryModel;
    private $stockMovementModel;

    public function __construct()
    {
        global $pdo;
        $this->transferModel = new Transfer($pdo);
        $this->drugModel = new Drug($pdo);
        $this->inventoryModel = new Inventory($pdo);
        $this->stockMovementModel = new StockMovement($pdo);
        AuthMiddleware::check();
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
        $sourceLocation = Inventory::normalizeLocation($data['source_location'] ?? ($data['from_location'] ?? 'store'));
        $destinationLocation = Inventory::normalizeLocation($data['destination_location'] ?? ($data['to_location'] ?? 'dispensary'));
        $branchId = $_SESSION['branch_id'] ?? null;

        if (!$drugId || $quantity <= 0) {
            sendError('Drug ID and positive quantity required', 400);
            return;
        }
        if ($sourceLocation === null || $destinationLocation === null) {
            sendError('Source and destination must be store or dispensary', 400);
            return;
        }
        if ($sourceLocation === $destinationLocation) {
            sendError('Source and destination cannot be the same', 400);
            return;
        }
        if (!$branchId) {
            sendError('User branch is required to transfer stock', 400);
            return;
        }

        $drug = $this->drugModel->findById($drugId);
        if (!$drug || $drug['branch_id'] != $branchId) {
            sendError('Drug not found in this branch', 404);
            return;
        }

        global $pdo;
        try {
            $pdo->beginTransaction();

            $available = $this->inventoryModel->getQuantity($drugId, $branchId, $sourceLocation, true);
            if ($available < $quantity) {
                $pdo->rollBack();
                sendError("Insufficient stock in {$sourceLocation}", 400);
                return;
            }

            $transferId = $this->transferModel->create(
                $drugId,
                $quantity,
                $sourceLocation,
                $destinationLocation,
                $branchId,
                $_SESSION['user_id'],
                'completed'
            );

            $moved = $this->inventoryModel->transfer($drugId, $branchId, $sourceLocation, $destinationLocation, $quantity);
            if (!$moved) {
                $pdo->rollBack();
                sendError("Insufficient stock in {$sourceLocation}", 400);
                return;
            }

            $this->stockMovementModel->create(
                $drugId,
                -$quantity,
                "transfer:{$transferId}:{$sourceLocation}_to_{$destinationLocation}",
                $_SESSION['user_id'],
                $branchId,
                $sourceLocation
            );
            $this->stockMovementModel->create(
                $drugId,
                $quantity,
                "transfer:{$transferId}:{$sourceLocation}_to_{$destinationLocation}",
                $_SESSION['user_id'],
                $branchId,
                $destinationLocation
            );

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            sendError('Transfer failed: ' . $e->getMessage(), 500);
            return;
        }

        sendSuccess([
            'id' => $transferId,
            'source_location' => $sourceLocation,
            'destination_location' => $destinationLocation
        ], 'Transfer completed and inventory updated');
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

        if ($transfer['status'] !== 'pending') {
            sendError('Only pending transfers can be updated', 400);
            return;
        }

        if ($status === 'completed') {
            $drug = $this->drugModel->findById($transfer['drug_id']);
            if (!$drug) {
                sendError('Drug not found', 404);
                return;
            }

            global $pdo;
            try {
                $pdo->beginTransaction();

                $available = $this->inventoryModel->getQuantity(
                    $transfer['drug_id'],
                    $transfer['branch_id'],
                    $transfer['source_location'],
                    true
                );
                if ($available < $transfer['quantity']) {
                    $pdo->rollBack();
                    sendError("Insufficient stock in {$transfer['source_location']} to complete transfer", 400);
                    return;
                }

                $moved = $this->inventoryModel->transfer(
                    $transfer['drug_id'],
                    $transfer['branch_id'],
                    $transfer['source_location'],
                    $transfer['destination_location'],
                    $transfer['quantity']
                );
                if (!$moved) {
                    $pdo->rollBack();
                    sendError("Insufficient stock in {$transfer['source_location']} to complete transfer", 400);
                    return;
                }

                $reason = "transfer:{$id}:{$transfer['source_location']}_to_{$transfer['destination_location']}";
                $this->stockMovementModel->create($drug['id'], -$transfer['quantity'], $reason, $_SESSION['user_id'], $transfer['branch_id'], $transfer['source_location']);
                $this->stockMovementModel->create($drug['id'], $transfer['quantity'], $reason, $_SESSION['user_id'], $transfer['branch_id'], $transfer['destination_location']);
                $updated = $this->transferModel->updateStatus($id, $status);

                $pdo->commit();
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                sendError('Failed to update status: ' . $e->getMessage(), 500);
                return;
            }
        } else {
            $updated = $this->transferModel->updateStatus($id, $status);
        }

        if (!$updated) {
            sendError('Failed to update status', 500);
            return;
        }

        sendSuccess(null, 'Transfer status updated successfully');
    }
}
