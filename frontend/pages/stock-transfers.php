<?php
require_once __DIR__ . '/../includes/init_session.php';
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['manager', 'store_keeper'])) {
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
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Stock Transfers</h2>
                <p class="text-slate-500 font-medium">Monitor and manage internal stock movements between locations.</p>
            </div>
            <?php if ($_SESSION['role'] === 'store_keeper'): ?>
                <button onclick="showTransferModal()" class="btn btn-primary shadow-lg shadow-blue-500/20">
                    <i class="fas fa-exchange-alt"></i> New Stock Transfer
                </button>
            <?php endif; ?>
        </div>

        <div class="card animate-slide-up" style="animation-delay: 0.1s;">
            <div class="table-container">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th>Drug Item</th>
                            <th>Qty</th>
                            <th>Direction</th>
                            <th>Source Branch</th>
                            <th>Requested By</th>
                            <th>Transfer Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="transfersTable"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Transfer Modal -->
<div id="transferModal" class="modal-backdrop hidden z-50">
    <div class="modal-content !max-w-md">
        <div class="flex items-center justify-between p-6 border-b border-slate-100">
            <h3 class="text-xl font-bold text-slate-800">New Stock Transfer</h3>
            <button onclick="closeTransferModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fas fa-times-circle text-xl"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Select Drug</label>
                <select id="transferDrug" class="!bg-slate-50">
                    <option value="">Choose a drug...</option>
                </select>
            </div>
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Transfer Quantity</label>
                <input type="number" id="transferQty" placeholder="Enter amount" class="!bg-slate-50">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">From Location</label>
                    <select id="transferFrom" class="!bg-slate-50">
                        <option value="store">Store</option>
                        <option value="dispensary">Dispensary</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">To Location</label>
                    <select id="transferTo" class="!bg-slate-50">
                        <option value="dispensary">Dispensary</option>
                        <option value="store">Store</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 rounded-b-lg">
            <button onclick="closeTransferModal()" class="btn !bg-white border border-slate-200 text-slate-600 hover:bg-slate-100">Cancel</button>
            <button onclick="createTransfer()" class="btn btn-primary px-8">Execute Transfer</button>
        </div>
    </div>
</div>

<script src="../assets/js/utils.js"></script>
<script src="../assets/js/api.js"></script>
<script>
    async function loadTransfers() {
        try {
            const res = await API.getTransfers();
            const tbody = document.getElementById('transfersTable');
            tbody.innerHTML = '';
            if (res.data && res.data.length) {
                res.data.forEach(t => {
                    tbody.innerHTML += `
                        <tr class="border-b">
                            <td class="px-4 py-2">${escapeHtml(t.drug_name)}</td>
                            <td class="px-4 py-2">${t.quantity}</td>
                            <td class="px-4 py-2">${t.source_location || t.from_location} &rarr; ${t.destination_location || t.to_location}</td>
                            <td class="px-4 py-2">${escapeHtml(t.branch_name)}</td>
                            <td class="px-4 py-2">${escapeHtml(t.created_by_name)}</td>
                            <td class="px-4 py-2">${formatDateTime(t.transfer_date)}</td>
                            <td class="px-4 py-2">${t.status}</td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4">No transfers found</td></tr>';
            }
        } catch (err) {
            console.error(err);
            showToast('Failed to load transfers', 'error');
        }
    }

    async function loadDrugsForTransfer() {
        try {
            const drugs = await API.getDrugs(null, '', 'store');
            const select = document.getElementById('transferDrug');
            select.innerHTML = '<option value="">Select Drug</option>';
            if (drugs.data) {
                drugs.data.forEach(drug => {
                    select.innerHTML += `<option value="${drug.id}" data-store="${drug.stock}" data-dispensary="${drug.dispensary_stock}">${escapeHtml(drug.name)} (Store: ${drug.stock}, Dispensary: ${drug.dispensary_stock})</option>`;
                });
            }
            updateTransferDrugLabels();
        } catch (err) {
            console.error(err);
        }
    }

    function updateTransferDrugLabels() {
        const source = document.getElementById('transferFrom')?.value || 'store';
        const select = document.getElementById('transferDrug');
        if (!select) return;

        Array.from(select.options).forEach(option => {
            if (!option.value) return;
            const storeQty = option.dataset.store || '0';
            const dispensaryQty = option.dataset.dispensary || '0';
            const baseName = option.textContent.split(' (')[0];
            const available = source === 'dispensary' ? dispensaryQty : storeQty;
            option.textContent = `${baseName} (${source === 'dispensary' ? 'Dispensary' : 'Store'}: ${available})`;
        });
    }

    function showTransferModal() {
        document.getElementById('transferModal').classList.remove('hidden');
        document.getElementById('transferModal').classList.add('flex');
        loadDrugsForTransfer();
        document.getElementById('transferQty').value = '';
        document.getElementById('transferFrom').value = 'store';
        document.getElementById('transferTo').value = 'dispensary';
    }

    function closeTransferModal() {
        document.getElementById('transferModal').classList.add('hidden');
        document.getElementById('transferModal').classList.remove('flex');
    }

    async function createTransfer() {
        const drugId = document.getElementById('transferDrug').value;
        const quantity = parseInt(document.getElementById('transferQty').value);
        const fromLocation = document.getElementById('transferFrom').value;
        const toLocation = document.getElementById('transferTo').value;

        if (!drugId || quantity <= 0) {
            showToast('Please select a drug and enter a valid quantity', 'error');
            return;
        }

        try {
            const result = await API.createTransfer({
                drug_id: drugId,
                quantity: quantity,
                source_location: fromLocation,
                destination_location: toLocation
            });
            if (result.success) {
                showToast('Transfer completed and inventory updated');
                closeTransferModal();
                loadTransfers();
            } else {
                showToast(result.message || 'Transfer failed', 'error');
            }
        } catch (err) {
            showToast(err.message, 'error');
        }
    }

    document.getElementById('transferFrom')?.addEventListener('change', updateTransferDrugLabels);
    loadTransfers();
</script>
<?php include '../includes/footer.php'; ?>
