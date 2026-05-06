// PharmaFlow System - Core Application Logic
let currentUser = null;

document.addEventListener('DOMContentLoaded', function () {
    loadNotificationsCount();
    // Refresh notifications count every 5 minutes
    setInterval(loadNotificationsCount, 300000);
});

// Load unread notifications count (for sidebar badge)
async function loadNotificationsCount() {
    try {
        const result = await API.getNotifications(true);
        const unreadCount = result.data ? result.data.length : 0;
        const sidebarBadge = document.getElementById('notifCount');
        const sidebarBottomBadge = document.getElementById('sidebarBottomNotifBadge');
        const navbarBadge = document.getElementById('navNotifBadge');

        const updateBadge = (badge) => {
            if (badge) {
                badge.innerText = unreadCount;
                if (unreadCount > 0) {
                    badge.classList.remove('hidden');
                    badge.classList.add('flex');
                } else {
                    badge.classList.add('hidden');
                    badge.classList.remove('flex');
                }
            }
        };

        updateBadge(sidebarBadge);
        updateBadge(sidebarBottomBadge);
        updateBadge(navbarBadge);
    } catch (error) {
        console.warn('Silent notification count failure:', error);
    }
}

// Global UI Helpers
function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function (m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}