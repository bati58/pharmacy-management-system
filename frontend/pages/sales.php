<?php
require_once __DIR__ . '/../includes/init_session.php';
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['manager', 'pharmacist'])) {
    header('Location: dashboard.php');
    exit;
}
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="ml-64 flex-1">
    <?php include '../includes/navbar.php'; ?>
    <div class="p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold">Sales</h2>
            <a href="new-sale.php" class="bg-green-600 text-white px-4 py-2 rounded">+ New Sale</a>
        </div>
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2">Invoice</th>
                        <th class="px-4 py-2">Customer</th>
                        <th class="px-4 py-2">Items</th>
                        <th class="px-4 py-2">Amount</th>
                        <th class="px-4 py-2">Payment</th>
                        <th class="px-4 py-2">Pharmacist</th>
                        <th class="px-4 py-2">Date</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody id="sales-table-body"></tbody>
            </table>
        </div>
    </div>
</div>
<script src="../assets/js/utils.js"></script>
<script src="../assets/js/api.js"></script>
<script src="../assets/js/sales.js"></script>
<?php include '../includes/footer.php'; ?>