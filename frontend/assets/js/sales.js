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
                tbody.innerHTML += `
                    <tr class="border-b">
                        <td class="px-4 py-2">${sale.invoice_no}</td>
                        <td class="px-4 py-2">${escapeHtml(sale.customer_name)}</td>
                        <td class="px-4 py-2">${sale.items_count || '-'}</td>
                        <td class="px-4 py-2">${formatCurrency(sale.total_amount)}</td>
                        <td class="px-4 py-2">${sale.payment_method}</td>
                        <td class="px-4 py-2">${escapeHtml(sale.pharmacist_name)}</td>
                        <td class="px-4 py-2">${formatDateTime(sale.sale_date)}</td>
                        <td class="px-4 py-2">
                            <button class="action-icon-btn action-view" onclick="viewSale(${sale.id})" title="View sale" aria-label="View sale">
                                <i class="fas fa-eye"></i>
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
            <tr>
                <td class="px-2 py-1">${escapeHtml(item.name)}</td>
                <td class="px-2 py-1">${item.quantity}</td>
                <td class="px-2 py-1">${formatCurrency(item.price)}</td>
                <td class="px-2 py-1">${formatCurrency(subtotal)}</td>
                <td class="px-2 py-1">
                    <button class="action-icon-btn action-delete" onclick="removeFromCart(${index})" title="Remove item" aria-label="Remove item">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    totalSpan.innerText = formatCurrency(total);
}

function removeFromCart(index) {
    currentSaleCart.splice(index, 1);
    updateCartDisplay();
    showToast('Item removed', 'info');
}

async function completeSale() {
    if (currentSaleCart.length === 0) {
        showToast('Cart is empty', 'error');
        return;
    }
    const customerName = document.getElementById('customerName').value.trim() || 'Walk-in customer';
    const paymentMethod = document.getElementById('paymentMethod').value;
    const discountAmount = parseFloat(document.getElementById('discountAmount')?.value || '0');
    const prescriptionReference = document.getElementById('prescriptionReference')?.value?.trim() || '';
    if (discountAmount < 0) {
        showToast('Discount cannot be negative', 'error');
        return;
    }

    const saleData = {
        customer_name: customerName,
        payment_method: paymentMethod,
        discount_amount: discountAmount,
        prescription_reference: prescriptionReference,
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

function viewSale(id) {
    alert('View sale ' + id);
}