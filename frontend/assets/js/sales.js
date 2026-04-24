let currentSaleCart = [];
let currentDrugsList = [];

document.addEventListener('DOMContentLoaded', function () {
    // If on sales.php, load the table
    if (document.getElementById('sales-table-body')) {
        loadSalesTable();
    }
    // If on new-sale.php, load drugs
    if (document.getElementById('drugSelect')) {
        loadDrugsForSale();
    }
});

async function loadSalesTable() {
    try {
        const sales = await API.getSales();
        const tbody = document.getElementById('sales-table-body');
        if (!tbody) return;
        tbody.innerHTML = '';
        if (sales.data && sales.data.length) {
            sales.data.forEach(sale => {
                const methodBadge = sale.payment_method === 'Cash' ? 'bg-emerald-100 text-emerald-600' : (sale.payment_method === 'Card' ? 'bg-blue-100 text-blue-600' : 'bg-amber-100 text-amber-600');
                tbody.innerHTML += `
                    <tr class="group hover:bg-slate-50 transition-colors">
                        <td><code class="text-xs font-bold text-indigo-500 bg-indigo-50 px-2 py-1 rounded-lg">${sale.invoice_no}</code></td>
                        <td>
                            <p class="font-bold text-slate-800 text-sm">${escapeHtml(sale.customer_name)}</p>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">${formatDateTime(sale.sale_date)}</p>
                        </td>
                        <td class="text-slate-600 font-bold text-sm">${sale.items_count || '0'} Items</td>
                        <td class="font-black text-slate-900">${formatCurrency(sale.total_amount)}</td>
                        <td>
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider ${methodBadge}">
                                ${sale.payment_method}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500">
                                    ${sale.pharmacist_name.charAt(0)}
                                </div>
                                <span class="text-xs font-bold text-slate-600">${escapeHtml(sale.pharmacist_name)}</span>
                            </div>
                        </td>
                        <td class="text-right">
                            <button onclick="viewSale(${sale.id})" 
                                class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 transition-all flex items-center justify-center mx-auto lg:ml-auto" title="View Invoice">
                                <i class="fas fa-eye text-xs"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4">No sales found</td></tr>';
        }
    } catch (err) {
        console.error(err);
        showToast('Failed to load sales', 'error');
    }
}

async function loadDrugsForSale() {
    try {
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        const branchId = user.branch_id || '';
        const drugs = await API.getDrugs(branchId);
        const select = document.getElementById('drugSelect');
        if (!select) return;
        select.innerHTML = '<option value="">Select drug</option>';
        currentDrugsList = drugs.data || [];
        currentDrugsList.forEach(drug => {
            select.innerHTML += `<option value="${drug.id}" data-price="${drug.price}" data-stock="${drug.stock}">${escapeHtml(drug.name)} - $${drug.price} (Stock: ${drug.stock})</option>`;
        });
    } catch (err) {
        console.error('Error loading drugs for sale:', err);
        showToast('Failed to load drugs', 'error');
    }
}

function addToCart() {
    const drugSelect = document.getElementById('drugSelect');
    const drugId = drugSelect.value;
    const quantity = parseInt(document.getElementById('itemQty').value);
    if (!drugId || quantity <= 0) {
        showToast('Select a drug and valid quantity', 'error');
        return;
    }
    const drug = currentDrugsList.find(d => d.id == drugId);
    if (!drug) return;
    if (quantity > drug.stock) {
        showToast(`Only ${drug.stock} units available`, 'error');
        return;
    }
    const existing = currentSaleCart.find(item => item.drug_id == drugId);
    if (existing) {
        existing.quantity += quantity;
    } else {
        currentSaleCart.push({
            drug_id: drug.id,
            name: drug.name,
            quantity: quantity,
            price: parseFloat(drug.price)
        });
    }
    updateCartDisplay();
    document.getElementById('itemQty').value = 1;
    drugSelect.value = '';
    showToast('Item added to cart', 'success');
}

function updateCartDisplay() {
    const cartBody = document.getElementById('cartBody');
    const totalSpan = document.getElementById('cartTotal');
    if (!cartBody) return;
    cartBody.innerHTML = '';
    let total = 0;
    currentSaleCart.forEach((item, index) => {
        const subtotal = item.quantity * item.price;
        total += subtotal;
        cartBody.innerHTML += `
            <tr class="group hover:bg-slate-50 transition-colors">
                <td>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-[10px]">
                            <i class="fas fa-pills"></i>
                        </div>
                        <span class="font-bold text-slate-700 text-sm">${escapeHtml(item.name)}</span>
                    </div>
                </td>
                <td class="text-slate-600 font-medium text-sm">${formatCurrency(item.price)}</td>
                <td class="text-slate-600 font-bold text-sm">x${item.quantity}</td>
                <td class="text-right font-black text-slate-900 text-sm">${formatCurrency(subtotal)}</td>
                <td class="text-right">
                    <button onclick="removeFromCart(${index})" class="w-8 h-8 rounded-lg bg-slate-100 text-rose-500 hover:bg-rose-50 hover:text-rose-600 transition-all flex items-center justify-center" title="Remove">
                        <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    const discount = parseFloat(document.getElementById('discountAmount')?.value || 0);
    const finalTotal = Math.max(0, total - discount);
    totalSpan.innerText = formatCurrency(finalTotal);
}

function removeFromCart(index) {
    currentSaleCart.splice(index, 1);
    updateCartDisplay();
    showToast('Item removed from basket', 'info');
}

async function clearCart() {
    if (currentSaleCart.length === 0) return;
    const confirmed = await showConfirm('Clear Basket', 'Are you sure you want to remove all items from your current sales basket?');
    if (confirmed) {
        currentSaleCart = [];
        updateCartDisplay();
        showToast('Basket cleared');
    }
}

async function completeSale() {
    if (currentSaleCart.length === 0) {
        showToast('Cart is empty', 'error');
        return;
    }
    const customerName = document.getElementById('customerName').value.trim() || 'Walk-in customer';
    const paymentMethod = document.getElementById('paymentMethod').value;
    const discount = parseFloat(document.getElementById('discountAmount')?.value || 0);
    const prescriptionRef = document.getElementById('prescriptionRef')?.value || null;
    const saleData = {
        customer_name: customerName,
        payment_method: paymentMethod,
        discount_amount: discount,
        prescription_ref: prescriptionRef,
        items: currentSaleCart.map(item => ({
            drug_id: item.drug_id,
            quantity: item.quantity
        }))
    };
    try {
        const result = await API.createSale(saleData);
        if (result.success) {
            showToast(`Sale completed! Invoice: ${result.data.invoice_no}`);
            // Redirect to sales list page after short delay
            setTimeout(() => {
                window.location.href = 'sales.php';
            }, 1500);
        } else {
            showToast(result.message || 'Sale failed', 'error');
        }
    } catch (err) {
        showToast(err.message, 'error');
    }
}

async function viewSale(id) {
    try {
        const res = await API.getSale(id);
        if (!res.success) throw new Error(res.message);
        const sale = res.data;

        const panel = document.getElementById('saleDetailsPanel');
        const content = document.getElementById('detailContent');
        const invoiceNo = document.getElementById('detailInvoiceNo');

        invoiceNo.innerText = `#${sale.invoice_no}`;
        
        let itemsHtml = '';
        sale.items.forEach(item => {
            itemsHtml += `
                <div class="flex justify-between items-center py-2 border-b border-slate-50 last:border-0">
                    <div>
                        <p class="text-sm font-bold text-slate-800">${escapeHtml(item.drug_name)}</p>
                        <p class="text-[10px] text-slate-400 font-bold uppercase">Qty: ${item.quantity} × ${formatCurrency(item.price)}</p>
                    </div>
                    <span class="font-black text-slate-700 text-sm">${formatCurrency(item.quantity * item.price)}</span>
                </div>
            `;
        });

        content.innerHTML = `
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Customer Information</p>
                <div class="flex items-center gap-3 bg-slate-50 p-3 rounded-2xl">
                    <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-indigo-500">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 text-sm">${escapeHtml(sale.customer_name)}</p>
                        <p class="text-[10px] text-slate-400 font-bold">${formatDateTime(sale.sale_date)}</p>
                    </div>
                </div>
            </div>

            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Purchased Items</p>
                <div class="space-y-1">${itemsHtml}</div>
            </div>

            <div class="bg-indigo-600 rounded-2xl p-5 text-white shadow-lg shadow-indigo-200">
                <div class="flex justify-between items-center text-xs opacity-80 mb-1">
                    <span>Subtotal</span>
                    <span>${formatCurrency(parseFloat(sale.total_amount) + parseFloat(sale.discount_amount))}</span>
                </div>
                <div class="flex justify-between items-center text-xs opacity-80 mb-3 pb-3 border-b border-white/10">
                    <span>Discount</span>
                    <span>-${formatCurrency(sale.discount_amount)}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs font-bold uppercase tracking-wider">Total Amount</span>
                    <span class="text-xl font-black">${formatCurrency(sale.total_amount)}</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Payment Method</p>
                    <p class="text-xs font-bold text-slate-700">${sale.payment_method}</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Pharmacist</p>
                    <p class="text-xs font-bold text-slate-700">${escapeHtml(sale.pharmacist_name)}</p>
                </div>
            </div>
        `;

        panel.classList.remove('hidden');
        panel.classList.add('block');
        
        // Scroll into view if needed on mobile
        if (window.innerWidth < 1024) {
            panel.scrollIntoView({ behavior: 'smooth' });
        }

    } catch (err) {
        showToast(err.message, 'error');
    }
}

function closeDetails() {
    const panel = document.getElementById('saleDetailsPanel');
    panel.classList.add('hidden');
    panel.classList.remove('block');
}