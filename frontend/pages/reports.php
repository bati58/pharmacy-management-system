<?php
require_once __DIR__ . '/../includes/init_session.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header('Location: dashboard.php');
    exit;
}
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="ml-64 flex-1">
    <?php include '../includes/navbar.php'; ?>
    <div class="p-6">
        <h2 class="text-2xl font-bold mb-4">Reports & Analytics</h2>

        <!-- Filters (auto‑refresh on change) -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="flex flex-wrap gap-4">
                <select id="reportPeriod" class="border rounded px-3 py-2">
                    <option value="daily">Daily</option>
                    <option value="weekly" selected>Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
                <select id="reportBranch" class="border rounded px-3 py-2">
                    <option value="">All Branches</option>
                </select>
                <input type="date" id="startDate" class="border rounded px-3 py-2" placeholder="Start Date">
                <input type="date" id="endDate" class="border rounded px-3 py-2" placeholder="End Date">
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <h3 class="text-gray-500">Total Revenue</h3>
                <p id="totalRevenue" class="text-2xl font-bold">$0</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <h3 class="text-gray-500">Total Sales</h3>
                <p id="totalSalesCount" class="text-2xl font-bold">0</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <h3 class="text-gray-500">Average Sale</h3>
                <p id="avgSale" class="text-2xl font-bold">$0</p>
            </div>
        </div>

        <!-- Tab Buttons -->
        <div class="flex flex-wrap gap-2 mb-4 border-b">
            <button id="tabRevenueTrend" class="tab-btn px-4 py-2 text-blue-600 border-b-2 border-blue-600 font-semibold">Revenue Trend</button>
            <button id="tabRevenueBranch" class="tab-btn px-4 py-2 text-gray-600 hover:text-blue-600">Revenue by Branch</button>
            <button id="tabRevenuePharmacist" class="tab-btn px-4 py-2 text-gray-600 hover:text-blue-600">Revenue by Pharmacist</button>
            <button id="tabTopDrugs" class="tab-btn px-4 py-2 text-gray-600 hover:text-blue-600">Top Drugs</button>
        </div>

        <!-- Chart Container -->
        <div class="bg-white rounded-lg shadow p-4">
            <canvas id="reportChart" height="300"></canvas>
        </div>
    </div>
</div>

