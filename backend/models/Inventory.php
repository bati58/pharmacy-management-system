<?php

class Inventory
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public static function normalizeLocation($location)
    {
        $location = strtolower(trim((string)$location));
        if (in_array($location, ['store', 'stock'], true)) {
            return 'store';
        }
        if (in_array($location, ['dispensary', 'dispensary_stock', 'shelf'], true)) {
            return 'dispensary';
        }
        return null;
    }

    public static function locations()
    {
        return ['store', 'dispensary'];
    }

    public function ensureRow($drugId, $branchId, $location, $quantity = 0)
    {
        $location = self::normalizeLocation($location);
        if ($location === null) {
            return false;
        }

        $quantity = max(0, (int)$quantity);
        $stmt = $this->db->prepare("
            INSERT INTO inventory (drug_id, branch_id, location, quantity)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = quantity
        ");
        return $stmt->execute([(int)$drugId, (int)$branchId, $location, $quantity]);
    }

    public function ensureDrugRows($drugId, $branchId, $storeQuantity = 0, $dispensaryQuantity = 0)
    {
        return $this->setQuantity($drugId, $branchId, 'store', $storeQuantity)
            && $this->setQuantity($drugId, $branchId, 'dispensary', $dispensaryQuantity);
    }

    public function getQuantity($drugId, $branchId, $location, $forUpdate = false)
    {
        $location = self::normalizeLocation($location);
        if ($location === null) {
            return null;
        }

        $this->ensureRow($drugId, $branchId, $location);

        $sql = "
            SELECT quantity
            FROM inventory
            WHERE drug_id = ? AND branch_id = ? AND location = ?
        ";
        if ($forUpdate) {
            $sql .= " FOR UPDATE";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$drugId, (int)$branchId, $location]);
        $row = $stmt->fetch();

        return $row ? (int)$row['quantity'] : 0;
    }

    public function setQuantity($drugId, $branchId, $location, $quantity)
    {
        $location = self::normalizeLocation($location);
        $quantity = (int)$quantity;
        if ($location === null || $quantity < 0) {
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO inventory (drug_id, branch_id, location, quantity)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)
        ");
        return $stmt->execute([(int)$drugId, (int)$branchId, $location, $quantity]);
    }

    public function adjustQuantity($drugId, $branchId, $location, $change)
    {
        $location = self::normalizeLocation($location);
        $change = (int)$change;
        if ($location === null || $change === 0) {
            return $change === 0;
        }

        $this->ensureRow($drugId, $branchId, $location);

        $stmt = $this->db->prepare("
            UPDATE inventory
            SET quantity = quantity + ?
            WHERE drug_id = ?
              AND branch_id = ?
              AND location = ?
              AND quantity + ? >= 0
        ");
        $stmt->execute([$change, (int)$drugId, (int)$branchId, $location, $change]);

        return $stmt->rowCount() === 1;
    }

    public function transfer($drugId, $branchId, $sourceLocation, $destinationLocation, $quantity)
    {
        $sourceLocation = self::normalizeLocation($sourceLocation);
        $destinationLocation = self::normalizeLocation($destinationLocation);
        $quantity = (int)$quantity;

        if (
            $sourceLocation === null ||
            $destinationLocation === null ||
            $sourceLocation === $destinationLocation ||
            $quantity <= 0
        ) {
            return false;
        }

        $deducted = $this->adjustQuantity($drugId, $branchId, $sourceLocation, -$quantity);
        if (!$deducted) {
            return false;
        }

        return $this->adjustQuantity($drugId, $branchId, $destinationLocation, $quantity);
    }
}
