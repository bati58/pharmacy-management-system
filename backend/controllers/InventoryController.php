<?php
require_once __DIR__ . '/../models/Drug.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/alert.php';

class InventoryController
{
    private $drugModel;

    public function __construct()
    {
        global $pdo;
        $this->drugModel = new Drug($pdo);
        AuthMiddleware::check();
    }

    public function updateStock($id)
    {
        AuthMiddleware::requireRole(['store_keeper']);
        $data = json_decode(file_get_contents('php://input'), true);
        $quantityChange = $data['quantity_change'] ?? 0; // can be positive (receive) or negative (damaged/expired)
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

        $newStock = $drug['stock'] + $quantityChange;
        if ($newStock < 0) {
            sendError('Insufficient stock', 400);
            return;
        }

        $updated = $this->drugModel->updateStock($id, $newStock, null, $_SESSION['user_id'], $reason);
        if ($updated) {
            // Automated Low Stock Alert (SRS §3.2)
            checkAndNotifyLowStock($id, $newStock);
            sendSuccess(['new_stock' => $newStock], 'Stock updated');
        } else {
            sendError('Failed to update stock', 500);
        }
    }

    public function lowStockAlerts()
    {
        AuthMiddleware::requireRole(['manager', 'store_keeper']);
        $threshold = $_GET['threshold'] ?? 10;
        $drugs = $this->drugModel->getLowStock($threshold);
        sendSuccess($drugs);
    }

    public function expiringSoon()
    {
        AuthMiddleware::requireRole(['manager', 'store_keeper']);
        $days = $_GET['days'] ?? 30;
        $drugs = $this->drugModel->getExpiringSoon($days);
        sendSuccess($drugs);
    }
}
