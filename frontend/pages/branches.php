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
    <div class="p-6 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-slide-up">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Pharmacy Branches</h2>
                <p class="text-slate-500 font-medium">Manage and monitor all pharmacy locations.</p>
            </div>
            <button onclick="showBranchModal()" class="btn btn-primary shadow-lg shadow-blue-500/20">
                <i class="fas fa-plus"></i> Add New Branch
            </button>
        </div>

        <div class="card animate-slide-up" style="animation-delay: 0.1s;">
            <div class="table-container">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-4">Branch Name</th>
                            <th class="px-6 py-4">Physical Address</th>
                            <th class="px-6 py-4">Contact Number</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="branchesTable"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal for creating/editing branches -->
<div id="branchModal" class="modal-backdrop hidden z-50">
    <div class="modal-content !max-w-md">
        <div class="flex items-center justify-between p-6 border-b border-slate-100">
            <h3 class="text-xl font-bold text-slate-800" id="modalTitle">Add Branch</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fas fa-times-circle text-xl"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="branchId">
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Branch Name</label>
                <input type="text" id="branchName" placeholder="e.g. Main Street Pharmacy" class="!bg-slate-50">
            </div>
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Address</label>
                <textarea id="branchAddress" placeholder="Physical location details..." class="!bg-slate-50 min-h-[100px]"></textarea>
            </div>
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Phone Number</label>
                <input type="text" id="branchPhone" placeholder="+251..." class="!bg-slate-50">
            </div>
        </div>
        <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 rounded-b-lg">
            <button onclick="closeModal()" class="btn !bg-white border border-slate-200 text-slate-600 hover:bg-slate-100">Cancel</button>
            <button onclick="saveBranch()" class="btn btn-primary px-8">Save Branch</button>
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
                        <td class="px-6 py-3 whitespace-nowrap">
                            <button onclick="editBranch(${b.id}, '${escapeHtml(b.name)}', '${escapeHtml(b.address || '')}', '${escapeHtml(b.phone || '')}')" class="action-icon-btn action-edit mr-1" title="Edit branch" aria-label="Edit branch">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button onclick="deleteBranch(${b.id})" class="action-icon-btn action-delete" title="Delete branch" aria-label="Delete branch">
                                <i class="fas fa-trash"></i>
                            </button>
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
        const confirmed = await showConfirmDialog({
            title: 'Delete Branch',
            message: 'Deleting this branch may <strong>affect assigned users and drug records</strong>. This action is permanent and cannot be undone.',
            type: 'danger',
            confirmText: 'Yes, Delete Branch',
            cancelText: 'Cancel'
        });
        if (confirmed) {
            try {
                await API.deleteBranch(id);
                showToast('Branch deleted successfully');
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