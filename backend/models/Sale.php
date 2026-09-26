<?php
class Sale
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getAll($branchId = null, $pharmacistId = null, $period = 'all')
    {
        $sql = "
            SELECT s.*, u.name as pharmacist_name, b.name as branch_name,
                   (SELECT COUNT(*) FROM sale_items si WHERE si.sale_id = s.id) as items_count
            FROM sales s 
            JOIN users u ON s.pharmacist_id = u.id
            LEFT JOIN branches b ON s.branch_id = b.id
            WHERE 1=1
        ";
        $params = [];
        if ($branchId) {
            $sql .= " AND s.branch_id = ?";
            $params[] = $branchId;
        }
        if ($pharmacistId) {
            $sql .= " AND s.pharmacist_id = ?";
            $params[] = $pharmacistId;
        }

        // Apply period filters (SRS §3.3)
        if ($period === 'today') {
            $sql .= " AND DATE(s.sale_date) = CURDATE()";
        } elseif ($period === 'weekly') {
            $sql .= " AND YEARWEEK(s.sale_date, 1) = YEARWEEK(CURDATE(), 1)";
        } elseif ($period === 'monthly') {
            $sql .= " AND MONTH(s.sale_date) = MONTH(CURDATE()) AND YEAR(s.sale_date) = YEAR(CURDATE())";
        }

        $sql .= " ORDER BY s.sale_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT s.*, u.name as pharmacist_name 
            FROM sales s 
            JOIN users u ON s.pharmacist_id = u.id 
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        $sale = $stmt->fetch();
        if ($sale) {
            // Get items
            $stmt2 = $this->db->prepare("
                SELECT si.*, d.name as drug_name 
                FROM sale_items si 
                JOIN drugs d ON si.drug_id = d.id 
                WHERE si.sale_id = ?
            ");
            $stmt2->execute([$id]);
            $sale['items'] = $stmt2->fetchAll();
        }
        return $sale;
    }

    public function create($invoiceNo, $customerName, $total, $discount, $totalCost, $prescriptionRef, $paymentMethod, $pharmacistId, $branchId)
    {
        $stmt = $this->db->prepare("
            INSERT INTO sales (invoice_no, customer_name, total_amount, discount_amount, total_cost, prescription_ref, payment_method, pharmacist_id, branch_id, sale_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$invoiceNo, $customerName, $total, $discount, $totalCost, $prescriptionRef, $paymentMethod, $pharmacistId, $branchId]);
        return $this->db->lastInsertId();
    }

    public function addItem($saleId, $drugId, $quantity, $price)
    {
        $stmt = $this->db->prepare("
            INSERT INTO sale_items (sale_id, drug_id, quantity, price) 
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$saleId, $drugId, $quantity, $price]);
    }

    private function buildReportFilters($period, $branchId, $startDate, $endDate, $alias = 's')
    {
        $filters = [];
        $params = [];

        if ($startDate && $endDate) {
            $filters[] = "$alias.sale_date >= ?";
            $filters[] = "$alias.sale_date < DATE_ADD(?, INTERVAL 1 DAY)";
            $params[] = $startDate;
            $params[] = $endDate;
        } else {
            if ($period === 'daily') {
                $filters[] = "DATE($alias.sale_date) = CURDATE()";
            } elseif ($period === 'weekly') {
                $filters[] = "YEARWEEK($alias.sale_date, 1) = YEARWEEK(CURDATE(), 1)";
            } elseif ($period === 'monthly') {
                $filters[] = "YEAR($alias.sale_date) = YEAR(CURDATE()) AND MONTH($alias.sale_date) = MONTH(CURDATE())";
            }
        }

        if ($branchId) {
            $filters[] = "$alias.branch_id = ?";
            $params[] = $branchId;
        }

        return [$filters, $params];
    }

    public function getSalesReport($period = 'daily', $branchId = null, $startDate = null, $endDate = null, $pharmacistId = null)
    {
        if ($startDate && $endDate) {
            $dateFormat = "'%Y-%m-%d'";
        } elseif ($period === 'weekly') {
            $dateFormat = "'%x Week %v'";
        } elseif ($period === 'monthly') {
            $dateFormat = "'%Y-%m'";
        } else {
            $dateFormat = "'%Y-%m-%d'";
        }

        [$filters, $params] = $this->buildReportFilters($period, $branchId, $startDate, $endDate);
        $sql = "
            SELECT
                DATE_FORMAT(s.sale_date, $dateFormat) as period,
                COUNT(*) as transaction_count,
                SUM(s.total_amount) as total_revenue,
                SUM(s.total_cost) as total_cost,
                SUM(s.total_amount - s.total_cost) as total_profit,
                AVG(s.total_amount) as avg_sale
            FROM sales s
        ";
        if ($filters) {
            $sql .= ' WHERE ' . implode(' AND ', $filters);
        }
        if ($pharmacistId) {
            $sql .= $filters ? ' AND s.pharmacist_id = ?' : ' WHERE s.pharmacist_id = ?';
            $params[] = $pharmacistId;
        }
        $sql .= ' GROUP BY period ORDER BY MIN(s.sale_date)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getRevenueByBranch($period = 'all', $branchId = null, $startDate = null, $endDate = null)
    {
        [$filters, $params] = $this->buildReportFilters($period, $branchId, $startDate, $endDate);
        $sql = "
            SELECT
                b.name as branch_name,
                COALESCE(SUM(s.total_amount), 0) as revenue,
                COALESCE(SUM(s.total_amount - s.total_cost), 0) as profit,
                COUNT(s.id) as sales_count
            FROM branches b
            LEFT JOIN sales s ON b.id = s.branch_id
        ";
        if ($filters) {
            $sql .= ' AND ' . implode(' AND ', $filters);
        }
        if ($branchId) {
            $sql .= ' WHERE b.id = ?';
            $params[] = $branchId;
        }
        $sql .= ' GROUP BY b.id ORDER BY b.name';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getRevenueByPharmacist($period = 'all', $branchId = null, $startDate = null, $endDate = null)
    {
        [$filters, $params] = $this->buildReportFilters($period, $branchId, $startDate, $endDate);
        $sql = "
            SELECT
                u.name as pharmacist_name,
                COALESCE(SUM(s.total_amount), 0) as revenue,
                COALESCE(SUM(s.total_amount - s.total_cost), 0) as profit,
                COUNT(s.id) as sales_count
            FROM users u
            LEFT JOIN sales s ON u.id = s.pharmacist_id
        ";
        if ($filters) {
            $sql .= ' AND ' . implode(' AND ', $filters);
        }
        $sql .= " WHERE u.role = 'pharmacist' GROUP BY u.id ORDER BY u.name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getTopDrugs($limit = 10, $period = 'all', $branchId = null, $startDate = null, $endDate = null)
    {
        [$filters, $params] = $this->buildReportFilters($period, $branchId, $startDate, $endDate);
        $sql = "
            SELECT d.name, SUM(si.quantity) as total_quantity, SUM(si.quantity * si.price) as total_revenue
            FROM sale_items si
            JOIN sales s ON s.id = si.sale_id
            JOIN drugs d ON d.id = si.drug_id
        ";
        if ($filters) {
            $sql .= ' WHERE ' . implode(' AND ', $filters);
        }
        $sql .= ' GROUP BY d.id ORDER BY total_quantity DESC LIMIT ?';

        $params[] = (int)$limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

}
