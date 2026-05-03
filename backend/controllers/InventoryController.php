<?php
require_once __DIR__ . '/../models/Drug.php';
require_once __DIR__ . '/../models/StockMovement.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/response.php';

class InventoryController
{
    private $drugModel;
    private $stockMovementModel;

    public function __construct()
    {
        global $pdo;
        $this->drugModel = new Drug($pdo);
        $this->stockMovementModel = new StockMovement($pdo);
        AuthMiddleware::check();
    }

    public function updateStock($id)
    {
        AuthMiddleware::requireRole(['manager', 'store_keeper']);

        $data = json_decode(file_get_contents('php://input'), true);
        $quantityChange = $data['quantity_change'] ?? 0;
        $reason = $data['reason'] ?? 'manual';

        if ($quantityChange == 0) {
            sendError('Quantity change must not be zero', 400);
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

        $newStock = $drug['stock'] + $quantityChange;
        if ($newStock < 0) {
            sendError('Insufficient stock', 400);
            return;
        }

        $updated = $this->drugModel->updateStock($id, $newStock);
        if ($updated) {
            $this->stockMovementModel->create(
                $id,
                (int)$quantityChange,
                (string)$reason,
                (int)($_SESSION['user_id'] ?? 0)
            );
            sendSuccess(['new_stock' => $newStock], 'Stock updated');
        } else {
            sendError('Failed to update stock', 500);
        }
    }

    public function lowStockAlerts()
    {
        AuthMiddleware::requireRole(['manager', 'pharmacist', 'store_keeper']);

        $threshold = (int)($_GET['threshold'] ?? 10);
        $drugs = $this->drugModel->getLowStock($threshold);

        if (($_SESSION['role'] ?? '') !== 'manager') {
            $bid = (int)($_SESSION['branch_id'] ?? 0);
            $drugs = array_values(array_filter($drugs, function ($d) use ($bid) {
                return (int)$d['branch_id'] === $bid;
            }));
        }

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