<script src="../assets/js/utils.js"></script>
<script src="../assets/js/api.js"></script>
<script>
    let currentChart = null;
    let currentTab = 'revenueTrend';
    let cachedData = null; // store latest API response

    document.addEventListener('DOMContentLoaded', function() {
        loadBranchesForReport();
        loadReports(); // initial load
        setupEventListeners();
    });

    function setupEventListeners() {
        // Filter changes trigger reload
        document.getElementById('reportPeriod')?.addEventListener('change', () => loadReports());
        document.getElementById('reportBranch')?.addEventListener('change', () => loadReports());
        document.getElementById('startDate')?.addEventListener('change', () => loadReports());
        document.getElementById('endDate')?.addEventListener('change', () => loadReports());

        // Tab switching
        document.getElementById('tabRevenueTrend')?.addEventListener('click', () => switchTab('revenueTrend'));
        document.getElementById('tabRevenueBranch')?.addEventListener('click', () => switchTab('revenueBranch'));
        document.getElementById('tabRevenuePharmacist')?.addEventListener('click', () => switchTab('revenuePharmacist'));
        document.getElementById('tabTopDrugs')?.addEventListener('click', () => switchTab('topDrugs'));
    }

    function switchTab(tab) {
        currentTab = tab;
        // Update button styles
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-b-2', 'border-blue-600', 'text-blue-600', 'font-semibold');
            btn.classList.add('text-gray-600');
        });
        let activeBtn = null;
        if (tab === 'revenueTrend') activeBtn = document.getElementById('tabRevenueTrend');
        else if (tab === 'revenueBranch') activeBtn = document.getElementById('tabRevenueBranch');
        else if (tab === 'revenuePharmacist') activeBtn = document.getElementById('tabRevenuePharmacist');
        else if (tab === 'topDrugs') activeBtn = document.getElementById('tabTopDrugs');
        if (activeBtn) {
            activeBtn.classList.add('border-b-2', 'border-blue-600', 'text-blue-600', 'font-semibold');
            activeBtn.classList.remove('text-gray-600');
        }
        // If we have cached data, render immediately; else reload
        if (cachedData) {
            renderChart(currentTab, cachedData);
        } else {
            loadReports();
        }
    }

    async function loadReports() {
        const period = document.getElementById('reportPeriod')?.value || 'weekly';
        const branchId = document.getElementById('reportBranch')?.value || '';
        const startDate = document.getElementById('startDate')?.value;
        const endDate = document.getElementById('endDate')?.value;

        try {
            // Fetch all data in parallel
            const [salesReport, revenueByBranch, revenueByPharmacist, topDrugs] = await Promise.all([
                API.getSalesReport(period, branchId, startDate, endDate),
                API.getRevenueByBranch(),
                API.getRevenueByPharmacist(),
                API.getTopDrugs(10)
            ]);

            cachedData = {
                salesReport,
                revenueByBranch,
                revenueByPharmacist,
                topDrugs
            };

            // Update KPI cards
            let totalRevenue = 0;
            let totalSales = 0;
            if (salesReport.data && salesReport.data.length) {
                totalRevenue = salesReport.data.reduce((sum, item) => sum + parseFloat(item.total_revenue), 0);
                totalSales = salesReport.data.reduce((sum, item) => sum + item.transaction_count, 0);
            }
            const avgSale = totalSales > 0 ? totalRevenue / totalSales : 0;
            document.getElementById('totalRevenue').innerText = formatCurrency(totalRevenue);
            document.getElementById('totalSalesCount').innerText = totalSales;
            document.getElementById('avgSale').innerText = formatCurrency(avgSale);

            // Render current tab
            renderChart(currentTab, cachedData);

        } catch (err) {
            console.error('Reports error:', err);
            showToast('Failed to load reports', 'error');
        }
    }

    function renderChart(tab, data) {
        const ctx = document.getElementById('reportChart').getContext('2d');
        if (currentChart) currentChart.destroy();

        let labels = [];
        let values = [];
        let chartType = 'bar';
        let labelText = '';

        switch (tab) {
            case 'revenueTrend':
                chartType = 'line';
                labelText = 'Revenue ($)';
                if (data.salesReport.data && data.salesReport.data.length) {
                    labels = data.salesReport.data.map(item => item.period);
                    values = data.salesReport.data.map(item => parseFloat(item.total_revenue));
                } else {
                    labels = ['No Data'];
                    values = [0];
                }
                break;
            case 'revenueBranch':
                chartType = 'bar';
                labelText = 'Revenue ($)';
                if (data.revenueByBranch.data && data.revenueByBranch.data.length) {
                    labels = data.revenueByBranch.data.map(item => item.branch_name);
                    values = data.revenueByBranch.data.map(item => parseFloat(item.revenue));
                } else {
                    labels = ['No Data'];
                    values = [0];
                }
                break;
            case 'revenuePharmacist':
                chartType = 'pie';
                labelText = 'Revenue ($)';
                if (data.revenueByPharmacist.data && data.revenueByPharmacist.data.length) {
                    labels = data.revenueByPharmacist.data.map(item => item.pharmacist_name);
                    values = data.revenueByPharmacist.data.map(item => parseFloat(item.revenue));
                } else {
                    labels = ['No Data'];
                    values = [1];
                }
                break;
            case 'topDrugs':
                chartType = 'bar';
                labelText = 'Units Sold';
                if (data.topDrugs.data && data.topDrugs.data.length) {
                    const top5 = data.topDrugs.data.slice(0, 5);
                    labels = top5.map(item => item.name);
                    values = top5.map(item => parseFloat(item.total_quantity));
                } else {
                    labels = ['No Data'];
                    values = [0];
                }
                break;
        }

        if (chartType === 'pie') {
            currentChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec489a', '#06b6d4']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        } else {
            currentChart = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: labelText,
                        data: values,
                        backgroundColor: '#3b82f6',
                        borderColor: '#2563eb',
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    }

    async function loadBranchesForReport() {
        try {
            const branches = await API.getBranches();
            const select = document.getElementById('reportBranch');
            if (select && branches.data) {
                select.innerHTML = '<option value="">All Branches</option>';
                branches.data.forEach(b => {
                    select.innerHTML += `<option value="${b.id}">${escapeHtml(b.name)}</option>`;
                });
            }
        } catch (err) {
            console.error(err);
        }
    }
</script>
<?php include '../includes/footer.php'; ?>