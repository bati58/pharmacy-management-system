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
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">User Management</h2>
            <p class="text-slate-500 mt-1 font-medium">Control access levels and manage team members across all branches.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="showInviteModal()" class="btn-premium btn-premium-primary shadow-indigo-200">
                <i class="fas fa-user-plus"></i> Invite New User
            </button>
        </div>
    </div>

    <!-- User Table -->
    <div class="card overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-users text-indigo-500"></i> Active Personnel
            </h3>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-400">
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Active</span>
                <span class="flex items-center gap-1 ml-3"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Inactive</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <th>User Profile</th>
                        <th>System Role</th>
                        <th>Assigned Branch</th>
                        <th>Account Status</th>
                        <th>Joined Date</th>
                        <th class="text-right">Management</th>
                    </tr>
                </thead>
                <tbody id="usersTable">
                    <!-- Data will be injected here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Invite Modal -->
<div id="inviteModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-[100] p-4">
    <div class="bg-white/95 backdrop-blur shadow-2xl rounded-3xl p-8 w-full max-w-md animate-fade-in border border-white/20">
        <div class="flex items-center justify-between mb-8">
            <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">Invite Personnel</h3>
            <button onclick="closeInviteModal()" class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Full Name</label>
                <input type="text" id="inviteName" placeholder="e.g. Dr. John Doe" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Email Address</label>
                <input type="email" id="inviteEmail" placeholder="e.g. john@batiflow.com" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">System Role</label>
                <select id="inviteRole" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium text-slate-600">
                    <option value="pharmacist">Pharmacist</option>
                    <option value="store_keeper">Store Keeper</option>
                    <option value="manager">Manager</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Assign Branch</label>
                <select id="inviteBranch" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium text-slate-600"></select>
            </div>
        </div>
        
        <div class="mt-8 flex gap-3">
            <button onclick="closeInviteModal()" class="flex-1 px-6 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition-all">Cancel</button>
            <button onclick="sendInvite()" class="flex-1 px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all">Send Invitation</button>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-[100] p-4">
    <div class="bg-white/95 backdrop-blur shadow-2xl rounded-3xl p-8 w-full max-w-md animate-fade-in border border-white/20">
        <div class="flex items-center justify-between mb-8">
            <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">Update Personnel</h3>
            <button onclick="closeEditModal()" class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <input type="hidden" id="editUserId">
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Full Name</label>
                <input type="text" id="editName" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">System Role</label>
                <select id="editRole" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium text-slate-600">
                    <option value="pharmacist">Pharmacist</option>
                    <option value="store_keeper">Store Keeper</option>
                    <option value="manager">Manager</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Assign Branch</label>
                <select id="editBranch" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium text-slate-600"></select>
            </div>
        </div>
        
        <div class="mt-8 flex gap-3">
            <button onclick="closeEditModal()" class="flex-1 px-6 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition-all">Cancel</button>
            <button onclick="saveUserEdit()" class="flex-1 px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all">Save Changes</button>
        </div>
    </div>
</div>

