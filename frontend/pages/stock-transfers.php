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
    <div class="p-6">
        <div class="page-toolbar flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold">Stock Transfers</h2>
            <button onclick="showTransferModal()" class="bg-blue-600 text-white px-4 py-2 rounded">+ New Transfer</button>
        </div>
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2">Drug</th>
                        <th class="px-4 py-2">Qty</th>
                        <th class="px-4 py-2">Direction</th>
                        <th class="px-4 py-2">Branch</th>
                        <th class="px-4 py-2">By</th>
                        <th class="px-4 py-2">Date</th>
                        <th class="px-4 py-2">Status</th>
                    </tr>
                </thead>
                <tbody id="transfersTable"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Transfer Modal -->
<div id="transferModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-96">
        <h3 class="text-lg font-bold mb-4">New Stock Transfer</h3>
        <select id="transferDrug" class="w-full border rounded px-3 py-2 mb-3">
            <option value="">Select Drug</option>
        </select>
        <input type="number" id="transferQty" placeholder="Quantity" class="w-full border rounded px-3 py-2 mb-3">
        <select id="transferFrom" class="w-full border rounded px-3 py-2 mb-3">
            <option value="store">Store</option>
            <option value="dispensary">Dispensary</option>
        </select>
        <select id="transferTo" class="w-full border rounded px-3 py-2 mb-3">
            <option value="dispensary">Dispensary</option>
            <option value="store">Store</option>
        </select>
        <div class="flex justify-end space-x-2">
            <button onclick="closeTransferModal()" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
            <button onclick="createTransfer()" class="px-4 py-2 bg-blue-600 text-white rounded">Transfer</button>
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
                            <td class="px-4 py-2">${t.from_location} → ${t.to_location}</td>
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