// Utility functions (should be included before other scripts)

function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function formatDateTime(datetimeString) {
    const date = new Date(datetimeString);
    return date.toLocaleString();
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function (m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function showToast(message, type = 'success') {
    // Simple alert for now; can be replaced with a nicer toast library
    alert(message);
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function checkExpiryStatus(expiryDate) {
    const today = new Date();
    const expiry = new Date(expiryDate);
    const diffDays = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));
    if (diffDays < 0) return { status: 'expired', days_left: diffDays };
    if (diffDays <= 30) return { status: 'expiring_soon', days_left: diffDays };
    return { status: 'ok', days_left: diffDays };
}