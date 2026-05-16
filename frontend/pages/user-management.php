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
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">User Management</h2>
                <p class="text-slate-500 font-medium">Control access and manage pharmacy staff across branches.</p>
            </div>
            <button onclick="showInviteModal()" class="btn btn-primary shadow-lg shadow-blue-500/20">
                <i class="fas fa-user-plus"></i> Invite New User
            </button>
        </div>

        <div class="card animate-slide-up" style="animation-delay: 0.1s;">
            <div class="table-container">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-4">Staff Member</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Assigned Branch</th>
                            <th class="px-6 py-4">Account Status</th>
                            <th class="px-6 py-4">Join Date</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTable"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Invite Modal -->
<div id="inviteModal" class="modal-backdrop hidden z-50">
    <div class="modal-content !max-w-md">
        <div class="flex items-center justify-between p-6 border-b border-slate-100">
            <h3 class="text-xl font-bold text-slate-800">Invite Team Member</h3>
            <button onclick="closeInviteModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fas fa-times-circle text-xl"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Full Name</label>
                <input type="text" id="inviteName" placeholder="e.g. John Doe" class="!bg-slate-50" required>
            </div>
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Email Address</label>
                <input type="email" id="inviteEmail" placeholder="john@example.com" class="!bg-slate-50" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">System Role</label>
                    <select id="inviteRole" class="!bg-slate-50">
                        <option value="pharmacist">Pharmacist</option>
                        <option value="store_keeper">Store Keeper</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Primary Branch</label>
                    <select id="inviteBranch" class="!bg-slate-50"></select>
                </div>
            </div>
        </div>
        <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 rounded-b-lg">
            <button onclick="closeInviteModal()" class="btn !bg-white border border-slate-200 text-slate-600 hover:bg-slate-100">Cancel</button>
            <button onclick="sendInvite()" class="btn btn-primary px-8">Send Invitation Link</button>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal-backdrop hidden z-50">
    <div class="modal-content !max-w-md">
        <div class="flex items-center justify-between p-6 border-b border-slate-100">
            <h3 class="text-xl font-bold text-slate-800">Edit Staff Member</h3>
            <button onclick="closeEditUserModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fas fa-times-circle text-xl"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="editUserId">
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Full Name</label>
                <input type="text" id="editUserName" placeholder="Full name" class="!bg-slate-50" required>
            </div>
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Email Address</label>
                <input type="email" id="editUserEmail" placeholder="Email" class="!bg-slate-50" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">System Role</label>
                    <select id="editUserRole" class="!bg-slate-50">
                        <option value="pharmacist">Pharmacist</option>
                        <option value="store_keeper">Store Keeper</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Branch</label>
                    <select id="editUserBranch" class="!bg-slate-50"></select>
                </div>
            </div>
        </div>
        <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 rounded-b-lg">
            <button onclick="closeEditUserModal()" class="btn !bg-white border border-slate-200 text-slate-600 hover:bg-slate-100">Cancel</button>
            <button onclick="saveEditUser()" class="btn btn-primary px-8">Save Changes</button>
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
                    tbody.innerHTML += `
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-3">${escapeHtml(u.name)}<br><span class="text-xs text-gray-500">${escapeHtml(u.email)}</span></td>
                            <td class="px-6 py-3">${u.role}</td>
                            <td class="px-6 py-3">${branchMap[u.branch_id] || '-'}</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs ${u.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${u.status}</span></td>
                            <td class="px-6 py-3">${formatDate(u.created_at)}</td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                <button onclick="editUser(${u.id}, '${escapeHtml(u.name)}', '${escapeHtml(u.email)}', '${u.role}', ${u.branch_id || 'null'}, '${u.status}')" class="action-icon-btn action-edit mr-1" title="Edit user" aria-label="Edit user">
                                    <i class="fas fa-pen"></i>
                                </button>
                                ${u.status === 'active'
                                    ? `<button onclick="toggleUserStatus(${u.id}, 'inactive')" class="action-icon-btn action-deactivate mr-1" title="Deactivate user" aria-label="Deactivate user"><i class="fas fa-user-slash"></i></button>`
                                    : (u.status === 'inactive'
                                        ? `<button onclick="toggleUserStatus(${u.id}, 'active')" class="action-icon-btn action-activate mr-1" title="Activate user" aria-label="Activate user"><i class="fas fa-user-check"></i></button>`
                                        : `<span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded font-bold mr-1" title="Awaiting user to set password">Pending</span>`
                                    )
                                }
                                <button onclick="deleteUser(${u.id})" class="action-icon-btn action-delete" title="Delete user" aria-label="Delete user">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = '<td><td colspan="6" class="text-center py-4">No users found</td><tr>';
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
        try {
            const response = await API.inviteUser({
                name,
                email,
                role,
                branch_id: branchId
            });
            showToast(response.message || 'Invitation sent successfully!');
            closeInviteModal();
            loadUsers();
        } catch (err) {
            showToast(err.message, 'error');
        }
    }

    // Edit user - open professional modal
    function editUser(id, name, email, role, branchId, status) {
        document.getElementById('editUserId').value = id;
        document.getElementById('editUserName').value = name;
        document.getElementById('editUserEmail').value = email;
        document.getElementById('editUserRole').value = role;
        loadBranchesForSelect('editUserBranch').then(() => {
            if (branchId) document.getElementById('editUserBranch').value = branchId;
        });
        document.getElementById('editUserModal').classList.remove('hidden');
        document.getElementById('editUserModal').classList.add('flex');
    }

    function closeEditUserModal() {
        document.getElementById('editUserModal').classList.add('hidden');
        document.getElementById('editUserModal').classList.remove('flex');
    }

    async function saveEditUser() {
        const id = document.getElementById('editUserId').value;
        const name = document.getElementById('editUserName').value.trim();
        const email = document.getElementById('editUserEmail').value.trim();
        const role = document.getElementById('editUserRole').value;
        const branchId = document.getElementById('editUserBranch').value || null;
        if (!name || !email) {
            showToast('Name and email are required', 'error');
            return;
        }
        try {
            await API.updateUser(id, { name, email, role, branch_id: branchId });
            showToast('User updated successfully');
            closeEditUserModal();
            loadUsers();
        } catch (err) {
            showToast(err.message, 'error');
        }
    }

    async function toggleUserStatus(id, newStatus) {
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
        const confirmed = await showConfirmDialog({
            title: 'Delete User Account',
            message: 'This will <strong>permanently remove</strong> this user and all associated data. This action cannot be undone.',
            type: 'danger',
            confirmText: 'Yes, Delete',
            cancelText: 'Cancel'
        });
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