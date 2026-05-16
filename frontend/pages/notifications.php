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
    <div class="p-6 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-slide-up">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Notifications</h2>
                <p class="text-slate-500 font-medium">Stay updated with inventory alerts and system activity.</p>
            </div>
            <button onclick="markAllRead()" class="text-blue-600 font-bold text-sm hover:text-blue-700 transition-colors">
                <i class="fas fa-check-double mr-1"></i> Mark All as Read
            </button>
        </div>

        <div class="card !p-0 overflow-hidden animate-slide-up" style="animation-delay: 0.1s;">
            <div class="divide-y divide-slate-100" id="notificationsList"></div>
        </div>
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
                    <div class="p-5 flex items-start justify-between gap-4 transition-colors ${!n.is_read ? 'bg-blue-50/50' : 'hover:bg-slate-50'}">
                        <div class="flex gap-4">
                            <div class="mt-1 w-10 h-10 rounded-full flex items-center justify-center ${n.type === 'low_stock' ? 'bg-amber-100 text-amber-600' : 'bg-red-100 text-red-600'}">
                                <i class="fas ${n.type === 'low_stock' ? 'fa-exclamation-triangle' : 'fa-clock'} text-sm"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-sm font-bold text-slate-800">${n.type === 'low_stock' ? 'Low Stock Alert' : (n.type === 'expiry' ? 'Expiry Warning' : n.type)}</span>
                                    ${!n.is_read ? '<span class="w-2 h-2 bg-blue-500 rounded-full"></span>' : ''}
                                </div>
                                <p class="text-sm text-slate-600 leading-relaxed">${escapeHtml(n.message)}</p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">${formatDateTime(n.created_at)}</p>
                            </div>
                        </div>
                        ${!n.is_read ? `
                            <button onclick="markRead(${n.id})" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-100 rounded-lg transition-all" title="Mark as read">
                                <i class="fas fa-check"></i>
                            </button>
                        ` : ''}
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