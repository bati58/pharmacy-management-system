<?php
require_once __DIR__ . '/Inventory.php';

class Drug
{
    private $db;
    private $inventoryModel;

    public function __construct($pdo)
    {
        $this->db = $pdo;
        $this->inventoryModel = new Inventory($pdo);
    }

    public function getAll($branchId = null, $search = null, $location = null)
    {
        $location = Inventory::normalizeLocation($location);
        $sql = "
            SELECT
                d.*,
                b.name as branch_name,
                COALESCE(inv_store.quantity, 0) as stock,
                COALESCE(inv_dispensary.quantity, 0) as dispensary_stock
            FROM drugs d
            JOIN branches b ON d.branch_id = b.id
            LEFT JOIN inventory inv_store
                ON inv_store.drug_id = d.id
                AND inv_store.branch_id = d.branch_id
                AND inv_store.location = 'store'
            LEFT JOIN inventory inv_dispensary
                ON inv_dispensary.drug_id = d.id
                AND inv_dispensary.branch_id = d.branch_id
                AND inv_dispensary.location = 'dispensary'
        ";
        $params = [];
        $conditions = [];

        if ($branchId !== null && $branchId !== '') {
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
        $stmt = $this->db->prepare("
            SELECT
                d.*,
                COALESCE(inv_store.quantity, 0) as stock,
                COALESCE(inv_dispensary.quantity, 0) as dispensary_stock
            FROM drugs d
            LEFT JOIN inventory inv_store
                ON inv_store.drug_id = d.id
                AND inv_store.branch_id = d.branch_id
                AND inv_store.location = 'store'
            LEFT JOIN inventory inv_dispensary
                ON inv_dispensary.drug_id = d.id
                AND inv_dispensary.branch_id = d.branch_id
                AND inv_dispensary.location = 'dispensary'
            WHERE d.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($name, $category, $manufacturer, $supplier, $batch, $stock, $price, $expiry, $branchId, $costPrice = 0, $requiresPrescription = 0, $dispensaryStock = 0)
    {
        $startedTransaction = !$this->db->inTransaction();
        if ($startedTransaction) {
            $this->db->beginTransaction();
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO drugs (name, category, manufacturer, supplier, batch, price, expiry_date, branch_id, cost_price, requires_prescription)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $category, $manufacturer ?: null, $supplier ?: null, $batch, $price, $expiry, $branchId, $costPrice, $requiresPrescription]);
            $drugId = $this->db->lastInsertId();

            $this->inventoryModel->ensureDrugRows($drugId, $branchId, (int)$stock, (int)$dispensaryStock);

            if ($startedTransaction) {
                $this->db->commit();
            }

            return $drugId;
        } catch (Throwable $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function update($id, $name, $category, $manufacturer, $supplier, $batch, $price, $expiry, $costPrice = null, $requiresPrescription = null)
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
        if ($costPrice !== null) {
            $fields[] = "cost_price = ?";
            $params[] = $costPrice;
        }
        if ($requiresPrescription !== null) {
            $fields[] = "requires_prescription = ?";
            $params[] = $requiresPrescription;
        }
        if (empty($fields)) return false;
        $params[] = $id;
        $sql = "UPDATE drugs SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function updateStock($id, $newStock = null, $change = null, $target = 'stock')
    {
        $drug = $this->findById($id);
        if (!$drug) {
            return false;
        }

        $location = Inventory::normalizeLocation($target);
        if ($location === null) {
            return false;
        }

        if ($newStock !== null) {
            return $this->inventoryModel->setQuantity($id, $drug['branch_id'], $location, (int)$newStock);
        } elseif ($change !== null) {
            return $this->inventoryModel->adjustQuantity($id, $drug['branch_id'], $location, (int)$change);
        }
        return false;
    }

    public function getLocationStock($id, $location, $forUpdate = false)
    {
        $drug = $this->findById($id);
        if (!$drug) {
            return null;
        }
        return $this->inventoryModel->getQuantity($id, $drug['branch_id'], $location, $forUpdate);
    }

    public function getLowStock($threshold = 10, $location = null)
    {
        $location = Inventory::normalizeLocation($location);
        $sql = "
            SELECT
                d.*,
                b.name as branch_name,
                COALESCE(inv_store.quantity, 0) as stock,
                COALESCE(inv_dispensary.quantity, 0) as dispensary_stock
            FROM drugs d
            JOIN branches b ON d.branch_id = b.id
            LEFT JOIN inventory inv_store
                ON inv_store.drug_id = d.id
                AND inv_store.branch_id = d.branch_id
                AND inv_store.location = 'store'
            LEFT JOIN inventory inv_dispensary
                ON inv_dispensary.drug_id = d.id
                AND inv_dispensary.branch_id = d.branch_id
                AND inv_dispensary.location = 'dispensary'
        ";
        $params = [];

        if ($location === 'store') {
            $sql .= " WHERE COALESCE(inv_store.quantity, 0) <= ? ORDER BY COALESCE(inv_store.quantity, 0) ASC";
            $params[] = $threshold;
        } elseif ($location === 'dispensary') {
            $sql .= " WHERE COALESCE(inv_dispensary.quantity, 0) <= ? ORDER BY COALESCE(inv_dispensary.quantity, 0) ASC";
            $params[] = $threshold;
        } else {
            $sql .= "
                WHERE COALESCE(inv_store.quantity, 0) <= ?
                   OR COALESCE(inv_dispensary.quantity, 0) <= ?
                ORDER BY LEAST(COALESCE(inv_store.quantity, 0), COALESCE(inv_dispensary.quantity, 0)) ASC
            ";
            $params[] = $threshold;
            $params[] = $threshold;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return array_map(function ($row) use ($location) {
            $row['stock'] = (int)($row['stock'] ?? 0);
            $row['dispensary_stock'] = (int)($row['dispensary_stock'] ?? 0);
            if ($location === 'store') {
                $row['location'] = 'store';
                $row['location_quantity'] = $row['stock'];
            } elseif ($location === 'dispensary') {
                $row['location'] = 'dispensary';
                $row['location_quantity'] = $row['dispensary_stock'];
            } else {
                $row['location'] = 'all';
                $row['location_quantity'] = min($row['stock'], $row['dispensary_stock']);
            }
            return $row;
        }, $rows);
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
