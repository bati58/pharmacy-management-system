<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['manager', 'pharmacist'])) {
    header('Location: dashboard.php');
    exit;
}
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<?php include '../includes/navbar.php'; ?>
<div class="animate-fade-in">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Sales History</h2>
            <p class="text-slate-500 mt-1 font-medium">Track and review all transactions across your branches.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="new-sale.php" class="btn-premium btn-premium-primary shadow-indigo-200">
                <i class="fas fa-plus"></i> Process New Sale
            </a>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="card overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-history text-indigo-500"></i> Recent Transactions
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer / Date</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Payment</th>
                        <th>Pharmacist</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="sales-table-body">
                    <!-- Data will be injected here -->
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="../assets/js/utils.js"></script>
<script src="../assets/js/api.js"></script>
<script src="../assets/js/sales.js"></script>
<?php include '../includes/footer.php'; ?>