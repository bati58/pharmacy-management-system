<?php
require_once __DIR__ . '/../includes/init_session.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}
$appRole = htmlspecialchars($_SESSION['role'] ?? '', ENT_QUOTES, 'UTF-8');
$appBranchId = (int)($_SESSION['branch_id'] ?? 0);
include '../includes/header.php';
include '../includes/sidebar.php';

// Trigger expiry checker if not run in the last hour
$lastRunFile = __DIR__ . '/../../backend/logs/last_expiry_check.txt';
$shouldRun = true;
if (file_exists($lastRunFile)) {
    $lastRun = file_get_contents($lastRunFile);
    if (time() - (int)$lastRun < 3600) {
        $shouldRun = false;
    }
}

if ($shouldRun && in_array($_SESSION['role'], ['manager', 'store_keeper'])) {
    @include __DIR__ . '/../../backend/helpers/expiry_checker.php';
    @file_put_contents($lastRunFile, time());
}
?>
<script>
    window.APP_ROLE = '<?php echo $appRole; ?>';
    window.APP_BRANCH_ID = <?php echo $appBranchId; ?>;
</script>
<div class="ml-64 flex-1">
    <?php include '../includes/navbar.php'; ?>
    <div class="p-6 space-y-6">
        <div class="animate-slide-up">
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Welcome back,
                <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?>
            </h2>
            <p class="text-slate-500 font-medium">Here's what's happening with your pharmacy today.</p>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 animate-slide-up" style="animation-delay: 0.1s;">
            <div class="card kpi-card">
                <div>
                    <h3 class="kpi-title" id="kpi-dynamic-title"><?php echo $_SESSION['role'] === 'manager' ? 'Net Profit' : 'Branches'; ?></h3>
                    <p class="kpi-value" id="kpi-dynamic-value">-</p>
                </div>
                <div class="mt-4 flex items-center text-xs font-bold text-blue-600">
                    <i class="fas <?php echo $_SESSION['role'] === 'manager' ? 'fa-chart-line' : 'fa-store'; ?> mr-1"></i> <span id="kpi-dynamic-subtext"><?php echo $_SESSION['role'] === 'manager' ? 'Earnings after cost' : 'Active branches'; ?></span>
                </div>
            </div>
            <div class="card kpi-card">
                <div>
                    <h3 class="kpi-title">Total Drugs</h3>
                    <p class="kpi-value" id="kpi-drugs">-</p>
                </div>
                <div class="mt-4 flex items-center text-xs font-bold text-green-600">
                    <i class="fas fa-capsules mr-1"></i> <span>In stock</span>
                </div>
            </div>
            <div class="card kpi-card">
                <div>
                    <h3 class="kpi-title">Today's Sales</h3>
                    <p class="kpi-value" id="kpi-sales">-</p>
                </div>
                <div class="mt-4 flex items-center text-xs font-bold text-purple-600">
                    <i class="fas fa-shopping-cart mr-1"></i> <span>Processed</span>
                </div>
            </div>
            <div class="card kpi-card">
                <div>
                    <h3 class="kpi-title">Revenue</h3>
                    <p class="kpi-value" id="kpi-revenue">-</p>
                </div>
                <div class="mt-4 flex items-center text-xs font-bold text-emerald-600">
                    <i class="fas fa-dollar-sign mr-1"></i> <span>Total earnings</span>
                </div>
            </div>
        </div>

        <!-- Charts and Alerts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-slide-up" style="animation-delay: 0.2s;">
            <div class="card lg:col-span-1">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-800">Sales Trend</h3>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">30 Days</span>
                </div>
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            <div class="card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-800">Low Stock Alerts</h3>
                    <i class="fas fa-exclamation-triangle text-amber-500"></i>
                </div>
                <div id="lowStockList" class="space-y-3 max-h-48 overflow-y-auto pr-2 custom-scrollbar"></div>
                <a href="drug-inventory.php" class="text-blue-600 text-xs font-bold mt-4 inline-flex items-center gap-1 hover:gap-2 transition-all">
                    View Inventory <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-800">Near Expiry</h3>
                    <i class="fas fa-clock text-red-500"></i>
                </div>
                <div id="expiringList" class="space-y-3 max-h-48 overflow-y-auto pr-2 custom-scrollbar"></div>
            </div>
        </div>

        <!-- Recent Sales Table -->
        <div class="card animate-slide-up" style="animation-delay: 0.3s;">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-slate-800">Recent Sales Transactions</h3>
                <a href="sales.php" class="btn btn-primary !py-2 !px-4 text-xs">View All Sales</a>
            </div>
            <div class="table-container">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="recentSalesTable"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="../assets/js/utils.js"></script>
<script src="../assets/js/api.js"></script>
<script src="../assets/js/dashboard.js"></script>
<?php include '../includes/footer.php'; ?>