<?php
require_once __DIR__ . '/../models/Drug.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/response.php';

class DrugController
{
    private $drugModel;
    private $db;

    public function __construct()
    {
        global $pdo;
        $this->db = $pdo;
        $this->drugModel = new Drug($pdo);
        AuthMiddleware::check();
    }

    public function index()
    {
        $search = $_GET['search'] ?? null;
        if (($_SESSION['role'] ?? '') === 'manager') {
            $branchId = $_GET['branch_id'] ?? null;
        } else {
            $branchId = $_SESSION['branch_id'] ?? null;
        }
        $drugs = $this->drugModel->getAll($branchId, $search);
        sendSuccess($drugs);
    }

    public function show($id)
    {
        $drug = $this->drugModel->findById($id);
        if (!$drug) {
            sendError('Drug not found', 404);
            return;
        }
        if (($_SESSION['role'] ?? '') !== 'manager') {
            if ((int)$drug['branch_id'] !== (int)($_SESSION['branch_id'] ?? 0)) {
                sendError('Forbidden', 403);
                return;
            }
        }
        sendSuccess($drug);
    }

    public function create()
    {
        AuthMiddleware::requireRole(['manager', 'store_keeper']);
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? '';
        $category = $data['category'] ?? '';
        $manufacturer = $data['manufacturer'] ?? '';
        $supplier = $data['supplier'] ?? '';
        $batch = $data['batch'] ?? '';
        $stock = $data['stock'] ?? 0;
        $price = $data['price'] ?? 0;
        $expiry = $data['expiry_date'] ?? '';
        $branchId = $data['branch_id'] ?? $_SESSION['branch_id'];

        if (empty($name) || empty($batch) || empty($expiry) || $price <= 0) {
            sendError('Name, batch, expiry date and price are required', 400);
            return;
        }

        $id = $this->drugModel->create($name, $category, $manufacturer, $supplier, $batch, $stock, $price, $expiry, $branchId);
        sendSuccess(['id' => $id], 'Drug added successfully');
    }

    public function update($id)
    {
        AuthMiddleware::requireRole(['manager', 'store_keeper']);
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? null;
        $category = $data['category'] ?? null;
        $manufacturer = $data['manufacturer'] ?? null;
        $supplier = $data['supplier'] ?? null;
        $batch = $data['batch'] ?? null;
        $price = $data['price'] ?? null;
        $expiry = $data['expiry_date'] ?? null;

        $updated = $this->drugModel->update($id, $name, $category, $manufacturer, $supplier, $batch, $price, $expiry);
        if ($updated) {
            sendSuccess(null, 'Drug updated successfully');
        } else {
            sendError('Drug not found', 404);
        }
    }

    public function delete($id)
    {
        AuthMiddleware::requireRole(['manager']);
        try {
            $this->db->beginTransaction();

            // Remove child rows that block drug deletion.
            $stmt = $this->db->prepare("DELETE FROM sale_items WHERE drug_id = ?");
            $stmt->execute([$id]);

            $stmt = $this->db->prepare("DELETE FROM transfers WHERE drug_id = ?");
            $stmt->execute([$id]);

            $stmt = $this->db->prepare("DELETE FROM stock_movements WHERE drug_id = ?");
            $stmt->execute([$id]);

            $deleted = $this->drugModel->delete($id);
            if ($deleted) {
                $this->db->commit();
                sendSuccess(null, 'Drug deleted successfully (including related records).');
            } else {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                sendError('Drug not found', 404);
            }
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $msg = $e->getMessage();
            if (stripos($msg, 'foreign key constraint fails') !== false) {
                sendError('Cannot delete this drug because related sale, transfer, or stock movement records exist.', 409);
                return;
            }
            sendError('Failed to delete drug. ' . $msg, 500);
        }
    }
}
