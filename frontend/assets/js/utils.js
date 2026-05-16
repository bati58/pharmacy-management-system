// Utility functions
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2);
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function formatDateTime(datetimeString) {
    if (!datetimeString) return '-';
    const date = new Date(datetimeString);
    return date.toLocaleString();
}

function checkExpiryStatus(dateString) {
    if (!dateString) return { status: 'valid' };
    const expiry = new Date(dateString);
    const now = new Date();
    
    expiry.setHours(0, 0, 0, 0);
    now.setHours(0, 0, 0, 0);
    
    if (expiry < now) {
        return { status: 'expired' };
    }
    
    const diffTime = expiry - now;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
    
    if (diffDays <= 30) {
        return { status: 'expiring_soon' };
    }
    
    return { status: 'valid' };
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

/**
 * Professional Toast Notification System
 */
function showToast(message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icon = type === 'success' ? 'fa-check-circle' : 
                 (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle');
    const iconColor = type === 'success' ? 'text-green-500' : 
                      (type === 'error' ? 'text-red-500' : 'text-blue-500');

    toast.innerHTML = `
        <i class="fas ${icon} ${iconColor} text-xl"></i>
        <span>${message}</span>
    `;

    container.appendChild(toast);

    // Auto-remove after 4 seconds
    setTimeout(() => {
        toast.classList.add('toast-fade-out');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

/* =====================================================
   Professional Dialog System
   Replaces native alert(), confirm(), prompt()
   ===================================================== */

function _ensureDialogStyles() {
    if (document.getElementById('pharmaflow-dialog-styles')) return;
    const style = document.createElement('style');
    style.id = 'pharmaflow-dialog-styles';
    style.textContent = `
        .pharmaflow-dialog-overlay {
            position: fixed; inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(6px);
            z-index: 99999;
            display: flex; align-items: center; justify-content: center;
            padding: 1rem;
            animation: pfDialogFadeIn 0.2s ease;
        }
        @keyframes pfDialogFadeIn { from { opacity: 0; } to { opacity: 1; } }
        .pharmaflow-dialog-box {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35);
            width: 100%; max-width: 420px;
            animation: pfDialogSlideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
        }
        @keyframes pfDialogSlideUp {
            from { opacity: 0; transform: translateY(24px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .pharmaflow-dialog-icon-wrap {
            width: 52px; height: 52px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; margin: 0 auto 1rem;
        }
        .pharmaflow-dialog-icon-danger  { background: #fee2e2; color: #dc2626; }
        .pharmaflow-dialog-icon-warning { background: #fef3c7; color: #d97706; }
        .pharmaflow-dialog-icon-success { background: #d1fae5; color: #059669; }
        .pharmaflow-dialog-icon-info    { background: #dbeafe; color: #2563eb; }
        .pharmaflow-dialog-header {
            padding: 1.75rem 1.75rem 0;
            text-align: center;
        }
        .pharmaflow-dialog-title {
            font-size: 1.1rem; font-weight: 700;
            color: #0f172a; margin-bottom: 0.4rem;
        }
        .pharmaflow-dialog-message {
            font-size: 0.875rem; color: #64748b;
            line-height: 1.6;
        }
        .pharmaflow-dialog-body { padding: 1.25rem 1.75rem; }
        .pharmaflow-dialog-input {
            width: 100%; padding: 0.625rem 0.875rem;
            border: 1.5px solid #e2e8f0; border-radius: 0.5rem;
            font-size: 0.875rem; margin-top: 0.75rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none; font-family: inherit;
            box-sizing: border-box;
        }
        .pharmaflow-dialog-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .pharmaflow-dialog-footer {
            padding: 1rem 1.75rem 1.5rem;
            display: flex; gap: 0.75rem; justify-content: center;
        }
        .pharmaflow-btn {
            flex: 1; padding: 0.65rem 1rem;
            border-radius: 0.6rem; font-weight: 600;
            font-size: 0.875rem; cursor: pointer;
            border: none; transition: all 0.2s ease;
            font-family: inherit;
        }
        .pharmaflow-btn-cancel {
            background: #f1f5f9; color: #475569;
            border: 1.5px solid #e2e8f0;
        }
        .pharmaflow-btn-cancel:hover { background: #e2e8f0; }
        .pharmaflow-btn-confirm-danger {
            background: #dc2626; color: white;
        }
        .pharmaflow-btn-confirm-danger:hover { background: #b91c1c; transform: translateY(-1px); }
        .pharmaflow-btn-confirm-primary {
            background: #2563eb; color: white;
        }
        .pharmaflow-btn-confirm-primary:hover { background: #1d4ed8; transform: translateY(-1px); }
        .pharmaflow-btn-ok {
            background: #2563eb; color: white;
            max-width: 140px; margin: 0 auto;
        }
        .pharmaflow-btn-ok:hover { background: #1d4ed8; transform: translateY(-1px); }
    `;
    document.head.appendChild(style);
}

/**
 * Professional confirm dialog - replaces native confirm()
 * @param {object} opts
 * @param {string} opts.title
 * @param {string} opts.message
 * @param {string} [opts.type]       'danger' | 'warning' | 'info'
 * @param {string} [opts.confirmText]
 * @param {string} [opts.cancelText]
 * @returns {Promise<boolean>}
 */
function showConfirmDialog({ title, message, type = 'danger', confirmText = 'Confirm', cancelText = 'Cancel' } = {}) {
    _ensureDialogStyles();
    return new Promise((resolve) => {
        const iconMap = {
            danger:  { cls: 'pharmaflow-dialog-icon-danger',  icon: 'fa-triangle-exclamation' },
            warning: { cls: 'pharmaflow-dialog-icon-warning', icon: 'fa-circle-exclamation' },
            info:    { cls: 'pharmaflow-dialog-icon-info',    icon: 'fa-circle-info' },
        };
        const btnCls = type === 'danger' ? 'pharmaflow-btn-confirm-danger' : 'pharmaflow-btn-confirm-primary';
        const { cls: iconWrapCls, icon: iconCls } = iconMap[type] || iconMap.danger;

        const overlay = document.createElement('div');
        overlay.className = 'pharmaflow-dialog-overlay';
        overlay.innerHTML = `
            <div class="pharmaflow-dialog-box" role="dialog" aria-modal="true">
                <div class="pharmaflow-dialog-header">
                    <div class="pharmaflow-dialog-icon-wrap ${iconWrapCls}">
                        <i class="fas ${iconCls}"></i>
                    </div>
                    <div class="pharmaflow-dialog-title">${escapeHtml(title)}</div>
                    <div class="pharmaflow-dialog-message">${message}</div>
                </div>
                <div class="pharmaflow-dialog-footer">
                    <button class="pharmaflow-btn pharmaflow-btn-cancel" id="pfDialogCancel">${escapeHtml(cancelText)}</button>
                    <button class="pharmaflow-btn ${btnCls}" id="pfDialogConfirm">${escapeHtml(confirmText)}</button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);

        const cleanup = (result) => { overlay.remove(); resolve(result); };
        overlay.querySelector('#pfDialogConfirm').addEventListener('click', () => cleanup(true));
        overlay.querySelector('#pfDialogCancel').addEventListener('click',  () => cleanup(false));
        overlay.addEventListener('click', (e) => { if (e.target === overlay) cleanup(false); });
    });
}

/**
 * Professional alert dialog - replaces native alert()
 * @param {object} opts
 * @param {string} opts.title
 * @param {string} opts.message
 * @param {string} [opts.type]  'success' | 'error' | 'info' | 'warning'
 * @returns {Promise<void>}
 */
function showAlertDialog({ title, message, type = 'info' } = {}) {
    _ensureDialogStyles();
    return new Promise((resolve) => {
        const iconMap = {
            success: { cls: 'pharmaflow-dialog-icon-success', icon: 'fa-circle-check' },
            error:   { cls: 'pharmaflow-dialog-icon-danger',  icon: 'fa-circle-xmark' },
            warning: { cls: 'pharmaflow-dialog-icon-warning', icon: 'fa-triangle-exclamation' },
            info:    { cls: 'pharmaflow-dialog-icon-info',    icon: 'fa-circle-info' },
        };
        const { cls: iconWrapCls, icon: iconCls } = iconMap[type] || iconMap.info;

        const overlay = document.createElement('div');
        overlay.className = 'pharmaflow-dialog-overlay';
        overlay.innerHTML = `
            <div class="pharmaflow-dialog-box" role="alertdialog" aria-modal="true">
                <div class="pharmaflow-dialog-header">
                    <div class="pharmaflow-dialog-icon-wrap ${iconWrapCls}">
                        <i class="fas ${iconCls}"></i>
                    </div>
                    <div class="pharmaflow-dialog-title">${escapeHtml(title)}</div>
                    <div class="pharmaflow-dialog-message">${message}</div>
                </div>
                <div class="pharmaflow-dialog-footer">
                    <button class="pharmaflow-btn pharmaflow-btn-ok" id="pfDialogOk">OK</button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);

        const cleanup = () => { overlay.remove(); resolve(); };
        overlay.querySelector('#pfDialogOk').addEventListener('click', cleanup);
        overlay.addEventListener('click', (e) => { if (e.target === overlay) cleanup(); });
    });
}

/**
 * Professional prompt dialog - replaces native prompt()
 * @param {object} opts
 * @param {string} opts.title
 * @param {string} opts.message
 * @param {string} [opts.placeholder]
 * @param {string} [opts.defaultValue]
 * @param {string} [opts.inputType]    'text' | 'number'
 * @param {string} [opts.confirmText]
 * @returns {Promise<string|null>}  resolves with value or null if cancelled
 */
function showPromptDialog({ title, message, placeholder = '', defaultValue = '', inputType = 'text', confirmText = 'Submit' } = {}) {
    _ensureDialogStyles();
    return new Promise((resolve) => {
        const overlay = document.createElement('div');
        overlay.className = 'pharmaflow-dialog-overlay';
        overlay.innerHTML = `
            <div class="pharmaflow-dialog-box" role="dialog" aria-modal="true">
                <div class="pharmaflow-dialog-header">
                    <div class="pharmaflow-dialog-icon-wrap pharmaflow-dialog-icon-info">
                        <i class="fas fa-pencil"></i>
                    </div>
                    <div class="pharmaflow-dialog-title">${escapeHtml(title)}</div>
                    <div class="pharmaflow-dialog-message">${message}</div>
                </div>
                <div class="pharmaflow-dialog-body">
                    <input
                        class="pharmaflow-dialog-input"
                        id="pfDialogInput"
                        type="${inputType}"
                        placeholder="${escapeHtml(placeholder)}"
                        value="${escapeHtml(defaultValue)}"
                        autocomplete="off"
                    >
                </div>
                <div class="pharmaflow-dialog-footer">
                    <button class="pharmaflow-btn pharmaflow-btn-cancel" id="pfDialogCancel">Cancel</button>
                    <button class="pharmaflow-btn pharmaflow-btn-confirm-primary" id="pfDialogConfirm">${escapeHtml(confirmText)}</button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
        const input = overlay.querySelector('#pfDialogInput');
        input.focus();
        input.select();

        const cleanup = (result) => { overlay.remove(); resolve(result); };
        overlay.querySelector('#pfDialogConfirm').addEventListener('click', () => cleanup(input.value));
        overlay.querySelector('#pfDialogCancel').addEventListener('click',  () => cleanup(null));
        overlay.addEventListener('click', (e) => { if (e.target === overlay) cleanup(null); });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') cleanup(input.value);
            if (e.key === 'Escape') cleanup(null);
        });
    });
}
