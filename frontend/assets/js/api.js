// Dynamically detect project root
const getProjectRoot = () => {
    const path = window.location.pathname;
    const parts = path.split('/');
    
    // Check for standard subfolders first
    const frontendIndex = parts.indexOf('frontend');
    if (frontendIndex !== -1) {
        return parts.slice(0, frontendIndex).join('/') || '';
    }
    
    const backendIndex = parts.indexOf('backend');
    if (backendIndex !== -1) {
        return parts.slice(0, backendIndex).join('/') || '';
    }
    
    // If we are in the root (like register.php), the root is everything before the last filename
    // and we ensure it doesn't return an empty string if it's the domain root
    const root = parts.slice(0, parts.length - 1).join('/');
    return root || '';
};

const PROJECT_ROOT = getProjectRoot();
const API_BASE_URL = PROJECT_ROOT + '/backend/index.php';
console.log('API Base URL detected:', API_BASE_URL);

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
        const text = await response.text(); // Get raw text first
        
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('Failed to parse JSON. Raw response:', text);
            throw new Error('Server returned invalid response. Check console for details.');
        }

        if (!response.ok) {
            if (response.status === 401 && result.message === 'Unauthorized. Please login.') {
                localStorage.removeItem('user');
                window.location.href = PROJECT_ROOT + '/frontend/pages/auth/login.php';
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
    resetPassword: (email) => apiRequest('/auth/reset-password', 'POST', { email }),
    activateInvitation: (data) => apiRequest('/auth/activate-invitation', 'POST', data),
    validateInvitation: (token) => apiRequest(`/auth/validate-invitation?token=${token}`, 'GET'),
    updateProfile: (data) => apiRequest('/auth/update-profile', 'POST', data),
    changePassword: (data) => apiRequest('/auth/change-password', 'POST', data),

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
    getSales: (branchId = null, period = 'all') => {
        let url = '/sales';
        const params = new URLSearchParams();
        if (branchId) params.append('branch_id', branchId);
        if (period) params.append('period', period);
        if (params.toString()) url += '?' + params.toString();
        return apiRequest(url);
    },
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