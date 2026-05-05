<?php
require_once __DIR__ . '/../models/Drug.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/StockMovement.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/response.php';

class InventoryController
{
    private $drugModel;
    private $inventoryModel;
    private $stockMovementModel;

    public function __construct()
    {
        global $pdo;
        $this->drugModel = new Drug($pdo);
        $this->inventoryModel = new Inventory($pdo);
        $this->stockMovementModel = new StockMovement($pdo);
        AuthMiddleware::check();
    }

    public function updateStock($id)
    {
        AuthMiddleware::requireRole(['manager', 'store_keeper']);

        $data = json_decode(file_get_contents('php://input'), true);
        $quantityChange = (int)($data['quantity_change'] ?? 0);
        $reason = $data['reason'] ?? 'manual';
        $location = Inventory::normalizeLocation($data['location'] ?? 'store');

        if ($quantityChange == 0) {
            sendError('Quantity change must not be zero', 400);
            return;
        }
        if ($location === null) {
            sendError('Location must be store or dispensary', 400);
            return;
        }
        if (($_SESSION['role'] ?? '') === 'store_keeper' && $location !== 'store') {
            sendError('Store keepers can only adjust store inventory. Use a transfer to move stock to the dispensary.', 403);
            return;
        }

        $drug = $this->drugModel->findById($id);
        if (!$drug) {
            sendError('Drug not found', 404);
            return;
        }

        if (($_SESSION['role'] ?? '') !== 'manager') {
            if ((int)$drug['branch_id'] !== (int)($_SESSION['branch_id'] ?? 0)) {
                sendError('You can only adjust stock in your branch', 403);
                return;
            }
        }

        global $pdo;
        try {
            $pdo->beginTransaction();

            $updated = $this->inventoryModel->adjustQuantity($id, $drug['branch_id'], $location, $quantityChange);
            if (!$updated) {
                $pdo->rollBack();
                sendError('Insufficient stock', 400);
                return;
            }

            $movementCreated = $this->stockMovementModel->create(
                $id,
                (int)$quantityChange,
                (string)$reason,
                (int)($_SESSION['user_id'] ?? 0),
                (int)$drug['branch_id'],
                $location
            );
            if (!$movementCreated) {
                $pdo->rollBack();
                sendError('Failed to record stock movement', 500);
                return;
            }

            $newStock = $this->inventoryModel->getQuantity($id, $drug['branch_id'], $location);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            sendError('Failed to update stock: ' . $e->getMessage(), 500);
            return;
        }

        sendSuccess(['new_stock' => $newStock, 'location' => $location], 'Stock updated');
    }

    public function lowStockAlerts()
    {
        AuthMiddleware::requireRole(['manager', 'pharmacist', 'store_keeper']);

        $threshold = (int)($_GET['threshold'] ?? 10);
        $location = Inventory::normalizeLocation($_GET['location'] ?? null);
        if (($_SESSION['role'] ?? '') === 'pharmacist') {
            $location = 'dispensary';
        } elseif (($_SESSION['role'] ?? '') === 'store_keeper') {
            $location = 'store';
        }
        $drugs = $this->drugModel->getLowStock($threshold, $location);

        if (($_SESSION['role'] ?? '') !== 'manager') {
            $bid = (int)($_SESSION['branch_id'] ?? 0);
            $drugs = array_values(array_filter($drugs, function ($d) use ($bid) {
                return (int)$d['branch_id'] === $bid;
            }));
        }

        $drugs = array_map(function ($drug) use ($location) {
            $drug['stock'] = (int)($drug['stock'] ?? 0);
            $drug['dispensary_stock'] = (int)($drug['dispensary_stock'] ?? 0);
            if ($location === 'dispensary') {
                $drug['location'] = 'dispensary';
                $drug['location_quantity'] = $drug['dispensary_stock'];
                if (($_SESSION['role'] ?? '') === 'pharmacist') {
                    $drug['stock'] = 0;
                }
            } elseif ($location === 'store') {
                $drug['location'] = 'store';
                $drug['location_quantity'] = $drug['stock'];
            } else {
                $drug['location'] = 'all';
                $drug['location_quantity'] = min($drug['stock'], $drug['dispensary_stock']);
            }
            return $drug;
        }, $drugs);

        sendSuccess($drugs);
    }

    public function expiringSoon()
    {
        AuthMiddleware::requireRole(['manager', 'pharmacist', 'store_keeper']);

        $days = (int)($_GET['days'] ?? 30);
        $drugs = $this->drugModel->getExpiringSoon($days);

        if (($_SESSION['role'] ?? '') !== 'manager') {
            $bid = (int)($_SESSION['branch_id'] ?? 0);
            $drugs = array_values(array_filter($drugs, function ($d) use ($bid) {
                return (int)$d['branch_id'] === $bid;
            }));
        }

        sendSuccess($drugs);
    }
}
