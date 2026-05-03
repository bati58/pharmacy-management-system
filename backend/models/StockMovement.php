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
}
