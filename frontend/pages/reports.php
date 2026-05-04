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
    <div class="p-6 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-slide-up">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Business Intelligence</h2>
                <p class="text-slate-500 font-medium">Strategic insights, revenue analysis, and inventory performance.</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="btn !bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 no-print">
                    <i class="fas fa-print"></i> Export PDF
                </button>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="card animate-slide-up no-print" style="animation-delay: 0.1s;">
            <div class="flex flex-wrap items-end gap-4">
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Time Period</label>
                    <select id="reportPeriod" class="!bg-slate-50 border-slate-200 min-w-[140px]">
                        <option value="daily">Daily View</option>
                        <option value="weekly" selected>Weekly View</option>
                        <option value="monthly">Monthly View</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Branch Filter</label>
                    <select id="reportBranch" class="!bg-slate-50 border-slate-200 min-w-[200px]">
                        <option value="">All Branches</option>
                    </select>
                </div>
                <div class="space-y-1 custom-range hidden">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Start Date</label>
                    <input type="date" id="startDate" class="!bg-slate-50 border-slate-200">
                </div>
                <div class="space-y-1 custom-range hidden">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">End Date</label>
                    <input type="date" id="endDate" class="!bg-slate-50 border-slate-200">
                </div>
                <button onclick="loadReports()" class="btn btn-primary px-6 h-[42px]">
                    <i class="fas fa-sync-alt"></i> Update
                </button>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 animate-slide-up" style="animation-delay: 0.2s;">
            <div class="card bg-gradient-to-br from-blue-600 to-indigo-700 text-white border-0">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-2 bg-white/20 rounded-lg backdrop-blur-md">
                        <i class="fas fa-dollar-sign text-white"></i>
                    </div>
                </div>
                <h3 class="text-white/70 text-xs font-bold uppercase tracking-widest mb-1">Total Revenue</h3>
                <p id="totalRevenue" class="text-3xl font-black tracking-tight">$0</p>
            </div>
            <div class="card bg-gradient-to-br from-emerald-500 to-teal-600 text-white border-0">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-2 bg-white/20 rounded-lg backdrop-blur-md">
                        <i class="fas fa-chart-line text-white"></i>
                    </div>
                </div>
                <h3 class="text-white/70 text-xs font-bold uppercase tracking-widest mb-1">Net Profit</h3>
                <p id="totalProfit" class="text-3xl font-black tracking-tight">$0</p>
            </div>
            <div class="card border-0 shadow-xl shadow-slate-200/50">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
                <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total Sales</h3>
                <p id="totalSalesCount" class="text-3xl font-black text-slate-800 tracking-tight">0</p>
            </div>
            <div class="card border-0 shadow-xl shadow-slate-200/50">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
                        <i class="fas fa-percentage"></i>
                    </div>
                </div>
                <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Avg. Transaction</h3>
                <p id="avgSale" class="text-3xl font-black text-slate-800 tracking-tight">$0</p>
            </div>
        </div>

        <!-- Analytical Tabs -->
        <div class="card animate-slide-up" style="animation-delay: 0.3s;">
            <div class="flex flex-wrap gap-2 mb-8 border-b border-slate-100 pb-4 no-print">
                <button id="tabRevenueTrend" class="tab-btn active px-4 py-2 text-sm font-bold transition-all rounded-lg">Revenue Trend</button>
                <button id="tabRevenueBranch" class="tab-btn px-4 py-2 text-sm font-bold text-slate-400 hover:text-slate-600 transition-all rounded-lg">By Branch</button>
                <button id="tabRevenuePharmacist" class="tab-btn px-4 py-2 text-sm font-bold text-slate-400 hover:text-slate-600 transition-all rounded-lg">By Pharmacist</button>
                <button id="tabTopDrugs" class="tab-btn px-4 py-2 text-sm font-bold text-slate-400 hover:text-slate-600 transition-all rounded-lg">Fast-Moving</button>
                <button id="tabSlowDrugs" class="tab-btn px-4 py-2 text-sm font-bold text-slate-400 hover:text-slate-600 transition-all rounded-lg">Slow-Moving</button>
            </div>

            <div class="relative h-[450px]">
                <canvas id="reportChart"></canvas>
            </div>
        </div>
    </div>
</div>

