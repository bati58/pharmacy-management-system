<?php
require_once __DIR__ . '/../models/Branch.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/response.php';

class BranchController
{
    private $branchModel;
    private $db;

    public function __construct()
    {
        global $pdo;
        $this->db = $pdo;
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
        try {
            $this->db->beginTransaction();

            // 1) Remove branch transfers.
            $stmt = $this->db->prepare("DELETE FROM transfers WHERE branch_id = ?");
            $stmt->execute([$id]);

            // 2) Remove sale items and sales for this branch.
            $stmt = $this->db->prepare("SELECT id FROM sales WHERE branch_id = ?");
            $stmt->execute([$id]);
            $saleIds = array_column($stmt->fetchAll(), 'id');
            if (!empty($saleIds)) {
                $in = implode(',', array_fill(0, count($saleIds), '?'));
                $stmtItems = $this->db->prepare("DELETE FROM sale_items WHERE sale_id IN ($in)");
                $stmtItems->execute($saleIds);

                $stmtSales = $this->db->prepare("DELETE FROM sales WHERE id IN ($in)");
                $stmtSales->execute($saleIds);
            }

            // 3) Remove items tied to drugs in this branch (safety).
            $stmt = $this->db->prepare("SELECT id FROM drugs WHERE branch_id = ?");
            $stmt->execute([$id]);
            $drugIds = array_column($stmt->fetchAll(), 'id');
            if (!empty($drugIds)) {
                $inDrugs = implode(',', array_fill(0, count($drugIds), '?'));
                $stmtItemsByDrug = $this->db->prepare("DELETE FROM sale_items WHERE drug_id IN ($inDrugs)");
                $stmtItemsByDrug->execute($drugIds);

                $stmtStock = $this->db->prepare("DELETE FROM stock_movements WHERE drug_id IN ($inDrugs)");
                $stmtStock->execute($drugIds);
            }

            // 4) Delete drugs in branch.
            $stmt = $this->db->prepare("DELETE FROM drugs WHERE branch_id = ?");
            $stmt->execute([$id]);

            // 5) Delete users in branch (after clearing their own transactional refs).
            $stmt = $this->db->prepare("SELECT id FROM users WHERE branch_id = ?");
            $stmt->execute([$id]);
            $userIds = array_column($stmt->fetchAll(), 'id');
            foreach ($userIds as $uid) {
                $stmtUserTransfers = $this->db->prepare("DELETE FROM transfers WHERE created_by = ?");
                $stmtUserTransfers->execute([$uid]);

                $stmtUserStock = $this->db->prepare("DELETE FROM stock_movements WHERE user_id = ?");
                $stmtUserStock->execute([$uid]);

                $stmtUserSales = $this->db->prepare("SELECT id FROM sales WHERE pharmacist_id = ?");
                $stmtUserSales->execute([$uid]);
                $userSaleIds = array_column($stmtUserSales->fetchAll(), 'id');
                if (!empty($userSaleIds)) {
                    $inSales = implode(',', array_fill(0, count($userSaleIds), '?'));
                    $stmtUserItems = $this->db->prepare("DELETE FROM sale_items WHERE sale_id IN ($inSales)");
                    $stmtUserItems->execute($userSaleIds);

                    $stmtDeleteSales = $this->db->prepare("DELETE FROM sales WHERE id IN ($inSales)");
                    $stmtDeleteSales->execute($userSaleIds);
                }
            }
            $stmt = $this->db->prepare("DELETE FROM users WHERE branch_id = ?");
            $stmt->execute([$id]);

            // 6) Finally remove branch.
            $deleted = $this->branchModel->delete($id);
            if ($deleted) {
                $this->db->commit();
                sendSuccess(null, 'Branch deleted successfully (including related records).');
            } else {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                sendError('Branch not found', 404);
            }
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            sendError('Failed to delete branch. ' . $e->getMessage(), 500);
        }
    }
}
