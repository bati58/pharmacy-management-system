let currentChart = null;
let activeTab = 'revenueTrend';
let reportData = null;
let requestVersion = 0;

const reportTabs = {
    tabRevenueTrend: 'revenueTrend',
    tabProfitTrend: 'profitTrend',
    tabRevenueBranch: 'revenueBranch',
    tabRevenuePharmacist: 'revenuePharmacist',
    tabTopDrugs: 'topDrugs',
    tabSlowDrugs: 'slowDrugs'
};

document.addEventListener('DOMContentLoaded', initializeReports);

async function initializeReports() {
    document.getElementById('applyFilters')?.addEventListener('click', loadReports);
    Object.entries(reportTabs).forEach(([buttonId, tab]) => {
        document.getElementById(buttonId)?.addEventListener('click', () => switchReportTab(tab));
    });

    await loadBranchesForReport();
    await loadReports();
}

function readReportFilters() {
    const startDate = document.getElementById('startDate')?.value || '';
    const endDate = document.getElementById('endDate')?.value || '';

    if (Boolean(startDate) !== Boolean(endDate)) {
        showToast('Choose both a start and end date', 'error');
        return null;
    }
    if (startDate && startDate > endDate) {
        showToast('Start date must be on or before the end date', 'error');
        return null;
    }

    return {
        period: document.getElementById('reportPeriod')?.value || 'weekly',
        branchId: document.getElementById('reportBranch')?.value || '',
        startDate,
        endDate
    };
}

async function loadReports() {
    const filters = readReportFilters();
    if (!filters) return;

    const currentRequest = ++requestVersion;
    const button = document.getElementById('applyFilters');
    const originalButtonContent = button?.innerHTML;
    if (button) {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Updating...';
    }

    try {
        const [salesReport, revenueByBranch, revenueByPharmacist, topDrugs, slowDrugs] = await Promise.all([
            API.getSalesReport(filters.period, filters.branchId, filters.startDate, filters.endDate),
            API.getRevenueByBranch(filters.period, filters.branchId, filters.startDate, filters.endDate),
            API.getRevenueByPharmacist(filters.period, filters.branchId, filters.startDate, filters.endDate),
            API.getTopDrugs(10, filters.period, filters.branchId, filters.startDate, filters.endDate),
            API.getSlowMovingDrugs(10, filters.period, filters.branchId, filters.startDate, filters.endDate)
        ]);

        if (currentRequest !== requestVersion) return;

        reportData = {
            salesReport: salesReport.data || [],
            revenueByBranch: revenueByBranch.data || [],
            revenueByPharmacist: revenueByPharmacist.data || [],
            topDrugs: topDrugs.data || [],
            slowDrugs: slowDrugs.data || []
        };

        const totalRevenue = reportData.salesReport.reduce((sum, item) => sum + Number(item.total_revenue || 0), 0);
        const totalProfit = reportData.salesReport.reduce((sum, item) => sum + Number(item.total_profit || 0), 0);
        const totalSales = reportData.salesReport.reduce((sum, item) => sum + Number(item.transaction_count || 0), 0);

        document.getElementById('totalRevenue').innerText = formatCurrency(totalRevenue);
        document.getElementById('totalProfit').innerText = formatCurrency(totalProfit);
        document.getElementById('totalSalesCount').innerText = totalSales;
        renderReportChart();
    } catch (error) {
        console.error('Reports error:', error);
        if (currentRequest === requestVersion) {
            showToast(error.message || 'Failed to load reports', 'error');
        }
    } finally {
        if (button && currentRequest === requestVersion) {
            button.disabled = false;
            button.innerHTML = originalButtonContent;
        }
    }
}

function switchReportTab(tab) {
    activeTab = tab;
    Object.entries(reportTabs).forEach(([buttonId, buttonTab]) => {
        document.getElementById(buttonId)?.classList.toggle('active', buttonTab === tab);
    });
    renderReportChart();
}

function renderReportChart() {
    if (!reportData) return;

    const ctx = document.getElementById('reportChart')?.getContext('2d');
    if (!ctx) return;
    currentChart?.destroy();

    const configuration = getChartConfiguration(activeTab, reportData);
    const labels = configuration.values.length ? configuration.labels : ['No matching data'];
    const values = configuration.values.length ? configuration.values : [0];
    const isPie = configuration.type === 'pie';

    currentChart = new Chart(ctx, {
        type: configuration.type,
        data: {
            labels,
            datasets: [{
                label: configuration.label,
                data: values,
                backgroundColor: isPie
                    ? ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#0891b2', '#65a30d', '#e11d48']
                    : configuration.color,
                borderColor: configuration.borderColor || configuration.color,
                fill: configuration.type === 'line',
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: isPie } },
            ...(isPie ? {} : { scales: { y: { beginAtZero: true } } })
        }
    });
}

function getChartConfiguration(tab, data) {
    switch (tab) {
        case 'profitTrend':
            return {
                type: 'line',
                label: 'Profit (Br)',
                labels: data.salesReport.map(item => item.period),
                values: data.salesReport.map(item => Number(item.total_profit || 0)),
                color: 'rgba(16, 185, 129, 0.25)',
                borderColor: '#059669'
            };
        case 'revenueBranch':
            return {
                type: 'bar',
                label: 'Revenue (Br)',
                labels: data.revenueByBranch.map(item => item.branch_name),
                values: data.revenueByBranch.map(item => Number(item.revenue || 0)),
                color: '#4f46e5'
            };
        case 'revenuePharmacist': {
            const staffWithSales = data.revenueByPharmacist.filter(item => Number(item.revenue) > 0);
            return {
                type: 'pie',
                label: 'Revenue (Br)',
                labels: staffWithSales.map(item => item.pharmacist_name),
                values: staffWithSales.map(item => Number(item.revenue)),
                color: '#4f46e5'
            };
        }
        case 'topDrugs':
            return {
                type: 'bar',
                label: 'Units Sold',
                labels: data.topDrugs.map(item => item.name),
                values: data.topDrugs.map(item => Number(item.total_quantity || 0)),
                color: '#0891b2'
            };
        case 'slowDrugs':
            return {
                type: 'bar',
                label: 'Units Sold',
                labels: data.slowDrugs.map(item => item.name),
                values: data.slowDrugs.map(item => Number(item.total_sold || 0)),
                color: '#f59e0b'
            };
        default:
            return {
                type: 'line',
                label: 'Revenue (Br)',
                labels: data.salesReport.map(item => item.period),
                values: data.salesReport.map(item => Number(item.total_revenue || 0)),
                color: 'rgba(79, 70, 229, 0.2)',
                borderColor: '#4f46e5'
            };
    }
}

async function loadBranchesForReport() {
    try {
        const response = await API.getBranches();
        const select = document.getElementById('reportBranch');
        if (!select || !response.data) return;

        const currentValue = select.value;
        select.innerHTML = '<option value="">All Branches</option>';
        response.data.forEach(branch => {
            select.insertAdjacentHTML('beforeend', `<option value="${branch.id}">${escapeHtml(branch.name)}</option>`);
        });
        select.value = currentValue;
    } catch (error) {
        console.error('Error loading report branches:', error);
    }
}