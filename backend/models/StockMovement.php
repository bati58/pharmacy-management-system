<?php
class StockMovement
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function create($drugId, $quantityChange, $reason, $userId)
    {
        $stmt = $this->db->prepare("
            INSERT INTO stock_movements (drug_id, quantity_change, reason, user_id, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$drugId, $quantityChange, $reason, $userId]);
    }

    public function getAll($branchId = null, $reason = null)
    {
        $sql = "
            SELECT sm.*, d.name as drug_name, d.batch, u.name as user_name, b.name as branch_name
            FROM stock_movements sm
            JOIN drugs d ON sm.drug_id = d.id
            JOIN users u ON sm.user_id = u.id
            JOIN branches b ON d.branch_id = b.id
            WHERE 1=1
        ";
        $params = [];
        if ($branchId) {
            $sql .= " AND d.branch_id = ?";
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
