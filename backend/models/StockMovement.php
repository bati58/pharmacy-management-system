<?php
require_once __DIR__ . '/Inventory.php';

class StockMovement
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function create($drugId, $quantityChange, $reason, $userId, $branchId = null, $location = null)
    {
        if ($branchId === null) {
            $stmt = $this->db->prepare("SELECT branch_id FROM drugs WHERE id = ?");
            $stmt->execute([(int)$drugId]);
            $drug = $stmt->fetch();
            if (!$drug) {
                return false;
            }
            $branchId = (int)$drug['branch_id'];
        }

        $location = $location !== null ? Inventory::normalizeLocation($location) : null;
        $stmt = $this->db->prepare("
            INSERT INTO stock_movements (drug_id, branch_id, location, quantity_change, reason, user_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([(int)$drugId, (int)$branchId, $location, (int)$quantityChange, $reason, (int)$userId]);
    }

    public function getAll($branchId = null, $reason = null)
    {
        $sql = "
            SELECT sm.*, d.name as drug_name, d.batch, u.name as user_name, b.name as branch_name
            FROM stock_movements sm
            JOIN drugs d ON sm.drug_id = d.id
            JOIN users u ON sm.user_id = u.id
            JOIN branches b ON sm.branch_id = b.id
            WHERE 1=1
        ";
        $params = [];
        if ($branchId) {
            $sql .= " AND sm.branch_id = ?";
            $params[] = $branchId;
        }
        if ($reason) {
            $sql .= " AND sm.reason LIKE ?";
            $params[] = "%$reason%";
        }
        $sql .= " ORDER BY sm.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
