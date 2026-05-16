<?php
class Branch
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getAll()
    {
        $stmt = $this->db->query("SELECT * FROM branches ORDER BY name");
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM branches WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($name, $address, $phone)
    {
        $stmt = $this->db->prepare("INSERT INTO branches (name, address, phone) VALUES (?, ?, ?)");
        $stmt->execute([$name, $address, $phone]);
        return $this->db->lastInsertId();
    }

    public function update($id, $name, $address, $phone)
    {
        $stmt = $this->db->prepare("UPDATE branches SET name = ?, address = ?, phone = ? WHERE id = ?");
        return $stmt->execute([$name, $address, $phone, $id]);
    }

    public function delete($id)
    {
        // Force delete related records for class project requirements
        $this->db->prepare("DELETE FROM sale_items WHERE drug_id IN (SELECT id FROM drugs WHERE branch_id = ?)")->execute([$id]);
        $this->db->prepare("DELETE FROM stock_movements WHERE drug_id IN (SELECT id FROM drugs WHERE branch_id = ?)")->execute([$id]);
        $this->db->prepare("DELETE FROM transfers WHERE branch_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM sales WHERE branch_id = ?")->execute([$id]);
        
        $stmt = $this->db->prepare("DELETE FROM branches WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
