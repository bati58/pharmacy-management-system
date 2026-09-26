<!-- Top bar -->
<div class="main-content min-h-screen flex flex-col transition-all duration-300">
    <header class="relative h-16 md:h-20 bg-white/80 backdrop-blur-md sticky top-0 z-[210] border-b border-slate-200/60 px-4 md:px-8 flex justify-between items-center no-print">
        <div class="flex items-center gap-4">
            <button id="mobileMenuBtn" class="md:hidden w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 transition-all duration-200 focus:outline-none">
                <i class="fas fa-bars text-lg"></i>
            </button>
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">
                <?php
                $page = basename($_SERVER['PHP_SELF'], '.php');
                echo ucwords(str_replace(['-', '_'], ' ', $page));
                ?>
            </h1>
        </div>
        
        <div class="flex items-center gap-3 md:gap-6">
            <div class="relative flex items-center gap-4 border-r border-slate-200 pr-3 md:pr-6 md:mr-2">
                <button type="button" id="notificationToggle" aria-expanded="false" aria-controls="notificationPanel" title="Notifications" class="relative w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 transition-all duration-200">
                    <i class="fas fa-bell"></i>
                    <span id="headerNotifCount" class="absolute top-1.5 right-1.5 min-w-[1rem] h-4 px-1 bg-rose-500 text-white text-[9px] font-bold rounded-full border-[1.5px] border-white flex items-center justify-center hidden">0</span>
                </button>
                <div id="notificationPanel" class="header-dropdown hidden absolute right-0 top-12 w-[min(22rem,calc(100vw-2rem))] bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-sm font-black text-slate-800">Notifications</h3>
                            <p class="text-[10px] text-slate-400 font-medium">Recent system updates</p>
                        </div>
                        <button type="button" id="markHeaderNotificationsRead" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 disabled:text-slate-300" disabled>Mark all read</button>
                    </div>
                    <div id="headerNotificationsList" class="max-h-[min(24rem,60vh)] overflow-y-auto">
                        <p class="px-4 py-8 text-center text-xs font-medium text-slate-400">Loading notifications...</p>
                    </div>
                    <a href="notifications.php" class="block px-4 py-3 bg-slate-50 text-center text-xs font-bold text-indigo-600 hover:bg-indigo-50">View notification history</a>
                </div>
            </div>
            
            <div class="relative">
                <button type="button" id="profileToggle" aria-expanded="false" aria-controls="profilePanel" title="Profile menu" class="flex items-center gap-2 md:gap-3 rounded-xl p-1.5 hover:bg-slate-100 transition-colors">
                    <span class="text-right hidden sm:block">
                        <span class="block text-xs font-bold text-slate-800 leading-none"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></span>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-tighter mt-1"><?php echo ucfirst(str_replace('_', ' ', $_SESSION['role'] ?? 'Guest')); ?></span>
                    </span>
                    <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg shadow-indigo-200 flex items-center justify-center text-white font-bold text-sm">
                        <?php echo strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)); ?>
                    </span>
                    <i class="fas fa-chevron-down text-[10px] text-slate-400 hidden sm:block"></i>
                </button>
                <div id="profilePanel" class="header-dropdown hidden absolute right-0 top-14 w-52 bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden">
                    <a href="settings.php" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-indigo-50 hover:text-indigo-600">
                        <i class="fas fa-user-cog w-4 text-center"></i> Settings
                    </a>
                    <a href="../../backend/index.php/auth/logout" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-rose-600 hover:bg-rose-50 border-t border-slate-100">
                        <i class="fas fa-power-off w-4 text-center"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </header>
    <div class="p-4 md:p-8 flex-1">

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const notificationToggle = document.getElementById('notificationToggle');
        const notificationPanel = document.getElementById('notificationPanel');
        const notificationList = document.getElementById('headerNotificationsList');
        const markAllButton = document.getElementById('markHeaderNotificationsRead');
        const profileToggle = document.getElementById('profileToggle');
        const profilePanel = document.getElementById('profilePanel');

        function closeDropdowns() {
            notificationPanel?.classList.add('hidden');
            profilePanel?.classList.add('hidden');
            notificationToggle?.setAttribute('aria-expanded', 'false');
            profileToggle?.setAttribute('aria-expanded', 'false');
        }

        notificationToggle?.addEventListener('click', async function () {
            const opening = notificationPanel.classList.contains('hidden');
            closeDropdowns();
            if (opening) {
                notificationPanel.classList.remove('hidden');
                notificationToggle.setAttribute('aria-expanded', 'true');
                await loadHeaderNotifications();
            }
        });

        profileToggle?.addEventListener('click', function () {
            const opening = profilePanel.classList.contains('hidden');
            closeDropdowns();
            if (opening) {
                profilePanel.classList.remove('hidden');
                profileToggle.setAttribute('aria-expanded', 'true');
            }
        });

        document.addEventListener('click', function (event) {
            if (!event.target.closest('#notificationToggle, #notificationPanel, #profileToggle, #profilePanel')) closeDropdowns();
        });

        async function loadHeaderNotifications() {
            if (typeof API === 'undefined') return;
            try {
                const response = await API.getNotifications();
                const notifications = response.data || [];
                const unread = notifications.filter(notification => !notification.is_read).length;
                markAllButton.disabled = unread === 0;
                notificationList.innerHTML = notifications.length ? notifications.slice(0, 8).map(renderHeaderNotification).join('') : '<p class="px-4 py-8 text-center text-xs font-medium text-slate-400">You are all caught up.</p>';
            } catch (error) {
                notificationList.innerHTML = '<p class="px-4 py-8 text-center text-xs font-medium text-rose-500">Notifications are unavailable.</p>';
            }
        }

        function renderHeaderNotification(notification) {
            return `<div class="flex items-start gap-3 px-4 py-3 border-b border-slate-100 ${notification.is_read ? '' : 'bg-indigo-50/50'}" id="header-notification-${notification.id}">
                <i class="fas fa-bell mt-1 text-indigo-500 text-xs"></i>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-slate-700 leading-relaxed">${escapeHtml(notification.message)}</p>
                    <p class="text-[10px] text-slate-400 mt-1">${formatDateTime(notification.created_at)}</p>
                    <div class="flex items-center gap-3 mt-2">
                        ${!notification.is_read ? `<button type="button" onclick="markHeaderNotificationRead(${notification.id})" class="text-[10px] font-bold text-emerald-600 hover:text-emerald-800">Mark read</button>` : '<span class="text-[10px] font-bold text-slate-400">Read</span>'}
                        <button type="button" onclick="removeHeaderNotification(${notification.id})" class="text-[10px] font-bold text-rose-500 hover:text-rose-700">Remove</button>
                    </div>
                </div>
            </div>`;
        }

        window.markHeaderNotificationRead = async function (id) {
            await API.markNotificationRead(id);
            await loadHeaderNotifications();
            updateNotificationBadge();
        };

        window.removeHeaderNotification = async function (id) {
            await API.deleteNotification(id);
            await loadHeaderNotifications();
            updateNotificationBadge();
        };

        markAllButton?.addEventListener('click', async function () {
            await API.markAllRead();
            await loadHeaderNotifications();
            updateNotificationBadge();
        });

        window.loadHeaderNotifications = loadHeaderNotifications;
    });
</script>