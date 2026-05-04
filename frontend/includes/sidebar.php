<?php
$role = $_SESSION['role'] ?? '';
$name = $_SESSION['name'] ?? 'User';
$logoutHref = '/pharmacy-management-system/backend/index.php/auth/logout';
?>
<div class="sidebar flex flex-col h-screen overflow-hidden" id="sidebar">
    <div class="sidebar-logo flex-none">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center text-white shadow-lg">
                    <i class="fas fa-prescription-bottle-alt text-lg"></i>
                </div>
                <div>
                    <h2 class="text-white text-lg font-bold leading-none">PharmaFlow</h2>
                    <p class="text-blue-400 text-[10px] font-bold tracking-widest uppercase mt-1">Pharmacy System</p>
                </div>
            </div>
            <button class="text-gray-400 hover:text-white lg:hidden" id="closeSidebarBtn">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
    </div>
    
    <nav class="flex-1 overflow-y-auto py-4 px-3 custom-scrollbar">
        <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-house w-5 mr-3"></i> Dashboard
        </a>

        <?php if ($role === 'manager'): ?>
            <div class="px-4 py-2 mt-4 mb-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Management</div>
            <a href="branches.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'branches.php' ? 'active' : ''; ?>">
                <i class="fas fa-store w-5 mr-3"></i> Branches
            </a>
            <a href="user-management.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'user-management.php' ? 'active' : ''; ?>">
                <i class="fas fa-users w-5 mr-3"></i> Users
            </a>
        <?php endif; ?>

        <?php if ($role === 'manager' || $role === 'store_keeper'): ?>
            <div class="px-4 py-2 mt-4 mb-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Inventory</div>
            <a href="drug-inventory.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'drug-inventory.php' ? 'active' : ''; ?>">
                <i class="fas fa-capsules w-5 mr-3"></i> Inventory
            </a>
            <a href="stock-transfers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'stock-transfers.php' ? 'active' : ''; ?>">
                <i class="fas fa-exchange-alt w-5 mr-3"></i> Transfers
            </a>
        <?php endif; ?>

        <?php if ($role === 'manager' || $role === 'pharmacist'): ?>
            <div class="px-4 py-2 mt-4 mb-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Sales</div>
            <a href="sales.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'sales.php' ? 'active' : ''; ?>">
                <i class="fas fa-clock-rotate-left w-5 mr-3"></i> Sales History
            </a>
        <?php endif; ?>

        <?php if ($role === 'pharmacist'): ?>
            <a href="new-sale.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'new-sale.php' ? 'active' : ''; ?>">
                <i class="fas fa-plus-circle w-5 mr-3"></i> New Sale
            </a>
        <?php endif; ?>

        <?php if ($role === 'manager'): ?>
            <div class="px-4 py-2 mt-4 mb-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Analytics</div>
            <a href="reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar w-5 mr-3"></i> Reports
            </a>
        <?php endif; ?>

        <div class="px-4 py-2 mt-4 mb-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest">System</div>
        <a href="notifications.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : ''; ?> justify-between">
            <span class="flex items-center">
                <i class="fas fa-bell w-5 mr-3"></i> Notifications
            </span>
            <span id="notifCount" class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full hidden shadow-sm">0</span>
        </a>
    </nav>

    <div class="flex-none p-4 bg-slate-900/50 backdrop-blur-sm border-t border-white/5">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-slate-700 flex items-center justify-center text-slate-300">
                <i class="fas fa-user text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white text-xs font-bold truncate"><?php echo htmlspecialchars($name); ?></p>
                <p class="text-slate-500 text-[10px] font-medium uppercase tracking-wider"><?php echo ucfirst($role); ?></p>
            </div>
            <div class="flex items-center">
                <a href="<?php echo htmlspecialchars($logoutHref); ?>" class="p-2 text-slate-400 hover:text-red-400 transition-colors" id="logout-btn" title="Logout">
                    <i class="fas fa-power-off"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Overlay -->
<div class="mobile-backdrop" id="mobileOverlay"></div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobileOverlay');
        const openBtn = document.getElementById('navMobileMenuBtn');
        const closeBtn = document.getElementById('closeSidebarBtn');

        function openSidebar() {
            if (sidebar) sidebar.classList.add('open');
            if (overlay) overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            if (sidebar) sidebar.classList.remove('open');
            if (overlay) overlay.classList.remove('show');
            document.body.style.overflow = '';
        }

        // Use event delegation for better mobile click reliability
        document.addEventListener('click', function(e) {
            if (openBtn && openBtn.contains(e.target)) {
                openSidebar();
            } else if (closeBtn && closeBtn.contains(e.target)) {
                closeSidebar();
            } else if (overlay && overlay.contains(e.target)) {
                closeSidebar();
            }
        });
    });
</script>