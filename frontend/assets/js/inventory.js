let currentBranchFilter = '';
let currentSearch = '';
let currentLocationFilter = (window.APP_ROLE || '') === 'manager' ? 'all' : 'store';

document.addEventListener('DOMContentLoaded', function () {
    const locationFilter = document.getElementById('locationFilter');
    if (locationFilter) {
        locationFilter.value = currentLocationFilter;
    }
    loadDrugs();
    loadBranchFilter();
    setupInventoryEventListeners();
});

function canDeleteDrug() {
    return (window.APP_ROLE || '') === 'manager';
}

// Load branches into filter dropdown (managers only)
async function loadBranchFilter() {
    const branchSelect = document.getElementById('branchFilter');
    const role = window.APP_ROLE || '';
    if (role !== 'manager') {
        if (branchSelect && branchSelect.parentElement) {
            branchSelect.parentElement.style.display = 'none';
        }
        currentBranchFilter = String(window.APP_BRANCH_ID || '');
        return;
    }
    try {
        const branches = await API.getBranches();
        if (branchSelect && branches.data) {
            branchSelect.innerHTML = '<option value="">All Branches</option>';
            branches.data.forEach(branch => {
                branchSelect.innerHTML += `<option value="${branch.id}">${escapeHtml(branch.name)}</option>`;
            });
        }
    } catch (error) {
        console.error('Error loading branch filter:', error);
    }
}

