<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<?php include '../includes/navbar.php'; ?>

<div class="animate-fade-in">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Notifications</h2>
            <p class="text-slate-500 mt-1 font-medium">Stay updated on important system events.</p>
        </div>
        <button onclick="markAllRead()"
            id="markAllBtn"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-200 transition-all duration-200 hover:-translate-y-0.5 self-start sm:self-auto">
            <i class="fas fa-check-double"></i>
            Mark all as read
        </button>
    </div>

    <!-- Notifications Container -->
    <div class="card overflow-hidden" id="notificationsCard">
        <!-- Loading State -->
        <div id="loadingState" class="flex flex-col items-center justify-center py-16 text-slate-400">
            <i class="fas fa-circle-notch fa-spin text-3xl text-indigo-400 mb-4"></i>
            <p class="font-medium">Loading notifications...</p>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden flex-col items-center justify-center py-16 text-slate-400">
            <div class="w-20 h-20 rounded-2xl bg-slate-50 flex items-center justify-center mb-4">
                <i class="fas fa-bell-slash text-3xl text-slate-300"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-600 mb-1">All caught up!</h3>
            <p class="text-sm font-medium">You have no notifications right now.</p>
        </div>

        <!-- Notifications List -->
        <div id="notificationsList" class="hidden divide-y divide-slate-100"></div>
    </div>
</div>

<script src="../assets/js/utils.js"></script>
<script src="../assets/js/api.js"></script>
<script>
    const typeIconMap = {
        system: { icon: 'fa-cog', bg: 'bg-slate-100', color: 'text-slate-500' },
        alert:  { icon: 'fa-exclamation-triangle', bg: 'bg-rose-50', color: 'text-rose-500' },
        info:   { icon: 'fa-info-circle', bg: 'bg-blue-50', color: 'text-blue-500' },
        stock:  { icon: 'fa-boxes', bg: 'bg-amber-50', color: 'text-amber-500' },
    };

    function getTypeStyle(type) {
        const t = (type || 'system').toLowerCase();
        return typeIconMap[t] || typeIconMap.system;
    }

    async function loadNotifications() {
        const loadingEl  = document.getElementById('loadingState');
        const emptyEl    = document.getElementById('emptyState');
        const listEl     = document.getElementById('notificationsList');
        const markAllBtn = document.getElementById('markAllBtn');

        loadingEl.classList.remove('hidden');
        loadingEl.classList.add('flex');
        emptyEl.classList.add('hidden');
        listEl.classList.add('hidden');

        try {
            const res = await API.getNotifications();

            loadingEl.classList.add('hidden');
            loadingEl.classList.remove('flex');

            if (res.data && res.data.length) {
                const hasUnread = res.data.some(n => !n.is_read);
                markAllBtn.classList.toggle('opacity-50', !hasUnread);
                markAllBtn.disabled = !hasUnread;

                listEl.innerHTML = '';
                res.data.forEach(n => {
                    const style = getTypeStyle(n.type);
                    listEl.innerHTML += `
                        <div class="flex items-start gap-4 px-6 py-5 transition-colors duration-150 ${!n.is_read ? 'bg-indigo-50/50 hover:bg-indigo-50' : 'hover:bg-slate-50'}" id="notif-${n.id}">
                            <!-- Icon -->
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl ${style.bg} flex items-center justify-center mt-0.5">
                                <i class="fas ${style.icon} ${style.color}"></i>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="text-xs font-black uppercase tracking-widest ${style.color}">${escapeHtml(n.type || 'System')}</span>
                                    ${!n.is_read ? '<span class="inline-block w-2 h-2 rounded-full bg-indigo-500"></span>' : ''}
                                </div>
                                <p class="text-sm text-slate-700 font-medium leading-relaxed">${escapeHtml(n.message)}</p>
                                <p class="text-xs text-slate-400 mt-1.5 flex items-center gap-1.5">
                                    <i class="far fa-clock"></i>
                                    ${formatDateTime(n.created_at)}
                                </p>
                            </div>

                            <!-- Action -->
                            <div class="flex-shrink-0 self-center">
                                ${!n.is_read
                                    ? `<button onclick="markRead(${n.id})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-emerald-200 text-emerald-600 hover:bg-emerald-500 hover:text-white hover:border-emerald-500 text-xs font-bold rounded-lg transition-all duration-200">
                                        <i class="fas fa-check text-[10px]"></i> Mark read
                                       </button>`
                                    : `<span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 text-slate-400 text-xs font-bold rounded-lg">
                                        <i class="fas fa-check-double text-[10px]"></i> Read
                                       </span>`
                                }
                            </div>
                        </div>
                    `;
                });

                listEl.classList.remove('hidden');
            } else {
                emptyEl.classList.remove('hidden');
                emptyEl.classList.add('flex');
                markAllBtn.disabled = true;
                markAllBtn.classList.add('opacity-50');
            }
        } catch (err) {
            loadingEl.classList.add('hidden');
            loadingEl.classList.remove('flex');
            emptyEl.classList.remove('hidden');
            emptyEl.classList.add('flex');
        }
    }

    async function markRead(id) {
        const btn = document.querySelector(`#notif-${id} button`);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin text-[10px]"></i>';
        }
        await API.markNotificationRead(id);
        loadNotifications();
    }

    async function markAllRead() {
        const btn = document.getElementById('markAllBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Marking...';
        await API.markAllRead();
        loadNotifications();
        btn.innerHTML = '<i class="fas fa-check-double"></i> Mark all as read';
    }

    loadNotifications();
</script>

<?php include '../includes/footer.php'; ?>