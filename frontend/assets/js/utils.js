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
    const toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed top-4 right-4 z-[9999] flex flex-col gap-3 pointer-events-none';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    toast.className = `
        pointer-events-auto min-w-[300px] max-w-md bg-white rounded-2xl shadow-2xl p-4 border border-slate-100 
        flex items-center gap-3 animate-slide-in-right transform transition-all duration-300
    `;
    
    const icon = type === 'success' ? 'fa-check-circle text-emerald-500' : (type === 'error' ? 'fa-exclamation-circle text-rose-500' : 'fa-info-circle text-indigo-500');
    
    toast.innerHTML = `
        <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center flex-shrink-0">
            <i class="fas ${icon} text-lg"></i>
        </div>
        <div class="flex-1">
            <p class="text-sm font-bold text-slate-800">${message}</p>
        </div>
        <button class="text-slate-300 hover:text-slate-500 transition-colors">
            <i class="fas fa-times text-xs"></i>
        </button>
    `;
    
    document.getElementById('toast-container').appendChild(toast);
    
    // Auto remove
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(20px)';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
    
    toast.querySelector('button').onclick = () => toast.remove();
}

function showConfirm(title, message, type = 'danger') {
    return new Promise((resolve) => {
        const modal = document.getElementById('confirmModal');
        const titleEl = document.getElementById('confirmTitle');
        const messageEl = document.getElementById('confirmMessage');
        const okBtn = document.getElementById('confirmOkBtn');
        const cancelBtn = document.getElementById('confirmCancelBtn');

        titleEl.innerText = title;
        messageEl.innerText = message;

        if (type === 'danger') {
            okBtn.className = 'flex-1 px-6 py-3 bg-rose-500 text-white font-bold rounded-xl hover:bg-rose-600 shadow-lg shadow-rose-200 transition-all';
        } else {
            okBtn.className = 'flex-1 px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        const cleanup = (result) => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            okBtn.onclick = null;
            cancelBtn.onclick = null;
            resolve(result);
        };

        okBtn.onclick = () => cleanup(true);
        cancelBtn.onclick = () => cleanup(false);
    });
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