async function loadDrugs() {
    try {
        const drugs = await API.getDrugs(currentBranchFilter, currentSearch, currentLocationFilter);
        const tbody = document.getElementById('drugsTable');
        if (!tbody) return;

        updateInventoryHeaders();
        tbody.innerHTML = '';
        if (!drugs.data || drugs.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="12" class="text-center py-4">No drugs found</td></tr>';
            return;
        }

        drugs.data.forEach(drug => {
            const expiryStatus = checkExpiryStatus(drug.expiry_date);
            const expiryClass = expiryStatus.status === 'expired' ? 'text-red-600' : (expiryStatus.status === 'expiring_soon' ? 'text-orange-500' : '');
            const ms = [drug.manufacturer, drug.supplier].filter(Boolean).join(' / ') || '-';
            const deleteBtn = canDeleteDrug()
                ? `<button class="btn-delete action-icon-btn action-delete mr-1" data-id="${drug.id}" title="Delete drug" aria-label="Delete drug">
                        <i class="fas fa-trash"></i>
                   </button>`
                : '';
            const stockCells = getStockCells(drug);
            const row = `
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2">${escapeHtml(drug.name)}</td>
                    <td class="px-4 py-2">${escapeHtml(drug.category || '-')}</td>
                    <td class="px-4 py-2 text-sm">${escapeHtml(ms)}</td>
                    <td class="px-4 py-2">${escapeHtml(drug.batch)}</td>
                    ${stockCells}
                    <td class="px-4 py-2">${formatCurrency(drug.cost_price || 0)}</td>
                    <td class="px-4 py-2">${formatCurrency(drug.price)}</td>
                    <td class="px-4 py-2">
                        ${drug.requires_prescription ? '<span class="px-2 py-1 bg-red-100 text-red-600 text-[10px] font-bold rounded-full uppercase">Rx</span>' : '<span class="text-slate-300">-</span>'}
                    </td>
                    <td class="px-4 py-2 ${expiryClass}">${formatDate(drug.expiry_date)}</td>
                    <td class="px-4 py-2">${escapeHtml(drug.branch_name)}</td>
                    <td class="px-4 py-2 whitespace-nowrap">
                        <button class="btn-edit action-icon-btn action-edit mr-1" data-id="${drug.id}" title="Edit drug" aria-label="Edit drug">
                            <i class="fas fa-pen"></i>
                        </button>
                        ${deleteBtn}
                        <button class="btn-stock action-icon-btn action-stock" data-id="${drug.id}" title="Adjust stock" aria-label="Adjust stock">
                            <i class="fas fa-boxes-stacked"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        // Attach event listeners
        document.querySelectorAll('.btn-edit').forEach(btn => btn.addEventListener('click', () => editDrug(btn.dataset.id)));
        document.querySelectorAll('.btn-delete').forEach(btn => btn.addEventListener('click', () => deleteDrug(btn.dataset.id)));
        document.querySelectorAll('.btn-stock').forEach(btn => btn.addEventListener('click', () => updateStock(btn.dataset.id)));

    } catch (error) {
        console.error('Error loading drugs:', error);
        const tbody = document.getElementById('drugsTable');
        if (tbody) tbody.innerHTML = '<tr><td colspan="12" class="text-center py-4 text-red-600">Error loading drugs. Check console.</td></tr>';
        showToast('Error loading drugs', 'error');
    }
}

function updateInventoryHeaders() {
    const headerA = document.getElementById('stockHeaderA');
    const headerB = document.getElementById('stockHeaderB');
    if (!headerA || !headerB) return;

    if (currentLocationFilter === 'store') {
        headerA.innerText = 'Location';
        headerB.innerText = 'Qty';
    } else if (currentLocationFilter === 'dispensary') {
        headerA.innerText = 'Location';
        headerB.innerText = 'Qty';
    } else {
        headerA.innerText = 'Store';
        headerB.innerText = 'Dispensary';
    }
}

function getStockCells(drug) {
    if (currentLocationFilter === 'store') {
        return `
            <td class="px-4 py-2 text-slate-600">Store</td>
            <td class="px-4 py-2 font-bold text-slate-800" title="Backroom Store Stock">${drug.stock}</td>
        `;
    }
    if (currentLocationFilter === 'dispensary') {
        return `
            <td class="px-4 py-2 text-blue-600">Dispensary</td>
            <td class="px-4 py-2 font-bold text-blue-600" title="Front Shelf Dispensary Stock">${drug.dispensary_stock}</td>
        `;
    }
    return `
        <td class="px-4 py-2 font-bold text-slate-800" title="Backroom Store Stock">${drug.stock}</td>
        <td class="px-4 py-2 font-bold text-blue-600" title="Front Shelf Dispensary Stock">${drug.dispensary_stock}</td>
    `;
}

function setupInventoryEventListeners() {
    const searchInput = document.getElementById('searchDrug');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            currentSearch = e.target.value;
            loadDrugs();
        });
    }

    const branchFilter = document.getElementById('branchFilter');
    if (branchFilter) {
        branchFilter.addEventListener('change', (e) => {
            currentBranchFilter = e.target.value;
            loadDrugs();
        });
    }

    const locationFilter = document.getElementById('locationFilter');
    if (locationFilter) {
        locationFilter.addEventListener('change', (e) => {
            currentLocationFilter = e.target.value || 'all';
            loadDrugs();
        });
    }

    const addDrugBtn = document.getElementById('add-drug-btn');
    if (addDrugBtn) {
        addDrugBtn.addEventListener('click', showDrugModal);
    }
}

function showDrugModal() {
    // Reset form
    document.getElementById('drugId').value = '';
    document.getElementById('drugName').value = '';
    document.getElementById('drugCategory').value = '';
    const mfr = document.getElementById('drugManufacturer');
    const sup = document.getElementById('drugSupplier');
    if (mfr) mfr.value = '';
    if (sup) sup.value = '';
    document.getElementById('drugBatch').value = '';
    document.getElementById('drugStock').value = '';
    document.getElementById('drugPrice').value = '';
    document.getElementById('drugCostPrice').value = '';
    document.getElementById('drugRequiresPrescription').checked = false;
    document.getElementById('drugExpiry').value = '';
    document.getElementById('drugModalTitle').innerText = 'Add Drug';
    document.getElementById('drugModal').classList.remove('hidden');
    document.getElementById('drugModal').classList.add('flex');
    // Load branches into the branch select inside modal
    loadBranchesIntoSelect();
}

async function loadBranchesIntoSelect() {
    const branchSelect = document.getElementById('drugBranch');
    if (!branchSelect) return;
    if ((window.APP_ROLE || '') !== 'manager') {
        const bid = window.APP_BRANCH_ID || '';
        let branchLabel = `Branch #${escapeHtml(String(bid))}`;
        try {
            const branches = await API.getBranches();
            const found = (branches.data || []).find(b => String(b.id) === String(bid));
            if (found) {
                branchLabel = escapeHtml(found.name);
            }
        } catch (e) {
            // Keep fallback label when branch list cannot be loaded.
        }
        branchSelect.innerHTML = `<option value="${bid}">${branchLabel}</option>`;
        branchSelect.disabled = true;
        return;
    }
    branchSelect.disabled = false;
    try {
        const branches = await API.getBranches();
        if (branches.data) {
            branchSelect.innerHTML = '<option value="">Select Branch</option>';
            branches.data.forEach(branch => {
                branchSelect.innerHTML += `<option value="${branch.id}">${escapeHtml(branch.name)}</option>`;
            });
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            if (user.branch_id) branchSelect.value = user.branch_id;
        }
    } catch (error) {
        console.error('Error loading branches for modal:', error);
    }
}

async function editDrug(id) {
    try {
        const drug = await API.getDrug(id);
        if (drug.data) {
            document.getElementById('drugId').value = drug.data.id;
            document.getElementById('drugName').value = drug.data.name;
            document.getElementById('drugCategory').value = drug.data.category || '';
            const mfr = document.getElementById('drugManufacturer');
            const sup = document.getElementById('drugSupplier');
            if (mfr) mfr.value = drug.data.manufacturer || '';
            if (sup) sup.value = drug.data.supplier || '';
            document.getElementById('drugBatch').value = drug.data.batch;
            document.getElementById('drugStock').value = drug.data.stock;
            document.getElementById('drugPrice').value = drug.data.price;
            document.getElementById('drugCostPrice').value = drug.data.cost_price || 0;
            document.getElementById('drugRequiresPrescription').checked = !!drug.data.requires_prescription;
            document.getElementById('drugExpiry').value = drug.data.expiry_date;
            document.getElementById('drugModalTitle').innerText = 'Edit Drug';
            document.getElementById('drugModal').classList.remove('hidden');
            document.getElementById('drugModal').classList.add('flex');
            await loadBranchesIntoSelect();
            document.getElementById('drugBranch').value = drug.data.branch_id;
        }
    } catch (error) {
        showToast('Error loading drug details', 'error');
    }
}

async function saveDrug() {
    const id = document.getElementById('drugId').value;
    const data = {
        name: document.getElementById('drugName').value,
        category: document.getElementById('drugCategory').value,
        manufacturer: document.getElementById('drugManufacturer') ? document.getElementById('drugManufacturer').value : '',
        supplier: document.getElementById('drugSupplier') ? document.getElementById('drugSupplier').value : '',
        batch: document.getElementById('drugBatch').value,
        price: parseFloat(document.getElementById('drugPrice').value),
        cost_price: parseFloat(document.getElementById('drugCostPrice').value),
        requires_prescription: document.getElementById('drugRequiresPrescription').checked ? 1 : 0,
        expiry_date: document.getElementById('drugExpiry').value,
        branch_id: document.getElementById('drugBranch').value,
        stock: parseInt(document.getElementById('drugStock').value) || 0
    };

    // Validate required fields
    if (!data.name || !data.batch || !data.expiry_date || isNaN(data.price) || data.price <= 0) {
        showToast('Name, batch, expiry date and price are required', 'error');
        return;
    }

    try {
        if (id) {
            await API.updateDrug(id, data);
            showToast('Drug updated successfully');
        } else {
            await API.createDrug(data);
            showToast('Drug added successfully');
        }
        closeDrugModal();
        loadDrugs();
    } catch (error) {
        showToast(error.message, 'error');
    }
}

async function deleteDrug(id) {
    const confirmed = await showConfirmDialog({
        title: 'Delete Drug',
        message: 'This action is <strong>permanent</strong> and cannot be undone. Are you sure you want to delete this drug from the inventory?',
        type: 'danger',
        confirmText: 'Yes, Delete',
        cancelText: 'Cancel'
    });
    if (confirmed) {
        try {
            await API.deleteDrug(id);
            showToast('Drug deleted successfully');
            loadDrugs();
        } catch (error) {
            showToast(error.message, 'error');
        }
    }
}

async function updateStock(id) {
    const location = currentLocationFilter;
    if (!['store', 'dispensary'].includes(location)) {
        showToast('Choose Store or Dispensary before adjusting stock', 'error');
        return;
    }

    const quantity = await showPromptDialog({
        title: `Adjust ${location === 'store' ? 'Store' : 'Dispensary'} Stock`,
        message: 'Enter the quantity change. Use a <strong>positive number</strong> to add stock, or a <strong>negative number</strong> to remove stock.',
        placeholder: 'e.g. +50 or -10',
        inputType: 'number',
        confirmText: 'Apply'
    });
    if (quantity === null) return;
    const change = parseInt(quantity);
    if (isNaN(change)) {
        showToast('Please enter a valid number', 'error');
        return;
    }
    const reason = await showPromptDialog({
        title: 'Adjustment Reason',
        message: 'Provide a brief reason for this stock adjustment (for audit trail).',
        placeholder: 'e.g., Restock, Damaged, Expired',
        defaultValue: 'manual',
        confirmText: 'Submit'
    });
    if (reason === null) return;
    try {
        await API.updateStock(id, change, reason || 'manual', location);
        showToast('Stock updated successfully');
        loadDrugs();
    } catch (error) {
        showToast(error.message, 'error');
    }
}

function closeDrugModal() {
    document.getElementById('drugModal').classList.add('hidden');
    document.getElementById('drugModal').classList.remove('flex');
}

// Helper to close modal (used by cancel button)
function closeModal(modalId) {
    if (modalId === 'drugModal') closeDrugModal();
}