<script src="../assets/js/utils.js"></script>
<script src="../assets/js/api.js"></script>
<script>
    async function loadUsers() {
        try {
            const users = await API.getUsers();
            const branches = await API.getBranches();
            const branchMap = {};
            if (branches.data) branches.data.forEach(b => branchMap[b.id] = b.name);
            const tbody = document.getElementById('usersTable');
            tbody.innerHTML = '';
            if (users.data && users.data.length) {
                users.data.forEach(u => {
                    const statusClass = u.status === 'active' ? 'active' : 'inactive';
                    const initials = u.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                    tbody.innerHTML += `
                        <tr class="group hover:bg-slate-50 transition-colors">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs border border-indigo-100">
                                        ${initials}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800">${escapeHtml(u.name)}</p>
                                        <p class="text-xs text-slate-500 font-medium">${escapeHtml(u.email)}</p>
                                    </div>
                                </div>
                            </td>
                            <td><span class="text-xs font-bold text-slate-600 bg-slate-100 px-2 py-1 rounded-lg uppercase tracking-tight">${u.role.replace('_', ' ')}</span></td>
                            <td><span class="text-sm text-slate-600 font-medium">${branchMap[u.branch_id] || '<span class="text-slate-300">Not Assigned</span>'}</span></td>
                            <td><span class="status-pill ${statusClass}">${u.status}</span></td>
                            <td class="text-xs text-slate-500 font-medium">${formatDate(u.created_at)}</td>
                            <td class="text-right">
                                <div class="flex justify-end gap-2">
                                    <button onclick="openEditModal(${u.id}, '${escapeHtml(u.name)}', '${u.role}', ${u.branch_id || 'null'})" 
                                        class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 transition-all flex items-center justify-center" title="Edit">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    ${u.status === 'active' ? 
                                        `<button onclick="toggleUserStatus(${u.id}, 'inactive')" class="w-8 h-8 rounded-lg bg-slate-100 text-amber-500 hover:bg-amber-50 hover:text-amber-600 transition-all flex items-center justify-center" title="Deactivate"><i class="fas fa-user-minus text-xs"></i></button>` : 
                                        `<button onclick="toggleUserStatus(${u.id}, 'active')" class="w-8 h-8 rounded-lg bg-slate-100 text-emerald-500 hover:bg-emerald-50 hover:text-emerald-600 transition-all flex items-center justify-center" title="Activate"><i class="fas fa-user-plus text-xs"></i></button>`
                                    }
                                    <button onclick="deleteUser(${u.id})" class="w-8 h-8 rounded-lg bg-slate-100 text-rose-500 hover:bg-rose-50 hover:text-rose-600 transition-all flex items-center justify-center" title="Delete">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-slate-400 font-medium">No team members found</td></tr>';
            }
        } catch (err) {
            console.error(err);
            showToast('Failed to load users', 'error');
        }
    }

    async function loadBranchesForSelect(selectId) {
        try {
            const res = await API.getBranches();
            const select = document.getElementById(selectId);
            select.innerHTML = '<option value="">No Branch</option>';
            if (res.data) {
                res.data.forEach(b => {
                    select.innerHTML += `<option value="${b.id}">${escapeHtml(b.name)}</option>`;
                });
            }
        } catch (err) {
            console.error(err);
        }
    }

    function showInviteModal() {
        document.getElementById('inviteName').value = '';
        document.getElementById('inviteEmail').value = '';
        document.getElementById('inviteRole').value = 'pharmacist';
        document.getElementById('inviteModal').classList.remove('hidden');
        document.getElementById('inviteModal').classList.add('flex');
        loadBranchesForSelect('inviteBranch');
    }

    function closeInviteModal() {
        document.getElementById('inviteModal').classList.add('hidden');
        document.getElementById('inviteModal').classList.remove('flex');
    }

    async function sendInvite() {
        const name = document.getElementById('inviteName').value.trim();
        const email = document.getElementById('inviteEmail').value.trim();
        const role = document.getElementById('inviteRole').value;
        const branchId = document.getElementById('inviteBranch').value || null;

        if (!name || !email) {
            showToast('Name and email are required', 'error');
            return;
        }
        
        const btn = document.querySelector('#inviteModal button[onclick="sendInvite()"]');
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = 'Sending...';

        try {
            const res = await API.inviteUser({
                name,
                email,
                role,
                branch_id: branchId
            });
            
            if (res.data && res.data.link) {
                closeInviteModal();
                await showConfirm('Invitation Link', 
                    `Email could not be sent automatically, but the invitation was created.\n\nPlease copy this link and send it manually:\n\n${res.data.link}`,
                    'Copy Link', 'Close');
                navigator.clipboard.writeText(res.data.link);
                showToast('Link copied to clipboard');
            } else {
                showToast('Invitation sent! The user will receive an email.');
                closeInviteModal();
            }
        } catch (err) {
            showToast(err.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = originalText;
        }
    }

    async function openEditModal(id, name, role, branchId) {
        document.getElementById('editUserId').value = id;
        document.getElementById('editName').value = name;
        document.getElementById('editRole').value = role;
        await loadBranchesForSelect('editBranch');
        document.getElementById('editBranch').value = branchId || '';
        document.getElementById('editUserModal').classList.remove('hidden');
        document.getElementById('editUserModal').classList.add('flex');
    }

    function closeEditModal() {
        document.getElementById('editUserModal').classList.add('hidden');
        document.getElementById('editUserModal').classList.remove('flex');
    }

    async function saveUserEdit() {
        const id = document.getElementById('editUserId').value;
        const name = document.getElementById('editName').value.trim();
        const role = document.getElementById('editRole').value;
        const branchId = document.getElementById('editBranch').value || null;

        if (!name) {
            showToast('Name is required', 'error');
            return;
        }

        try {
            await API.updateUser(id, { name, role, branch_id: branchId });
            showToast('User profile updated');
            closeEditModal();
            loadUsers();
        } catch (err) {
            showToast(err.message, 'error');
        }
    }

    async function toggleUserStatus(id, newStatus) {
        const confirmed = await showConfirm(`User ${newStatus === 'active' ? 'Activation' : 'Deactivation'}`, `Are you sure you want to change this user's status to ${newStatus}?`);
        if (!confirmed) return;

        try {
            if (newStatus === 'active') await API.activateUser(id);
            else await API.deactivateUser(id);
            showToast(`User ${newStatus}d`);
            loadUsers();
        } catch (err) {
            showToast(err.message, 'error');
        }
    }

    async function deleteUser(id) {
        const confirmed = await showConfirm('Permanent Deletion', 'Are you sure you want to permanently delete this user? This action cannot be undone and may affect historical records.');
        if (confirmed) {
            try {
                await API.deleteUser(id);
                showToast('User deleted successfully');
                loadUsers();
            } catch (err) {
                showToast(err.message, 'error');
            }
        }
    }

    loadUsers();
</script>
<?php include '../includes/footer.php'; ?>