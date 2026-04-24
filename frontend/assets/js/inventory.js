let currentBranchFilter = '';
let currentSearch = '';

document.addEventListener('DOMContentLoaded', function () {
    loadDrugs();
    loadBranchFilter();
    setupInventoryEventListeners();
});

// Load branches into filter dropdown
async function loadBranchFilter() {
    try {
        const branches = await API.getBranches();
        const branchSelect = document.getElementById('branchFilter');
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
        const drugs = await API.getDrugs(currentBranchFilter, currentSearch);
        const tbody = document.getElementById('drugsTable');
        if (!tbody) return;

        tbody.innerHTML = '';
        if (!drugs.data || drugs.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4">No drugs found</td></tr>';
            return;
        }

        drugs.data.forEach(drug => {
            const expiryStatus = checkExpiryStatus(drug.expiry_date);
            let expiryStatusClass = 'bg-slate-100 text-slate-600';
            if (expiryStatus.status === 'expired') expiryStatusClass = 'bg-rose-100 text-rose-600';
            else if (expiryStatus.status === 'expiring_soon') expiryStatusClass = 'bg-amber-100 text-amber-600';

            const stockStatus = drug.stock <= 10 ? 'bg-rose-100 text-rose-600' : (drug.stock <= 50 ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600');

            const row = `
                <tr class="group hover:bg-slate-50 transition-colors">
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs border border-indigo-100">
                                <i class="fas fa-capsules"></i>
                            </div>
                            <div>
                                <p class="font-bold text-slate-800">${escapeHtml(drug.name)}</p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">${escapeHtml(drug.manufacturer || 'General Pharma')}</p>
                            </div>
                        </div>
                    </td>
                    <td><span class="text-xs font-bold text-slate-500 bg-slate-100 px-2 py-1 rounded-lg uppercase tracking-tight">${escapeHtml(drug.category || 'General')}</span></td>
                    <td><code class="text-xs font-bold text-indigo-500 bg-indigo-50 px-2 py-1 rounded-lg">${escapeHtml(drug.batch)}</code></td>
                    <td>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold ${stockStatus}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            ${drug.stock} Units
                        </span>
                    </td>
                    <td>
                        <div class="font-bold text-slate-900">${formatCurrency(drug.price)}</div>
                        <div class="text-[10px] text-slate-400 font-bold">Cost: ${formatCurrency(drug.cost_price)}</div>
                    </td>
                    <td>
                        <span class="inline-flex px-2 py-1 rounded-lg text-xs font-bold ${expiryStatusClass}">
                            ${formatDate(drug.expiry_date)}
                        </span>
                    </td>
                    <td><span class="text-xs font-bold text-slate-500">${escapeHtml(drug.branch_name)}</span></td>
                    <td class="text-right">
                        <div class="flex justify-end gap-2">
                            <button onclick="updateStock(${drug.id})" class="w-8 h-8 rounded-lg bg-slate-100 text-emerald-500 hover:bg-emerald-50 hover:text-emerald-600 transition-all flex items-center justify-center" title="Update Stock">
                                <i class="fas fa-boxes text-xs"></i>
                            </button>
                            <button onclick="editDrug(${drug.id})" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 transition-all flex items-center justify-center" title="Edit">
                                <i class="fas fa-edit text-xs"></i>
                            </button>
                            <button onclick="deleteDrug(${drug.id})" class="w-8 h-8 rounded-lg bg-slate-100 text-rose-500 hover:bg-rose-50 hover:text-rose-600 transition-all flex items-center justify-center" title="Delete">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        </div>
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
        if (tbody) tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-red-600">Error loading drugs. Check console.</td></tr>';
        showToast('Error loading drugs', 'error');
    }
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
    document.getElementById('drugBatch').value = '';
    document.getElementById('drugStock').value = '';
    document.getElementById('drugPrice').value = '';
    document.getElementById('drugCostPrice').value = '';
    document.getElementById('drugManufacturer').value = '';
    document.getElementById('drugSupplier').value = '';
    document.getElementById('drugExpiry').value = '';
    document.getElementById('drugModalTitle').innerText = 'Add Drug';
    document.getElementById('drugModal').classList.remove('hidden');
    document.getElementById('drugModal').classList.add('flex');
    // Load branches into the branch select inside modal
    loadBranchesIntoSelect();
}

async function loadBranchesIntoSelect() {
    try {
        const branches = await API.getBranches();
        const branchSelect = document.getElementById('drugBranch');
        if (branchSelect && branches.data) {
            branchSelect.innerHTML = '<option value="">Select Branch</option>';
            branches.data.forEach(branch => {
                branchSelect.innerHTML += `<option value="${branch.id}">${escapeHtml(branch.name)}</option>`;
            });
            // Pre-select current user's branch if available
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
            document.getElementById('drugBatch').value = drug.data.batch;
            document.getElementById('drugStock').value = drug.data.stock;
            document.getElementById('drugPrice').value = drug.data.price;
            document.getElementById('drugCostPrice').value = drug.data.cost_price || 0;
            document.getElementById('drugManufacturer').value = drug.data.manufacturer || '';
            document.getElementById('drugSupplier').value = drug.data.supplier || '';
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
        batch: document.getElementById('drugBatch').value,
        price: parseFloat(document.getElementById('drugPrice').value),
        cost_price: parseFloat(document.getElementById('drugCostPrice').value) || 0,
        manufacturer: document.getElementById('drugManufacturer').value,
        supplier: document.getElementById('drugSupplier').value,
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
    const confirmed = await showConfirm('Delete Medication', 'Are you sure you want to permanently remove this drug from the inventory? This action cannot be undone.');
    if (confirmed) {
        try {
            await API.deleteDrug(id);
            showToast('Drug deleted');
            loadDrugs();
        } catch (error) {
            showToast(error.message, 'error');
        }
    }
}

async function updateStock(id) {
    try {
        const drug = await API.getDrug(id);
        if (drug.data) {
            document.getElementById('stockDrugId').value = drug.data.id;
            document.getElementById('stockDrugName').innerText = drug.data.name;
            document.getElementById('stockChange').value = '';
            document.getElementById('stockReason').value = 'manual';
            document.getElementById('stockModal').classList.remove('hidden');
            document.getElementById('stockModal').classList.add('flex');
        }
    } catch (error) {
        showToast('Error loading drug info', 'error');
    }
}

async function saveStockUpdate() {
    const id = document.getElementById('stockDrugId').value;
    const change = parseInt(document.getElementById('stockChange').value);
    const reason = document.getElementById('stockReason').value;

    if (isNaN(change) || change === 0) {
        showToast('Please enter a valid quantity change', 'error');
        return;
    }

    try {
        await API.updateStock(id, change, reason);
        showToast('Stock updated successfully');
        closeStockModal();
        loadDrugs();
    } catch (error) {
        showToast(error.message, 'error');
    }
}

function closeStockModal() {
    document.getElementById('stockModal').classList.add('hidden');
    document.getElementById('stockModal').classList.remove('flex');
}

function closeDrugModal() {
    document.getElementById('drugModal').classList.add('hidden');
    document.getElementById('drugModal').classList.remove('flex');
}

// Helper to close modal (used by cancel button)
function closeModal(modalId) {
    if (modalId === 'drugModal') closeDrugModal();
}