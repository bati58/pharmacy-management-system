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
        <div class="page-toolbar flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold">User Management</h2>
            <button onclick="showInviteModal()" class="bg-blue-600 text-white px-4 py-2 rounded">+ Invite User</button>
        </div>
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">User</th>
                        <th class="px-6 py-3 text-left">Role</th>
                        <th class="px-6 py-3 text-left">Branch</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Joined</th>
                        <th class="px-6 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTable"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Invite Modal (no password) -->
<div id="inviteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-96">
        <h3 class="text-lg font-bold mb-4">Invite User</h3>
        <input type="text" id="inviteName" placeholder="Full Name" class="w-full border rounded px-3 py-2 mb-3" required>
        <input type="email" id="inviteEmail" placeholder="Email" class="w-full border rounded px-3 py-2 mb-3" required>
        <select id="inviteRole" class="w-full border rounded px-3 py-2 mb-3">
            <option value="pharmacist">Pharmacist</option>
            <option value="store_keeper">Store Keeper</option>
            <option value="manager">Manager</option>
        </select>
        <select id="inviteBranch" class="w-full border rounded px-3 py-2 mb-3"></select>
        <div class="flex justify-end space-x-2">
            <button onclick="closeInviteModal()" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
            <button onclick="sendInvite()" class="px-4 py-2 bg-blue-600 text-white rounded">Send Invite</button>
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
                            <td class="px-6 py-3">
                                <button onclick="editUser(${u.id}, '${escapeHtml(u.name)}', '${escapeHtml(u.email)}', '${u.role}', ${u.branch_id || 'null'}, '${u.status}')" class="text-blue-600 hover:underline mr-2">Edit</button>
                                ${u.status === 'active' ? `<button onclick="toggleUserStatus(${u.id}, 'inactive')" class="text-orange-600 hover:underline mr-2">Deactivate</button>` : `<button onclick="toggleUserStatus(${u.id}, 'active')" class="text-green-600 hover:underline mr-2">Activate</button>`}
                                <button onclick="deleteUser(${u.id})" class="text-red-600 hover:underline">Delete</button>
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
            await API.inviteUser({
                name,
                email,
                role,
                branch_id: branchId
            });
            showToast('Invitation sent! The user will receive an email.');
            closeInviteModal();
            // Optionally reload user list – but user not yet created, so maybe not needed.
        } catch (err) {
            showToast(err.message, 'error');
        }
    }

    // Edit user (still uses the old direct update, password optional)
    function editUser(id, name, email, role, branchId, status) {
        // For simplicity, we can open a separate edit modal or reuse the invite modal with changes.
        // I'll keep a simple prompt for now; you can expand later.
        const newName = prompt('Edit name:', name);
        if (newName) {
            API.updateUser(id, {
                name: newName,
                role,
                branch_id: branchId,
                status
            }).then(() => {
                showToast('User updated');
                loadUsers();
            }).catch(err => showToast(err.message, 'error'));
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
        if (confirm('Permanently delete this user?')) {
            try {
                await API.deleteUser(id);
                showToast('User deleted');
                loadUsers();
            } catch (err) {
                showToast(err.message, 'error');
            }
        }
    }

    loadUsers();
</script>
<?php include '../includes/footer.php'; ?>