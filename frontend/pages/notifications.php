<?php
require_once __DIR__ . '/../includes/init_session.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="ml-64 flex-1">
    <?php include '../includes/navbar.php'; ?>
    <div class="p-6">
        <div class="page-toolbar flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold">Notifications</h2>
            <button onclick="markAllRead()" class="text-blue-600">Mark all as read</button>
        </div>
        <div class="bg-white rounded-lg shadow divide-y" id="notificationsList"></div>
    </div>
</div>
<script src="../assets/js/utils.js"></script>
<script src="../assets/js/api.js"></script>
<script>
    async function loadNotifications() {
        const res = await API.getNotifications();
        const container = document.getElementById('notificationsList');
        container.innerHTML = '';
        if (res.data && res.data.length) {
            res.data.forEach(n => {
                container.innerHTML += `
                    <div class="p-4 ${!n.is_read ? 'bg-blue-50' : ''}">
                        <div class="flex justify-between">
                            <div>
                                <span class="font-semibold">${n.type}</span>
                                <p class="text-gray-700">${escapeHtml(n.message)}</p>
                                <small class="text-gray-500">${formatDateTime(n.created_at)}</small>
                            </div>
                            ${!n.is_read ? `<button onclick="markRead(${n.id})" class="action-icon-btn action-activate" title="Mark as read" aria-label="Mark as read"><i class="fas fa-check"></i></button>` : ''}
                        </div>
                    </div>
                `;
            });
        } else {
            container.innerHTML = '<div class="p-4 text-gray-500">No notifications</div>';
        }
    }
    async function markRead(id) {
        await API.markNotificationRead(id);
        loadNotifications();
    }
    async function markAllRead() {
        await API.markAllRead();
        loadNotifications();
    }
    loadNotifications();
</script>
<?php include '../includes/footer.php'; ?>