let revenueChart = null;
let branchChart = null;
let pharmacistChart = null;
let topDrugsChart = null;

document.addEventListener('DOMContentLoaded', function () {
    loadBranchesForReport();
    loadReports();
    document.getElementById('applyFilters').addEventListener('click', loadReports);
});

async function loadReports() {
    const period = document.getElementById('reportPeriod').value;
    const branchId = document.getElementById('reportBranch').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;

    try {
        // Fetch all data in parallel
        const [salesReport, revenueByBranch, revenueByPharmacist, topDrugs] = await Promise.all([
            API.getSalesReport(period, branchId, startDate, endDate),
            API.getRevenueByBranch(),
            API.getRevenueByPharmacist(),
            API.getTopDrugs(10)
        ]);

        // Calculate KPI totals
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

        // Render charts
        renderRevenueChart(salesReport.data);
        renderBranchChart(revenueByBranch.data);
        renderPharmacistChart(revenueByPharmacist.data);
        renderTopDrugsChart(topDrugs.data);

    } catch (err) {
        console.error('Reports error:', err);
        showToast('Failed to load reports', 'error');
    }
}

function renderRevenueChart(data) {
    const ctx = document.getElementById('revenueChart')?.getContext('2d');
    if (!ctx) return;
    if (revenueChart) revenueChart.destroy();

    const labels = data && data.length ? data.map(item => item.period) : ['No Data'];
    const values = data && data.length ? data.map(item => parseFloat(item.total_revenue)) : [0];

    revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{ label: 'Revenue ($)', data: values, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', tension: 0.3, fill: true }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
}

function renderBranchChart(data) {
    const ctx = document.getElementById('branchChart')?.getContext('2d');
    if (!ctx) return;
    if (branchChart) branchChart.destroy();

    const labels = data && data.length ? data.map(item => item.branch_name) : ['No Data'];
    const values = data && data.length ? data.map(item => parseFloat(item.revenue)) : [0];

    branchChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{ label: 'Revenue ($)', data: values, backgroundColor: '#10b981' }]
        }
    });
}

function renderPharmacistChart(data) {
    const ctx = document.getElementById('pharmacistChart')?.getContext('2d');
    if (!ctx) return;
    if (pharmacistChart) pharmacistChart.destroy();

    const labels = data && data.length ? data.map(item => item.pharmacist_name) : ['No Data'];
    const values = data && data.length ? data.map(item => parseFloat(item.revenue)) : [1];

    pharmacistChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{ data: values, backgroundColor: ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6'] }]
        }
    });
}

function renderTopDrugsChart(data) {
    const ctx = document.getElementById('topDrugsChart')?.getContext('2d');
    if (!ctx) return;
    if (topDrugsChart) topDrugsChart.destroy();

    const top5 = data && data.length ? data.slice(0, 5) : [];
    const labels = top5.length ? top5.map(item => item.name) : ['No Data'];
    const values = top5.length ? top5.map(item => parseFloat(item.total_quantity)) : [0];

    topDrugsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{ label: 'Units Sold', data: values, backgroundColor: '#8b5cf6' }]
        }
    });
}

async function loadBranchesForReport() {
    try {
        const branches = await API.getBranches();
        const select = document.getElementById('reportBranch');
        if (select && branches.data && branches.data.length) {
            select.innerHTML = '<option value="">All Branches</option>';
            branches.data.forEach(b => {
                select.innerHTML += `<option value="${b.id}">${escapeHtml(b.name)}</option>`;
            });
        }
    } catch (err) {
        console.error('Error loading branches for report:', err);
    }
}