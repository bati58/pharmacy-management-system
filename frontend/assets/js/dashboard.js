// Dashboard specific code
let salesChart = null;

document.addEventListener('DOMContentLoaded', function () {
    loadDashboardData();
    loadRecentSales();
    loadAlerts();
});

async function loadDashboardData() {
    try {
        // Get sales report for last 7 days
        const report = await API.getSalesReport('weekly');
        const branches = await API.getBranches();
        const drugs = await API.getDrugs();

        // Calculate KPIs
        const totalBranches = branches.data ? branches.data.length : 0;
        const totalDrugs = drugs.data ? drugs.data.length : 0;
        let totalSales = 0;
        let totalRevenue = 0;

        if (report.data && report.data.length) {
            totalSales = report.data.reduce((sum, day) => sum + day.transaction_count, 0);
            totalRevenue = report.data.reduce((sum, day) => sum + day.total_revenue, 0);
        }

        document.getElementById('kpi-branches').innerText = totalBranches;
        document.getElementById('kpi-drugs').innerText = totalDrugs;
        document.getElementById('kpi-sales').innerText = totalSales;
        document.getElementById('kpi-revenue').innerText = formatCurrency(totalRevenue);

        // Render chart
        renderSalesChart(report.data);

    } catch (error) {
        console.error('Dashboard data error:', error);
    }
}

async function loadRecentSales() {
    try {
        const sales = await API.getSales();
        const tbody = document.getElementById('recentSalesTable');
        if (!tbody) return;

        tbody.innerHTML = '';
        const recent = sales.data ? sales.data.slice(0, 5) : [];
        
        if (recent.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-slate-400">No recent transactions found</td></tr>';
            return;
        }

        recent.forEach(sale => {
            const row = `
                <tr class="group hover:bg-slate-50 transition-colors">
                    <td class="font-bold text-indigo-600">#${sale.invoice_no}</td>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 text-xs">
                                ${sale.customer_name ? sale.customer_name.charAt(0) : 'C'}
                            </div>
                            <span class="font-medium">${sale.customer_name || 'Walk-in Customer'}</span>
                        </div>
                    </td>
                    <td class="font-bold text-slate-900">${formatCurrency(sale.total_amount)}</td>
                    <td><span class="text-xs font-semibold text-slate-500">${sale.branch_name || 'Main Branch'}</span></td>
                    <td><span class="status-pill active">Completed</span></td>
                    <td class="text-slate-500 text-xs">${formatDateTime(sale.sale_date)}</td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });
    } catch (error) {
        console.error('Recent sales error:', error);
    }
}

async function loadAlerts() {
    try {
        const lowStock = await API.getLowStock();
        const lowStockList = document.getElementById('lowStockList');
        if (!lowStockList) return;

        lowStockList.innerHTML = '';
        const alerts = lowStock.data || [];
        
        if (alerts.length === 0) {
            lowStockList.innerHTML = '<div class="text-center py-4 text-slate-400 text-sm">No critical stock alerts</div>';
            return;
        }

        alerts.forEach(drug => {
            const item = `
                <div class="flex items-center justify-between p-3 rounded-xl bg-rose-50 border border-rose-100 pulse-warning">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-rose-100 flex items-center justify-center text-rose-600">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">${drug.name}</p>
                            <p class="text-xs text-rose-600 font-medium">${drug.stock} units remaining</p>
                        </div>
                    </div>
                    <button class="text-rose-400 hover:text-rose-600"><i class="fas fa-chevron-right"></i></button>
                </div>
            `;
            lowStockList.insertAdjacentHTML('beforeend', item);
        });
    } catch (error) {
        console.error('Alerts error:', error);
    }
}

function renderSalesChart(salesData) {
    const ctx = document.getElementById('salesChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(99, 102, 241, 0.4)');
    gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');

    const labels = [];
    const values = [];

    if (salesData && salesData.length) {
        salesData.forEach(item => {
            labels.push(item.period);
            values.push(item.total_revenue);
        });
    } else {
        for (let i = 6; i >= 0; i--) {
            const d = new Date();
            d.setDate(d.getDate() - i);
            labels.push(d.toLocaleDateString('en-US', { weekday: 'short' }));
            values.push(0);
        }
    }

    if (salesChart) salesChart.destroy();
    salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue',
                data: values,
                backgroundColor: gradient,
                borderColor: '#6366f1',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#6366f1',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                    ticks: { color: '#64748b', font: { size: 10, weight: 'bold' } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#64748b', font: { size: 10, weight: 'bold' } }
                }
            }
        }
    });
}