<style>
    .tab-btn.active {
        background: #f1f5f9;
        color: #3b82f6;
    }
    @media print {
        .ml-64 { margin-left: 0 !important; }
        .sidebar, .navbar { display: none !important; }
        .card { border: 1px solid #e2e8f0 !important; box-shadow: none !important; }
        body { background: white !important; }
    }
</style>

<script src="../assets/js/utils.js"></script>
<script src="../assets/js/api.js"></script>
<script>
    let currentChart = null;
    let currentTab = 'revenueTrend';
    let cachedData = null;

    document.addEventListener('DOMContentLoaded', function() {
        loadBranchesForReport();
        loadReports();
        setupEventListeners();
    });

    function setupEventListeners() {
        document.getElementById('reportPeriod')?.addEventListener('change', (e) => {
            const customRange = document.querySelectorAll('.custom-range');
            if (e.target.value === 'custom') {
                customRange.forEach(el => el.classList.remove('hidden'));
            } else {
                customRange.forEach(el => el.classList.add('hidden'));
            }
            loadReports();
        });

        // Tab switching
        const tabs = {
            'tabRevenueTrend': 'revenueTrend',
            'tabRevenueBranch': 'revenueBranch',
            'tabRevenuePharmacist': 'revenuePharmacist',
            'tabTopDrugs': 'topDrugs',
            'tabSlowDrugs': 'slowDrugs'
        };

        Object.keys(tabs).forEach(id => {
            document.getElementById(id)?.addEventListener('click', () => switchTab(tabs[id], id));
        });
    }

    function switchTab(tab, btnId) {
        currentTab = tab;
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active', 'text-blue-600');
            btn.classList.add('text-slate-400');
        });
        document.getElementById(btnId).classList.add('active', 'text-blue-600');
        document.getElementById(btnId).classList.remove('text-slate-400');

        if (cachedData) renderChart(currentTab, cachedData);
        else loadReports();
    }

    async function loadReports() {
        const period = document.getElementById('reportPeriod')?.value || 'weekly';
        const branchId = document.getElementById('reportBranch')?.value || '';
        const startDate = document.getElementById('startDate')?.value;
        const endDate = document.getElementById('endDate')?.value;

        try {
            const [salesReport, revenueByBranch, revenueByPharmacist, topDrugs, slowDrugs] = await Promise.all([
                API.getSalesReport(period, branchId, startDate, endDate),
                API.getRevenueByBranch(),
                API.getRevenueByPharmacist(),
                API.getTopDrugs(10),
                API.getSlowMovingDrugs(10)
            ]);

            cachedData = { salesReport, revenueByBranch, revenueByPharmacist, topDrugs, slowDrugs };

            // Update KPI cards
            let totalRevenue = 0;
            let totalProfit = 0;
            let totalSales = 0;
            if (salesReport.data && salesReport.data.length) {
                totalRevenue = salesReport.data.reduce((sum, item) => sum + parseFloat(item.total_revenue), 0);
                totalProfit = salesReport.data.reduce((sum, item) => sum + parseFloat(item.total_profit || 0), 0);
                totalSales = salesReport.data.reduce((sum, item) => sum + item.transaction_count, 0);
            }
            const avgSale = totalSales > 0 ? totalRevenue / totalSales : 0;
            
            document.getElementById('totalRevenue').innerText = formatCurrency(totalRevenue);
            document.getElementById('totalProfit').innerText = formatCurrency(totalProfit);
            document.getElementById('totalSalesCount').innerText = totalSales;
            document.getElementById('avgSale').innerText = formatCurrency(avgSale);

            renderChart(currentTab, cachedData);
        } catch (err) {
            console.error('Reports error:', err);
            showToast('Failed to load strategic data', 'error');
        }
    }

    function renderChart(tab, data) {
        const ctx = document.getElementById('reportChart').getContext('2d');
        if (currentChart) currentChart.destroy();

        let labels = [], values = [], values2 = [], chartType = 'bar', labelText = '', labelText2 = '';
        const blue = '#3b82f6', teal = '#10b981', slate = '#94a3b8';

        switch (tab) {
            case 'revenueTrend':
                chartType = 'line';
                labelText = 'Revenue ($)';
                labelText2 = 'Profit ($)';
                if (data.salesReport.data?.length) {
                    labels = data.salesReport.data.map(item => item.period).reverse();
                    values = data.salesReport.data.map(item => parseFloat(item.total_revenue)).reverse();
                    values2 = data.salesReport.data.map(item => parseFloat(item.total_profit || 0)).reverse();
                }
                break;
            case 'revenueBranch':
                labelText = 'Revenue by Branch ($)';
                if (data.revenueByBranch.data?.length) {
                    labels = data.revenueByBranch.data.map(item => item.branch_name);
                    values = data.revenueByBranch.data.map(item => parseFloat(item.revenue));
                }
                break;
            case 'revenuePharmacist':
                chartType = 'doughnut';
                labelText = 'Pharmacist Performance';
                if (data.revenueByPharmacist.data?.length) {
                    labels = data.revenueByPharmacist.data.map(item => item.pharmacist_name);
                    values = data.revenueByPharmacist.data.map(item => parseFloat(item.revenue));
                }
                break;
            case 'topDrugs':
                labelText = 'Top Selling Drugs (Units)';
                if (data.topDrugs.data?.length) {
                    labels = data.topDrugs.data.map(item => item.name);
                    values = data.topDrugs.data.map(item => parseFloat(item.total_quantity));
                }
                break;
            case 'slowDrugs':
                labelText = 'Slow-Moving Items (Total Sales)';
                if (data.slowDrugs.data?.length) {
                    labels = data.slowDrugs.data.map(item => item.name);
                    values = data.slowDrugs.data.map(item => parseFloat(item.total_sold));
                }
                break;
        }

        const datasets = [{
            label: labelText,
            data: values,
            backgroundColor: chartType === 'doughnut' ? ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'] : blue,
            borderColor: chartType === 'line' ? blue : 'transparent',
            tension: 0.4,
            fill: chartType === 'line' ? 'origin' : false,
            pointBackgroundColor: blue
        }];

        if (tab === 'revenueTrend') {
            datasets.push({
                label: labelText2,
                data: values2,
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderColor: teal,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: teal
            });
        }

        currentChart = new Chart(ctx, {
            type: chartType,
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, font: { weight: 'bold' } } }
                },
                scales: chartType !== 'doughnut' ? {
                    y: { grid: { display: false }, ticks: { font: { weight: 'bold' } } },
                    x: { grid: { display: false }, ticks: { font: { weight: 'bold' } } }
                } : {}
            }
        });
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
        } catch (err) { console.error(err); }
    }
</script>
<?php include '../includes/footer.php'; ?>