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

    public function getSalesReport($period = 'daily', $branchId = null, $startDate = null, $endDate = null)
    {
        switch ($period) {
            case 'weekly':
                $groupBy = "YEARWEEK(sale_date)";
                $dateFormat = "'%Y Week %v'";
                break;
            case 'monthly':
                $groupBy = "DATE_FORMAT(sale_date, '%Y-%m')";
                $dateFormat = "'%Y-%m'";
                break;
            case 'custom':
                $groupBy = "DATE(sale_date)";
                $dateFormat = "'%Y-%m-%d'";
                break;
            default: // daily
                $groupBy = "DATE(sale_date)";
                $dateFormat = "'%Y-%m-%d'";
        }

        $sql = "
            SELECT 
                DATE_FORMAT(sale_date, $dateFormat) as period,
                COUNT(*) as transaction_count,
                SUM(total_amount) as total_revenue,
                SUM(total_cost) as total_cost,
                SUM(total_amount - total_cost) as total_profit,
                AVG(total_amount) as avg_sale
            FROM sales
            WHERE 1=1
        ";
        $params = [];
        if ($branchId) {
            $sql .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        if ($startDate && $endDate) {
            $sql .= " AND sale_date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }
        $sql .= " GROUP BY period ORDER BY period DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getRevenueByBranch()
    {
        $stmt = $this->db->query("
            SELECT 
                b.name as branch_name, 
                COALESCE(SUM(s.total_amount), 0) as revenue, 
                COALESCE(SUM(s.total_amount - s.total_cost), 0) as profit,
                COUNT(s.id) as sales_count
            FROM branches b
            LEFT JOIN sales s ON b.id = s.branch_id
            GROUP BY b.id
        ");
        return $stmt->fetchAll();
    }

    public function getRevenueByPharmacist()
    {
        $stmt = $this->db->query("
            SELECT 
                u.name as pharmacist_name, 
                COALESCE(SUM(s.total_amount), 0) as revenue, 
                COALESCE(SUM(s.total_amount - s.total_cost), 0) as profit,
                COUNT(s.id) as sales_count
            FROM users u
            LEFT JOIN sales s ON u.id = s.pharmacist_id
            WHERE u.role = 'pharmacist'
            GROUP BY u.id
        ");
        return $stmt->fetchAll();
    }

    public function getTopDrugs($limit = 10)
    {
        $stmt = $this->db->prepare("
            SELECT d.name, SUM(si.quantity) as total_quantity, SUM(si.quantity * si.price) as total_revenue
            FROM sale_items si
            JOIN drugs d ON si.drug_id = d.id
            GROUP BY d.id
            ORDER BY total_quantity DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
