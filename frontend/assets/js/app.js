// Global app state
let currentUser = null;

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    loadUserInfo();
    setupSidebar();
    setupLogout();
    highlightCurrentPage();
    loadNotificationsCount();
});

// Load user info from session (via API or stored)
async function loadUserInfo() {
    try {
        // Try to get current user info from a protected endpoint (e.g., GET /auth/me)
        // For simplicity, we'll rely on session; we can add a /auth/me endpoint.
        // Alternatively, we can store user data after login in localStorage.
        const storedUser = localStorage.getItem('user');
        if (storedUser) {
            currentUser = JSON.parse(storedUser);
            document.getElementById('user-name').innerText = currentUser.name;
            document.getElementById('user-role').innerText = currentUser.role;
        } else {
            // If on a page that requires auth, redirect to login
            const isAuthPage = window.location.pathname.includes('/auth/');
            if (!isAuthPage) {
                window.location.href = '/pharmacy-management-system/frontend/pages/auth/login.html';
            }
        }
    } catch (error) {
        console.error('Failed to load user info:', error);
    }
}

// Setup sidebar based on user role
function setupSidebar() {
    if (!currentUser) return;

    const role = currentUser.role;
    // Hide menu items based on role
    const menuItems = {
        'branches': ['manager'],
        'user-management': ['manager'],
        'reports': ['manager'],
        'stock-transfers': ['manager', 'store_keeper'],
        'drug-inventory': ['manager', 'store_keeper', 'pharmacist'],
        'sales': ['manager', 'pharmacist']
    };

    for (const [itemId, allowedRoles] of Object.entries(menuItems)) {
        const element = document.getElementById(`menu-${itemId}`);
        if (element) {
            if (!allowedRoles.includes(role)) {
                element.style.display = 'none';
            }
        }
    }
}

// Logout handler
function setupLogout() {
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                await API.logout();
                localStorage.removeItem('user');
                window.location.href = '/pharmacy-management-system/frontend/pages/auth/login.html';
            } catch (error) {
                console.error('Logout failed:', error);
                // Force redirect anyway
                window.location.href = '/pharmacy-management-system/frontend/pages/auth/login.html';
            }
        });
    }
}

// Highlight current page in sidebar
function highlightCurrentPage() {
    const currentPath = window.location.pathname;
    const links = document.querySelectorAll('.sidebar-nav a');
    links.forEach(link => {
        if (link.getAttribute('href') === currentPath.split('/').pop()) {
            link.classList.add('active');
        }
    });
}

// Load unread notifications count (for badge)
async function loadNotificationsCount() {
    try {
        const result = await API.getNotifications(true);
        const unreadCount = result.data ? result.data.length : 0;
        const badge = document.getElementById('notifications-badge');
        if (badge) {
            badge.innerText = unreadCount;
            badge.style.display = unreadCount > 0 ? 'inline-block' : 'none';
        }
    } catch (error) {
        console.error('Failed to load notifications count:', error);
    }
}