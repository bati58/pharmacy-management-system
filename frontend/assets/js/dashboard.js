// Dashboard — KPIs, chart, alerts, recent sales (role-aware)
let salesChart = null;

document.addEventListener('DOMContentLoaded', function () {
    loadDashboardData();
    loadRecentSales();
    loadAlerts();
});

function dashboardRole() {
    return window.APP_ROLE || (JSON.parse(localStorage.getItem('user') || '{}').role) || '';
}

async function loadDashboardData() {
    const role = dashboardRole();
    try {
        let totalBranches = 0;
        let totalDrugs = 0;
        let report = { data: [] };
        const userBranch = window.APP_BRANCH_ID || (JSON.parse(localStorage.getItem('user') || '{}').branch_id) || null;
        const branchArg = role === 'manager' ? null : userBranch;
        const inventoryLocation = role === 'pharmacist' ? 'dispensary' : (role === 'store_keeper' ? 'store' : '');

        if (role === 'manager') {
            try {
                const branches = await API.getBranches();
                totalBranches = branches.data ? branches.data.length : 0;
            } catch (e) {
                console.warn('Branches KPI skipped', e);
            }
        } else {
            totalBranches = 1;
        }

        const drugs = await API.getDrugs(branchArg ?? undefined, '', inventoryLocation);
        totalDrugs = drugs.data ? drugs.data.length : 0;

        const elDynamic = document.getElementById('kpi-dynamic-value');
        const elD = document.getElementById('kpi-drugs');
        const elS = document.getElementById('kpi-sales');
        const elR = document.getElementById('kpi-revenue');

        if (role === 'store_keeper') {
            const storeUnits = (drugs.data || []).reduce((sum, drug) => sum + Number(drug.stock || drug.location_quantity || 0), 0);
            let lowStockCount = 0;
            try {
                const lowStock = await API.getLowStock();
                lowStockCount = (lowStock.data || []).length;
            } catch (e) {
                console.warn('Low stock KPI skipped', e);
            }

            setText('kpi-drugs-title', 'Store Drugs');
            setText('kpi-sales-title', 'Store Units');
            setText('kpi-revenue-title', 'Low Stock');
            setHtml('kpi-drugs-subtext', '<i class="fas fa-warehouse mr-1"></i> <span>In store</span>');
            setHtml('kpi-sales-subtext', '<i class="fas fa-boxes-stacked mr-1"></i> <span>Available stock</span>');
            setHtml('kpi-revenue-subtext', '<i class="fas fa-exclamation-triangle mr-1"></i> <span>Store alerts</span>');

            if (elDynamic) elDynamic.innerText = totalBranches;
            if (elD) elD.innerText = totalDrugs;
            if (elS) elS.innerText = storeUnits;
            if (elR) elR.innerText = lowStockCount;

            const salesTrendCard = document.getElementById('salesTrendCard');
            if (salesTrendCard) salesTrendCard.style.display = 'none';
            return;
        }

        const salesTrendCard = document.getElementById('salesTrendCard');
        if (salesTrendCard) salesTrendCard.style.display = '';

        try {
            report = await API.getSalesReport('daily', branchArg ?? undefined, null, null);
        } catch (e) {
            console.warn('Sales report skipped', e);
        }

        let totalSales = 0;
        let totalRevenue = 0;
        let totalProfit = 0;
        if (report.data && report.data.length) {
            totalSales = report.data.reduce((sum, day) => sum + parseInt(day.transaction_count, 10), 0);
            totalRevenue = report.data.reduce((sum, day) => sum + parseFloat(day.total_revenue || 0), 0);
            totalProfit = report.data.reduce((sum, day) => sum + parseFloat(day.total_profit || 0), 0);
        }
        
        if (elDynamic) {
            elDynamic.innerText = role === 'manager' ? formatCurrency(totalProfit) : totalBranches;
        }
        if (elD) elD.innerText = totalDrugs;
        if (elS) elS.innerText = totalSales;
        if (elR) elR.innerText = formatCurrency(totalRevenue);

        renderSalesChart(report.data || []);
    } catch (error) {
        console.error('Dashboard data error:', error);
    }
}

function setText(id, value) {
    const el = document.getElementById(id);
    if (el) el.innerText = value;
}

function setHtml(id, value) {
    const el = document.getElementById(id);
    if (el) el.innerHTML = value;
}

function renderSalesChart(salesData) {
    const canvas = document.getElementById('salesChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const labels = [];
    const values = [];

    if (salesData && salesData.length) {
        salesData.forEach(item => {
            labels.push(item.period);
            values.push(parseFloat(item.total_revenue || 0));
        });
    } else {
        for (let i = 6; i >= 0; i--) {
            const d = new Date();
            d.setDate(d.getDate() - i);
            labels.push(d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
            values.push(0);
        }
    }

    if (salesChart) salesChart.destroy();
    salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue ($)',
                data: values,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

async function loadRecentSales() {
    const role = dashboardRole();
    if (role === 'store_keeper') {
        const recentSalesCard = document.getElementById('recentSalesCard');
        if (recentSalesCard) recentSalesCard.style.display = 'none';
        return;
    }
    const recentSalesCard = document.getElementById('recentSalesCard');
    if (recentSalesCard) recentSalesCard.style.display = '';
    try {
        const sales = await API.getSales();
        const tbody = document.getElementById('recentSalesTable');
        if (!tbody) return;

        tbody.innerHTML = '';
        const recent = sales.data ? sales.data.slice(0, 5) : [];
        recent.forEach(sale => {
            tbody.insertAdjacentHTML('beforeend', `
                <tr class="border-b">
                    <td class="py-2">${escapeHtml(sale.invoice_no)}</td>
                    <td>${escapeHtml(sale.customer_name || '')}</td>
                    <td>${formatCurrency(sale.total_amount)}</td>
                    <td>${formatDateTime(sale.sale_date)}</td>
                </tr>
            `);
        });
    } catch (error) {
        console.error('Recent sales error:', error);
    }
}

async function loadAlerts() {
    try {
        const lowStock = await API.getLowStock();
        const expiring = await API.getExpiringSoon();

        const lowStockList = document.getElementById('lowStockList');
        if (lowStockList) {
            lowStockList.innerHTML = '';
            (lowStock.data || []).forEach(drug => {
                const quantity = drug.location_quantity ?? drug.stock;
                const location = drug.location && drug.location !== 'all' ? ` (${drug.location})` : '';
                lowStockList.insertAdjacentHTML('beforeend',
                    `<div class="text-sm border-b py-1">${escapeHtml(drug.name)}${location} &mdash; <span class="font-medium">${quantity}</span> left</div>`);
            });
            if (!(lowStock.data || []).length) {
                lowStockList.innerHTML = '<p class="text-gray-500 text-sm">No low-stock items.</p>';
            }
        }

        const expiringList = document.getElementById('expiringList');
        if (expiringList) {
            expiringList.innerHTML = '';
            (expiring.data || []).forEach(drug => {
                expiringList.insertAdjacentHTML('beforeend',
                    `<div class="text-sm border-b py-1">${escapeHtml(drug.name)} — ${formatDate(drug.expiry_date)}</div>`);
            });
            if (!(expiring.data || []).length) {
                expiringList.innerHTML = '<p class="text-gray-500 text-sm">No near-expiry items in window.</p>';
            }
        }
    } catch (error) {
        console.error('Alerts error:', error);
    }
}
