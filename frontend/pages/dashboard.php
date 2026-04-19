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
?>
<script>
    window.APP_ROLE = '<?php echo $appRole; ?>';
    window.APP_BRANCH_ID = <?php echo $appBranchId; ?>;
</script>
<div class="ml-64 flex-1">
    <?php include '../includes/navbar.php'; ?>
    <div class="p-6">
        <h2 class="text-2xl font-bold mb-2">Welcome back,
            <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?>
        </h2>
        <p class="text-gray-600 mb-6">Here's what's happening with your pharmacy today.</p>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-gray-500 text-sm">Branches</h3>
                <p class="text-2xl font-bold" id="kpi-branches">-</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-gray-500 text-sm">Total Drugs</h3>
                <p class="text-2xl font-bold" id="kpi-drugs">-</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-gray-500 text-sm">Total Sales</h3>
                <p class="text-2xl font-bold" id="kpi-sales">-</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-gray-500 text-sm">Revenue</h3>
                <p class="text-2xl font-bold" id="kpi-revenue">-</p>
            </div>
        </div>

        <!-- Charts and Alerts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow p-4 lg:col-span-1">
                <h3 class="font-semibold mb-2">Sales (last 30 days)</h3>
                <canvas id="salesChart" height="200"></canvas>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold mb-2">Low stock</h3>
                <div id="lowStockList" class="space-y-1 max-h-48 overflow-y-auto text-sm"></div>
                <a href="drug-inventory.php" class="text-blue-600 text-sm mt-2 inline-block">View inventory →</a>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold mb-2">Near expiry</h3>
                <div id="expiringList" class="space-y-1 max-h-48 overflow-y-auto text-sm"></div>
            </div>
        </div>

        <!-- Recent Sales Table -->
        <div class="bg-white rounded-lg shadow p-4 mt-6">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold">Recent Sales</h3>
                <a href="sales.php" class="text-blue-600 text-sm">View all →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2">Invoice</th>
                            <th class="text-left">Customer</th>
                            <th class="text-left">Amount</th>
                            <th class="text-left">Date</th>
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