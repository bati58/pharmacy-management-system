<?php
class Transfer
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getAll($branchId = null)
    {
        $sql = "
            SELECT
                t.*,
                t.source_location as from_location,
                t.destination_location as to_location,
                d.name as drug_name,
                u.name as created_by_name,
                b.name as branch_name
            FROM transfers t 
            JOIN drugs d ON t.drug_id = d.id 
            JOIN users u ON t.created_by = u.id
            JOIN branches b ON t.branch_id = b.id
        ";
        $params = [];
        if ($branchId) {
            $sql .= " WHERE t.branch_id = ?";
            $params[] = $branchId;
        }
        $sql .= " ORDER BY t.transfer_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT
                t.*,
                t.source_location as from_location,
                t.destination_location as to_location
            FROM transfers t
            WHERE t.id = ?
        ");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function create($drugId, $quantity, $sourceLocation, $destinationLocation, $branchId, $createdBy, $status = 'completed')
    {
        $stmt = $this->db->prepare("
            INSERT INTO transfers (drug_id, quantity, source_location, destination_location, branch_id, created_by, status, transfer_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$drugId, $quantity, $sourceLocation, $destinationLocation, $branchId, $createdBy, $status]);
        return $this->db->lastInsertId();
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->db->prepare("UPDATE transfers SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}
