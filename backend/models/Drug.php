<?php
class Drug
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getAll($branchId = null, $search = null)
    {
        $sql = "SELECT d.*, b.name as branch_name FROM drugs d JOIN branches b ON d.branch_id = b.id";
        $params = [];
        $conditions = [];

        if ($branchId) {
            $conditions[] = "d.branch_id = ?";
            $params[] = $branchId;
        }
        if ($search) {
            $conditions[] = "(d.name LIKE ? OR d.batch LIKE ? OR d.category LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY d.expiry_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM drugs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($name, $category, $manufacturer, $supplier, $batch, $stock, $price, $expiry, $branchId)
    {
        $stmt = $this->db->prepare("
            INSERT INTO drugs (name, category, manufacturer, supplier, batch, stock, price, expiry_date, branch_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $category, $manufacturer ?: null, $supplier ?: null, $batch, $stock, $price, $expiry, $branchId]);
        return $this->db->lastInsertId();
    }

    public function update($id, $name, $category, $manufacturer, $supplier, $batch, $price, $expiry)
    {
        $fields = [];
        $params = [];
        if ($name !== null) {
            $fields[] = "name = ?";
            $params[] = $name;
        }
        if ($category !== null) {
            $fields[] = "category = ?";
            $params[] = $category;
        }
        if ($manufacturer !== null) {
            $fields[] = "manufacturer = ?";
            $params[] = $manufacturer;
        }
        if ($supplier !== null) {
            $fields[] = "supplier = ?";
            $params[] = $supplier;
        }
        if ($batch !== null) {
            $fields[] = "batch = ?";
            $params[] = $batch;
        }
        if ($price !== null) {
            $fields[] = "price = ?";
            $params[] = $price;
        }
        if ($expiry !== null) {
            $fields[] = "expiry_date = ?";
            $params[] = $expiry;
        }
        if (empty($fields)) return false;
        $params[] = $id;
        $sql = "UPDATE drugs SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function updateStock($id, $newStock = null, $change = null)
    {
        if ($newStock !== null) {
            $stmt = $this->db->prepare("UPDATE drugs SET stock = ? WHERE id = ?");
            return $stmt->execute([$newStock, $id]);
        } elseif ($change !== null) {
            $stmt = $this->db->prepare("UPDATE drugs SET stock = stock + ? WHERE id = ?");
            return $stmt->execute([$change, $id]);
        }
        return false;
    }

    public function getLowStock($threshold = 10)
    {
        $stmt = $this->db->prepare("
            SELECT d.*, b.name as branch_name 
            FROM drugs d 
            JOIN branches b ON d.branch_id = b.id 
            WHERE d.stock <= ? 
            ORDER BY d.stock ASC
        ");
        $stmt->execute([$threshold]);
        return $stmt->fetchAll();
    }

    public function getExpiringSoon($days = 30)
    {
        $stmt = $this->db->prepare("
            SELECT d.*, b.name as branch_name 
            FROM drugs d 
            JOIN branches b ON d.branch_id = b.id 
            WHERE d.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY d.expiry_date ASC
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }

    public function getSlowMoving($limit = 10)
    {
        // Slow-moving: drugs with low sales volume (assuming sale_items table)
        $stmt = $this->db->prepare("
            SELECT d.*, COALESCE(SUM(si.quantity), 0) as total_sold
            FROM drugs d
            LEFT JOIN sale_items si ON d.id = si.drug_id
            LEFT JOIN sales s ON si.sale_id = s.id AND s.sale_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY d.id
            ORDER BY total_sold ASC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM drugs WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
