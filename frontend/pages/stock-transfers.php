<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['manager', 'store_keeper'])) {
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
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Stock Logistics</h2>
            <p class="text-slate-500 mt-1 font-medium">Coordinate medication transfers between stores and dispensaries.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="showTransferModal()" class="btn-premium btn-premium-primary shadow-indigo-200">
                <i class="fas fa-exchange-alt"></i> New Stock Transfer
            </button>
        </div>
    </div>

    <!-- Transfers Table -->
    <div class="card overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-truck-loading text-indigo-500"></i> Transfer Logs
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-separate border-spacing-0">
                <thead>
                    <tr>
                        <th>Medication</th>
                        <th>Qty</th>
                        <th>Movement Path</th>
                        <th>Branch / Facility</th>
                        <th>Authorized By</th>
                        <th>Status</th>
                        <th class="text-right">Timestamp</th>
                    </tr>
                </thead>
                <tbody id="transfersTable">
                    <!-- Data will be injected here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Transfer Modal -->
<div id="transferModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-[100] p-4">
    <div class="bg-white/95 backdrop-blur shadow-2xl rounded-3xl p-8 w-full max-w-md animate-fade-in border border-white/20">
        <div class="flex items-center justify-between mb-8">
            <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">Initiate Transfer</h3>
            <button onclick="closeTransferModal()" class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Select Medication</label>
                <select id="transferDrug" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium text-slate-600">
                    <option value="">Loading drugs...</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Transfer Quantity</label>
                <input type="number" id="transferQty" placeholder="0" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Source</label>
                    <select id="transferFrom" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium text-slate-600">
                        <option value="store">Store</option>
                        <option value="dispensary">Dispensary</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Destination</label>
                    <select id="transferTo" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium text-slate-600">
                        <option value="dispensary">Dispensary</option>
                        <option value="store">Store</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="mt-8 flex gap-3">
            <button onclick="closeTransferModal()" class="flex-1 px-6 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition-all">Cancel</button>
            <button onclick="createTransfer()" class="flex-1 px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all">Execute Transfer</button>
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
                    const statusClass = t.status === 'completed' ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600';
                    const pathIcon = t.from_location === 'store' ? 'fa-warehouse' : 'fa-hand-holding-medical';
                    const toIcon = t.to_location === 'dispensary' ? 'fa-hand-holding-medical' : 'fa-warehouse';

                    tbody.innerHTML += `
                        <tr class="group hover:bg-slate-50 transition-colors">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs border border-indigo-100">
                                        <i class="fas fa-pills"></i>
                                    </div>
                                    <span class="font-bold text-slate-800">${escapeHtml(t.drug_name)}</span>
                                </div>
                            </td>
                            <td><span class="font-black text-slate-900">${t.quantity}</span></td>
                            <td>
                                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-tighter">
                                    <span class="text-slate-400"><i class="fas ${pathIcon} mr-1"></i> ${t.from_location}</span>
                                    <i class="fas fa-long-arrow-alt-right text-indigo-400"></i>
                                    <span class="text-indigo-600"><i class="fas ${toIcon} mr-1"></i> ${t.to_location}</span>
                                </div>
                            </td>
                            <td><span class="text-xs font-bold text-slate-500">${escapeHtml(t.branch_name)}</span></td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500">
                                        ${t.created_by_name.charAt(0)}
                                    </div>
                                    <span class="text-xs font-bold text-slate-600">${escapeHtml(t.created_by_name)}</span>
                                </div>
                            </td>
                            <td>
                                <span class="inline-flex px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider ${statusClass}">
                                    ${t.status}
                                </span>
                            </td>
                            <td class="text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                ${formatDateTime(t.transfer_date)}
                            </td>
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
            const drugs = await API.getDrugs();
            const select = document.getElementById('transferDrug');
            select.innerHTML = '<option value="">Select Drug</option>';
            if (drugs.data) {
                drugs.data.forEach(drug => {
                    select.innerHTML += `<option value="${drug.id}" data-stock="${drug.stock}">${escapeHtml(drug.name)} (Stock: ${drug.stock})</option>`;
                });
            }
        } catch (err) {
            console.error(err);
        }
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
                from_location: fromLocation,
                to_location: toLocation
            });
            if (result.success) {
                showToast('Transfer created successfully');
                closeTransferModal();
                loadTransfers();
            } else {
                showToast(result.message || 'Transfer failed', 'error');
            }
        } catch (err) {
            showToast(err.message, 'error');
        }
    }

    loadTransfers();
</script>
<?php include '../includes/footer.php'; ?>