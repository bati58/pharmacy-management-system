<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
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
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Business Intelligence</h2>
            <p class="text-slate-500 mt-1 font-medium">Deep dive into your pharmacy performance and sales trends.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-4 md:p-6 mb-8 bg-white/50 backdrop-blur-sm border-slate-200/60">
        <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-4">
            <div class="flex-1 min-w-[160px]">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Time Period</label>
                <select id="reportPeriod" class="w-full py-2.5 bg-slate-50 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-bold text-slate-700">
                    <option value="daily">Daily Analysis</option>
                    <option value="weekly" selected>Weekly Review</option>
                    <option value="monthly">Monthly Summary</option>
                </select>
            </div>
            <div class="flex-1 min-w-[160px]">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Branch filter</label>
                <select id="reportBranch" class="w-full py-2.5 bg-slate-50 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-bold text-slate-700">
                    <option value="">All Branches</option>
                </select>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 flex-1 min-w-[240px]">
                <div class="flex-1">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">From</label>
                    <input type="date" id="startDate" class="w-full py-2 bg-slate-50 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-bold text-slate-700">
                </div>
                <div class="flex-1">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">To</label>
                    <input type="date" id="endDate" class="w-full py-2 bg-slate-50 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-bold text-slate-700">
                </div>
                <div class="flex items-end">
                    <button type="button" onclick="loadReports()" class="w-full sm:w-auto px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-md shadow-indigo-200 transition-all">
                        <i class="fas fa-sync-alt mr-1"></i> Update
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card p-6 bg-gradient-to-br from-indigo-500 to-indigo-600 text-white shadow-lg shadow-indigo-100">
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-xl">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest bg-white/20 px-2 py-1 rounded-lg">Gross Revenue</span>
            </div>
            <h3 class="text-3xl font-black mb-1" id="totalRevenue">$0.00</h3>
            <p class="text-indigo-100/80 text-xs font-medium">Accumulated across selected period</p>
        </div>

        <div class="card p-6 bg-emerald-500 text-white shadow-lg shadow-emerald-100">
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-xl">
                    <i class="fas fa-chart-line"></i>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest bg-white/20 px-2 py-1 rounded-lg">Net Profit</span>
            </div>
            <h3 class="text-3xl font-black mb-1" id="totalProfit">$0.00</h3>
            <p class="text-emerald-50/80 text-xs font-medium">After deducting wholesale costs</p>
        </div>

        <div class="card p-6 bg-white border border-slate-200 text-slate-800">
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-indigo-600 flex items-center justify-center text-xl">
                    <i class="fas fa-receipt"></i>
                </div>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-100 px-2 py-1 rounded-lg">Volume</span>
            </div>
            <h3 class="text-3xl font-black mb-1 text-slate-900" id="totalSalesCount">0</h3>
            <p class="text-slate-400 text-xs font-medium">Total successful transactions</p>
        </div>
    </div>

    <!-- Analytics Tabs & Chart -->
    <div class="card overflow-hidden">
        <div class="bg-slate-50/80 border-b border-slate-100 overflow-x-auto">
            <div class="flex p-1 min-w-max">
                <button id="tabRevenueTrend" class="tab-btn active px-4 py-2.5 text-sm font-bold rounded-xl transition-all whitespace-nowrap">Revenue Trend</button>
                <button id="tabProfitTrend" class="tab-btn px-4 py-2.5 text-sm font-bold text-slate-500 hover:text-indigo-600 rounded-xl transition-all whitespace-nowrap">Profitability</button>
                <button id="tabRevenueBranch" class="tab-btn px-4 py-2.5 text-sm font-bold text-slate-500 hover:text-indigo-600 rounded-xl transition-all whitespace-nowrap">By Branch</button>
                <button id="tabRevenuePharmacist" class="tab-btn px-4 py-2.5 text-sm font-bold text-slate-500 hover:text-indigo-600 rounded-xl transition-all whitespace-nowrap">By Staff</button>
                <button id="tabTopDrugs" class="tab-btn px-4 py-2.5 text-sm font-bold text-slate-500 hover:text-indigo-600 rounded-xl transition-all whitespace-nowrap">Top Items</button>
            </div>
        </div>
        
        <div class="p-8">
            <div class="relative h-[400px]">
                <canvas id="reportChart"></canvas>
            </div>
        </div>
    </div>
</div>

<style>
.tab-btn.active {
    background: white;
    color: #4f46e5;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
}
</style>

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
        document.getElementById('tabProfitTrend')?.addEventListener('click', () => switchTab('profitTrend'));
        document.getElementById('tabRevenueBranch')?.addEventListener('click', () => switchTab('revenueBranch'));
        document.getElementById('tabRevenuePharmacist')?.addEventListener('click', () => switchTab('revenuePharmacist'));
        document.getElementById('tabTopDrugs')?.addEventListener('click', () => switchTab('topDrugs'));
    }

    function switchTab(tab) {
        currentTab = tab;
        // Update button styles
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        let activeBtnId = '';
        if (tab === 'revenueTrend') activeBtnId = 'tabRevenueTrend';
        else if (tab === 'profitTrend') activeBtnId = 'tabProfitTrend';
        else if (tab === 'revenueBranch') activeBtnId = 'tabRevenueBranch';
        else if (tab === 'revenuePharmacist') activeBtnId = 'tabRevenuePharmacist';
        else if (tab === 'topDrugs') activeBtnId = 'tabTopDrugs';
        
        const activeBtn = document.getElementById(activeBtnId);
        if (activeBtn) activeBtn.classList.add('active');
        
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
            let totalProfit = 0;
            let totalSales = 0;
            if (salesReport.data && salesReport.data.length) {
                totalRevenue = salesReport.data.reduce((sum, item) => sum + parseFloat(item.total_revenue), 0);
                totalProfit = salesReport.data.reduce((sum, item) => sum + parseFloat(item.total_profit), 0);
                totalSales = salesReport.data.reduce((sum, item) => sum + item.transaction_count, 0);
            }
            document.getElementById('totalRevenue').innerText = formatCurrency(totalRevenue);
            document.getElementById('totalProfit').innerText = formatCurrency(totalProfit);
            document.getElementById('totalSalesCount').innerText = totalSales;

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
            case 'profitTrend':
                chartType = 'line';
                labelText = 'Profit ($)';
                if (data.salesReport.data && data.salesReport.data.length) {
                    labels = data.salesReport.data.map(item => item.period);
                    values = data.salesReport.data.map(item => parseFloat(item.total_profit));
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