<?php
require_once __DIR__ . '/../includes/init_session.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header('Location: dashboard.php');
    exit;
}
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="ml-64 flex-1">
    <?php include '../includes/navbar.php'; ?>
    <div class="p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold">Branches</h2>
            <button onclick="showBranchModal()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">+ Add
                Branch</button>
        </div>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">Name</th>
                        <th class="px-6 py-3 text-left">Address</th>
                        <th class="px-6 py-3 text-left">Phone</th>
                        <th class="px-6 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody id="branchesTable"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="branchModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-96">
        <h3 class="text-lg font-bold mb-4" id="modalTitle">Add Branch</h3>
        <input type="hidden" id="branchId">
        <input type="text" id="branchName" placeholder="Branch Name" class="w-full border rounded px-3 py-2 mb-3">
        <textarea id="branchAddress" placeholder="Address" class="w-full border rounded px-3 py-2 mb-3"></textarea>
        <input type="text" id="branchPhone" placeholder="Phone" class="w-full border rounded px-3 py-2 mb-3">
        <div class="flex justify-end space-x-2">
            <button onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
            <button onclick="saveBranch()" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
        </div>
    </div>
</div>

<script src="../assets/js/utils.js"></script>
<script src="../assets/js/api.js"></script>
<script>
    async function loadBranches() {
        const res = await API.getBranches();
        const tbody = document.getElementById('branchesTable');
        tbody.innerHTML = '';
        if (res.data) {
            res.data.forEach(b => {
                tbody.innerHTML += `
                    <tr class="border-b">
                        <td class="px-6 py-3">${escapeHtml(b.name)}</td>
                        <td class="px-6 py-3">${escapeHtml(b.address || '-')}</td>
                        <td class="px-6 py-3">${escapeHtml(b.phone || '-')}</td>
                        <td class="px-6 py-3">
                            <button onclick="editBranch(${b.id}, '${escapeHtml(b.name)}', '${escapeHtml(b.address || '')}', '${escapeHtml(b.phone || '')}')" class="text-blue-600 mr-2">Edit</button>
                            <button onclick="deleteBranch(${b.id})" class="text-red-600">Delete</button>
                        </td>
                    </tr>
                `;
            });
        }
    }

    function showBranchModal() {
        document.getElementById('branchId').value = '';
        document.getElementById('branchName').value = '';
        document.getElementById('branchAddress').value = '';
        document.getElementById('branchPhone').value = '';
        document.getElementById('modalTitle').innerText = 'Add Branch';
        document.getElementById('branchModal').classList.remove('hidden');
        document.getElementById('branchModal').classList.add('flex');
    }

    function editBranch(id, name, address, phone) {
        document.getElementById('branchId').value = id;
        document.getElementById('branchName').value = name;
        document.getElementById('branchAddress').value = address;
        document.getElementById('branchPhone').value = phone;
        document.getElementById('modalTitle').innerText = 'Edit Branch';
        document.getElementById('branchModal').classList.remove('hidden');
        document.getElementById('branchModal').classList.add('flex');
    }

    async function saveBranch() {
        const id = document.getElementById('branchId').value;
        const data = {
            name: document.getElementById('branchName').value,
            address: document.getElementById('branchAddress').value,
            phone: document.getElementById('branchPhone').value
        };
        try {
            if (id) {
                await API.updateBranch(id, data);
                showToast('Branch updated');
            } else {
                await API.createBranch(data);
                showToast('Branch added');
            }
            closeModal();
            loadBranches();
        } catch (err) {
            showToast(err.message, 'error');
        }
    }

    async function deleteBranch(id) {
        if (confirm('Delete this branch? It may affect users and drugs.')) {
            try {
                await API.deleteBranch(id);
                showToast('Branch deleted');
                loadBranches();
            } catch (err) {
                showToast(err.message, 'error');
            }
        }
    }

    function closeModal() {
        document.getElementById('branchModal').classList.add('hidden');
        document.getElementById('branchModal').classList.remove('flex');
    }

    loadBranches();
</script>
<?php include '../includes/footer.php'; ?>