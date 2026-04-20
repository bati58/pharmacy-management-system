// Resolve API base from current URL (/your-folder/frontend/... → /your-folder/backend/index.php)
(function resolveApiBase() {
    if (typeof window.API_BASE_URL === 'string' && window.API_BASE_URL.length) {
        return;
    }
    const p = window.location.pathname;
    const i = p.indexOf('/frontend/');
    if (i > 0) {
        window.API_BASE_URL = p.slice(0, i) + '/backend/index.php';
    } else {
        window.API_BASE_URL = '/pharmacy-management-system/backend/index.php';
    }
})();
const API_BASE_URL = window.API_BASE_URL;

async function apiRequest(endpoint, method = 'GET', data = null) {
    const url = API_BASE_URL + endpoint;
    const options = {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include'
    };
    if (data && (method === 'POST' || method === 'PUT')) {
        options.body = JSON.stringify(data);
    }
    try {
        const response = await fetch(url, options);
        const contentType = response.headers.get('content-type') || '';
        let result;
        if (contentType.includes('application/json')) {
            result = await response.json();
        } else {
            const text = await response.text();
            throw new Error('Server returned non-JSON response. Check backend error logs.');
        }
        if (!response.ok) {
            if (response.status === 401 && result.message === 'Unauthorized. Please login.') {
                localStorage.removeItem('user');
                const p = window.location.pathname;
                const idx = p.indexOf('/frontend/');
                const base = idx > 0 ? p.slice(0, idx) : '/pharmacy-management-system';
                window.location.href = base + '/frontend/pages/auth/login.php';
            }
            throw new Error(result.message || 'Request failed');
        }
        return result;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

const API = {
    // Auth
    login: (email, password) => apiRequest('/auth/login', 'POST', { email, password }),
    logout: () => apiRequest('/auth/logout', 'POST'),
    register: (data) => apiRequest('/auth/register', 'POST', data),
    resetPassword: (email) => apiRequest('/auth/reset-password', 'POST', { email }),
    activateInvitation: (data) => apiRequest('/auth/activate-invitation', 'POST', data),

    // Branches
    getBranches: () => apiRequest('/branches'),
    createBranch: (data) => apiRequest('/branches', 'POST', data),
    updateBranch: (id, data) => apiRequest(`/branches/${id}`, 'PUT', data),
    deleteBranch: (id) => apiRequest(`/branches/${id}`, 'DELETE'),

    // Users
    getUsers: () => apiRequest('/users'),
    createUser: (data) => apiRequest('/users', 'POST', data),
    updateUser: (id, data) => apiRequest(`/users/${id}`, 'PUT', data),
    deleteUser: (id) => apiRequest(`/users/${id}`, 'DELETE'),
    activateUser: (id) => apiRequest(`/users/${id}/activate`, 'PUT'),
    deactivateUser: (id) => apiRequest(`/users/${id}/deactivate`, 'PUT'),
    inviteUser: (data) => apiRequest('/users/invite', 'POST', data),   // ✅ Added

    // Drugs
    getDrugs: (branchId = null, search = '') => {
        let url = '/drugs';
        const params = new URLSearchParams();
        if (branchId) params.append('branch_id', branchId);
        if (search) params.append('search', search);
        if (params.toString()) url += '?' + params.toString();
        return apiRequest(url);
    },
    getDrug: (id) => apiRequest(`/drugs/${id}`),
    createDrug: (data) => apiRequest('/drugs', 'POST', data),
    updateDrug: (id, data) => apiRequest(`/drugs/${id}`, 'PUT', data),
    deleteDrug: (id) => apiRequest(`/drugs/${id}`, 'DELETE'),

    // Inventory
    updateStock: (id, quantityChange, reason) => apiRequest(`/inventory/${id}/stock`, 'PUT', { quantity_change: quantityChange, reason }),
    getLowStock: () => apiRequest('/inventory/low-stock'),
    getExpiringSoon: () => apiRequest('/inventory/expiring-soon'),

    // Transfers
    getTransfers: () => apiRequest('/transfers'),
    createTransfer: (data) => apiRequest('/transfers', 'POST', data),
    updateTransferStatus: (id, status) => apiRequest(`/transfers/${id}/status`, 'PUT', { status }),

    // Sales
    getSales: () => apiRequest('/sales'),
    getSale: (id) => apiRequest(`/sales/${id}`),
    createSale: (data) => apiRequest('/sales', 'POST', data),

    // Reports
    getSalesReport: (period, branchId, startDate, endDate) => {
        let url = '/reports/sales';
        const params = new URLSearchParams();
        if (period) params.append('period', period);
        if (branchId) params.append('branch_id', branchId);
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (params.toString()) url += '?' + params.toString();
        return apiRequest(url);
    },
    getRevenueByBranch: () => apiRequest('/reports/revenue-by-branch'),
    getRevenueByPharmacist: () => apiRequest('/reports/revenue-by-pharmacist'),
    getTopDrugs: (limit = 10) => apiRequest(`/reports/top-drugs?limit=${limit}`),
    getSlowMovingDrugs: (limit = 10) => apiRequest(`/reports/slow-moving-drugs?limit=${limit}`),

    // Notifications
    getNotifications: (unreadOnly = false) => apiRequest(`/notifications?unread_only=${unreadOnly}`),
    markNotificationRead: (id) => apiRequest(`/notifications/${id}/read`, 'PUT'),
    markAllRead: () => apiRequest('/notifications/read-all', 'PUT')
};