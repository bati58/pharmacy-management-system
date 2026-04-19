<?php
require_once __DIR__ . '/../models/Branch.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/response.php';

class BranchController
{
    private $branchModel;

    public function __construct()
    {
        global $pdo;
        $this->branchModel = new Branch($pdo);
        AuthMiddleware::check();
        AuthMiddleware::requireRole(['manager']);
    }

    public function index()
    {
        $branches = $this->branchModel->getAll();
        sendSuccess($branches);
    }

    public function show($id)
    {
        $branch = $this->branchModel->findById($id);
        if (!$branch) {
            sendError('Branch not found', 404);
            return;
        }
        sendSuccess($branch);
    }

    public function create()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? '';
        $address = $data['address'] ?? '';
        $phone = $data['phone'] ?? '';

        if (empty($name)) {
            sendError('Branch name is required', 400);
            return;
        }

        $id = $this->branchModel->create($name, $address, $phone);
        sendSuccess(['id' => $id], 'Branch created successfully');
    }

    public function update($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? '';
        $address = $data['address'] ?? '';
        $phone = $data['phone'] ?? '';

        $updated = $this->branchModel->update($id, $name, $address, $phone);
        if ($updated) {
            sendSuccess(null, 'Branch updated successfully');
        } else {
            sendError('Branch not found or update failed', 404);
        }
    }

    public function delete($id)
    {
        $deleted = $this->branchModel->delete($id);
        if ($deleted) {
            sendSuccess(null, 'Branch deleted successfully');
        } else {
            sendError('Branch not found', 404);
        }
    }
}
