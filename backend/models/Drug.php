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
            $conditions[] = "(d.name LIKE ? OR d.batch LIKE ?)";
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

    public function create($name, $category, $batch, $stock, $price, $cost_price, $manufacturer, $supplier, $expiry, $branchId)
    {
        $stmt = $this->db->prepare("
            INSERT INTO drugs (name, category, batch, stock, price, cost_price, manufacturer, supplier, expiry_date, branch_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $category, $batch, $stock, $price, $cost_price, $manufacturer, $supplier, $expiry, $branchId]);
        return $this->db->lastInsertId();
    }

    public function update($id, $name, $category, $batch, $price, $cost_price, $manufacturer, $supplier, $expiry)
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
        if ($batch !== null) {
            $fields[] = "batch = ?";
            $params[] = $batch;
        }
        if ($price !== null) {
            $fields[] = "price = ?";
            $params[] = $price;
        }
        if ($cost_price !== null) {
            $fields[] = "cost_price = ?";
            $params[] = $cost_price;
        }
        if ($manufacturer !== null) {
            $fields[] = "manufacturer = ?";
            $params[] = $manufacturer;
        }
        if ($supplier !== null) {
            $fields[] = "supplier = ?";
            $params[] = $supplier;
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

    public function updateStock($id, $newStock = null, $change = null, $userId = null, $reason = 'manual')
    {
        $this->db->beginTransaction();
        try {
            if ($newStock !== null) {
                // Get current stock to calculate change
                $drug = $this->findById($id);
                $oldStock = $drug['stock'];
                $actualChange = $newStock - $oldStock;
                
                $stmt = $this->db->prepare("UPDATE drugs SET stock = ? WHERE id = ?");
                $stmt->execute([$newStock, $id]);
            } elseif ($change !== null) {
                $actualChange = $change;
                $stmt = $this->db->prepare("UPDATE drugs SET stock = stock + ? WHERE id = ?");
                $stmt->execute([$change, $id]);
            } else {
                $this->db->rollBack();
                return false;
            }

            // Log movement if user ID is provided
            if ($userId !== null && $actualChange != 0) {
                $stmtLog = $this->db->prepare("INSERT INTO stock_movements (drug_id, quantity_change, reason, user_id) VALUES (?, ?, ?, ?)");
                $stmtLog->execute([$id, $actualChange, $reason, $userId]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
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
        // Force delete related records for class project requirements
        $this->db->prepare("DELETE FROM sale_items WHERE drug_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM transfers WHERE drug_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM stock_movements WHERE drug_id = ?")->execute([$id]);
        
        $stmt = $this->db->prepare("DELETE FROM drugs WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
