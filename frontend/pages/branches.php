<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
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
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Pharmacy Branches</h2>
            <p class="text-slate-500 mt-1 font-medium">Manage your pharmaceutical network and distribution points.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="showBranchModal()" class="btn-premium btn-premium-primary shadow-indigo-200">
                <i class="fas fa-plus"></i> Add New Branch
            </button>
        </div>
    </div>

    <!-- Branches Table -->
    <div class="card overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-store text-indigo-500"></i> Active Locations
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <th>Branch Name</th>
                        <th>Location / Address</th>
                        <th>Contact Number</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="branchesTable">
                    <!-- Data will be injected here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Branch Modal -->
<div id="branchModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-[100] p-4">
    <div class="bg-white/95 backdrop-blur shadow-2xl rounded-3xl p-8 w-full max-w-md animate-fade-in border border-white/20">
        <div class="flex items-center justify-between mb-8">
            <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight" id="modalTitle">Add Branch</h3>
            <button onclick="closeModal()" class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <input type="hidden" id="branchId">
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Branch Name</label>
                <input type="text" id="branchName" placeholder="e.g. Westside Pharmacy" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Address</label>
                <textarea id="branchAddress" placeholder="e.g. 123 Oak Street, Westside" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium h-24 resize-none"></textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Contact Phone</label>
                <input type="text" id="branchPhone" placeholder="e.g. +1-555-0102" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium">
            </div>
        </div>
        
        <div class="mt-8 flex gap-3">
            <button onclick="closeModal()" class="flex-1 px-6 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition-all">Cancel</button>
            <button onclick="saveBranch()" class="flex-1 px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all">Save Branch</button>
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
                    <tr class="group hover:bg-slate-50 transition-colors">
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs border border-indigo-100">
                                    <i class="fas fa-store"></i>
                                </div>
                                <span class="font-bold text-slate-800">${escapeHtml(b.name)}</span>
                            </div>
                        </td>
                        <td class="text-slate-600 font-medium">${escapeHtml(b.address || '-')}</td>
                        <td><span class="text-xs font-bold text-slate-500 bg-slate-100 px-2 py-1 rounded-lg uppercase tracking-tight">${escapeHtml(b.phone || '-')}</span></td>
                        <td class="text-right">
                            <div class="flex justify-end gap-2">
                                <button onclick="editBranch(${b.id}, '${escapeHtml(b.name)}', '${escapeHtml(b.address || '')}', '${escapeHtml(b.phone || '')}')" 
                                    class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 transition-all flex items-center justify-center" title="Edit">
                                    <i class="fas fa-edit text-xs"></i>
                                </button>
                                <button onclick="deleteBranch(${b.id})" 
                                    class="w-8 h-8 rounded-lg bg-slate-100 text-rose-500 hover:bg-rose-50 hover:text-rose-600 transition-all flex items-center justify-center" title="Delete">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </div>
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
        const confirmed = await showConfirm('Delete Branch', 'Are you sure you want to delete this branch? This action may affect associated users and medication stock records.');
        if (confirmed) {
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