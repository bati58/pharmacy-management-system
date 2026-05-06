<?php
require_once __DIR__ . '/../models/Drug.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/response.php';

class DrugController
{
    private $drugModel;

    public function __construct()
    {
        global $pdo;
        $this->drugModel = new Drug($pdo);
        AuthMiddleware::check();
    }

    public function index()
    {
        $branchId = $_GET['branch_id'] ?? null;
        
        // Security: Pharmacists can ONLY see drugs from their own branch
        if ($_SESSION['role'] === 'pharmacist') {
            $branchId = $_SESSION['branch_id'];
        }
        
        $search = $_GET['search'] ?? null;
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
        sendSuccess($drug);
    }

    public function create()
    {
        AuthMiddleware::requireRole(['store_keeper']);
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? '';
        $category = $data['category'] ?? '';
        $batch = $data['batch'] ?? '';
        $stock = $data['stock'] ?? 0;
        $price = $data['price'] ?? 0;
        $cost_price = $data['cost_price'] ?? 0;
        $manufacturer = $data['manufacturer'] ?? '';
        $supplier = $data['supplier'] ?? '';
        $expiry = $data['expiry_date'] ?? '';
        $branchId = $data['branch_id'] ?? $_SESSION['branch_id'];

        if (empty($name) || empty($batch) || empty($expiry) || $price <= 0) {
            sendError('Name, batch, expiry date and price are required', 400);
            return;
        }

        $id = $this->drugModel->create($name, $category, $batch, $stock, $price, $cost_price, $manufacturer, $supplier, $expiry, $branchId);
        sendSuccess(['id' => $id], 'Drug added successfully');
    }

    public function update($id)
    {
        AuthMiddleware::requireRole(['manager', 'store_keeper']);
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? null;
        $category = $data['category'] ?? null;
        $batch = $data['batch'] ?? null;
        $price = $data['price'] ?? null;
        $cost_price = $data['cost_price'] ?? null;
        $manufacturer = $data['manufacturer'] ?? null;
        $supplier = $data['supplier'] ?? null;
        $expiry = $data['expiry_date'] ?? null;

        $updated = $this->drugModel->update($id, $name, $category, $batch, $price, $cost_price, $manufacturer, $supplier, $expiry);
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
            $deleted = $this->drugModel->delete($id);
            if ($deleted) {
                sendSuccess(null, 'Drug deleted');
            } else {
                sendError('Drug not found', 404);
            }
        } catch (PDOException $e) {
            sendError('Database error: ' . $e->getMessage(), 500);
        }
    }
}
