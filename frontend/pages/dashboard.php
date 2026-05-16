<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<?php include '../includes/navbar.php'; ?>
<div class="animate-fade-in">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                    Welcome back, <span class="text-indigo-600"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></span>
                </h2>
                <p class="text-slate-500 mt-1 font-medium">Here's your pharmacy's performance at a glance.</p>
            </div>
            <div class="flex items-center gap-3">
            <?php if ($_SESSION['role'] === 'pharmacist'): ?>
            <a href="new-sale.php" class="btn-premium btn-premium-primary">
                <i class="fas fa-plus"></i> New Transaction
            </a>
            <?php endif; ?>
        </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="card p-6 kpi-card">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600">
                        <i class="fas fa-store text-xl"></i>
                    </div>
                    <span class="text-emerald-500 text-xs font-bold bg-emerald-50 px-2 py-1 rounded-lg">+12%</span>
                </div>
                <h3 class="text-slate-500 text-xs font-bold uppercase tracking-wider">Active Branches</h3>
                <p class="text-3xl kpi-value mt-1" id="kpi-branches">0</p>
            </div>
            <div class="card p-6 kpi-card">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600">
                        <i class="fas fa-capsules text-xl"></i>
                    </div>
                    <span class="text-emerald-500 text-xs font-bold bg-emerald-50 px-2 py-1 rounded-lg">Stable</span>
                </div>
                <h3 class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Inventory</h3>
                <p class="text-3xl kpi-value mt-1" id="kpi-drugs">0</p>
            </div>
            <div class="card p-6 kpi-card">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600">
                        <i class="fas fa-shopping-cart text-xl"></i>
                    </div>
                    <span class="text-emerald-500 text-xs font-bold bg-emerald-50 px-2 py-1 rounded-lg">+5%</span>
                </div>
                <h3 class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Sales</h3>
                <p class="text-3xl kpi-value mt-1" id="kpi-sales">0</p>
            </div>
            <div class="card p-6 kpi-card">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-600">
                        <i class="fas fa-dollar-sign text-xl"></i>
                    </div>
                    <span class="text-emerald-500 text-xs font-bold bg-emerald-50 px-2 py-1 rounded-lg">+24%</span>
                </div>
                <h3 class="text-slate-500 text-xs font-bold uppercase tracking-wider">Revenue</h3>
                <p class="text-3xl kpi-value mt-1" id="kpi-revenue">$0.00</p>
            </div>
        </div>

        <!-- Charts and Alerts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 card p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-chart-line text-indigo-500"></i> Sales Performance
                    </h3>
                    <select class="bg-slate-50 border-none text-xs font-bold text-slate-500 rounded-lg px-3 py-1 focus:ring-0">
                        <option>Last 7 Days</option>
                        <option>Last 30 Days</option>
                    </select>
                </div>
                <div class="h-[300px]">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            <div class="card p-6">
                <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle text-rose-500"></i> Critical Alerts
                </h3>
                <div id="lowStockList" class="space-y-4 max-h-[300px] overflow-y-auto pr-2">
                    <!-- Alerts will be injected here -->
                </div>
                <a href="drug-inventory.php" class="block text-center text-indigo-600 text-sm font-bold mt-6 hover:text-indigo-700 transition-colors">
                    Manage Inventory <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <!-- Recent Sales Table -->
        <div class="card mt-8 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-history text-indigo-500"></i> Recent Transactions
                </h3>
                <a href="sales.php" class="text-indigo-600 text-xs font-bold hover:underline">View All History</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th>Invoice ID</th>
                            <th>Customer / Patient</th>
                            <th>Amount</th>
                            <th>Branch</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="recentSalesTable">
                        <!-- Data will be injected here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="../assets/js/utils.js"></script>
<script src="../assets/js/api.js"></script>
<script src="../assets/js/dashboard.js"></script>
<?php include '../includes/footer.php'; ?>