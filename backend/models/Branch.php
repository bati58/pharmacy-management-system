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
        $stmt = $this->db->prepare("DELETE FROM branches WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
