<?php
class User
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAll()
    {
        $stmt = $this->db->query("
            SELECT u.*, b.name as branch_name 
            FROM users u 
            LEFT JOIN branches b ON u.branch_id = b.id 
            ORDER BY u.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function create($name, $email, $hashedPassword, $role, $branchId, $status)
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (name, email, password, role, branch_id, status) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $email, $hashedPassword, $role, $branchId, $status]);
        return $this->db->lastInsertId();
    }

    public function update($id, $name, $role, $branchId, $status)
    {
        $fields = [];
        $params = [];
        if ($name !== null) {
            $fields[] = "name = ?";
            $params[] = $name;
        }
        if ($role !== null) {
            $fields[] = "role = ?";
            $params[] = $role;
        }
        if ($branchId !== null) {
            $fields[] = "branch_id = ?";
            $params[] = $branchId;
        }
        if ($status !== null) {
            $fields[] = "status = ?";
            $params[] = $status;
        }
        if (empty($fields)) return false;
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->db->prepare("UPDATE users SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
