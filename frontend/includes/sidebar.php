<?php
$role = $_SESSION['role'] ?? '';
$name = $_SESSION['name'] ?? 'User';
$logoutHref = '/pharmacy-management-system/backend/index.php/auth/logout';
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-prescription-bottle-alt text-lg"></i>
            </div>
            <div>
                <h2 class="text-white text-lg font-bold leading-none">BatiFlow</h2>
                <p class="text-blue-400 text-[10px] font-bold tracking-widest uppercase mt-1">Smart Pharma</p>
            </div>
        </div>
        <button class="text-gray-400 hover:text-white md:hidden absolute top-5 right-4" id="closeSidebarBtn">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>
    <nav class="mt-4">
        <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-house w-5 mr-3"></i> Dashboard
        </a>

        <?php if ($role === 'manager'): ?>
            <!-- Manager Only -->
            <a href="branches.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'branches.php' ? 'active' : ''; ?>">
                <i class="fas fa-store w-5 mr-3"></i> Branches
            </a>
            <a href="user-management.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'user-management.php' ? 'active' : ''; ?>">
                <i class="fas fa-users w-5 mr-3"></i> Users
            </a>
        <?php endif; ?>

        <?php if ($role === 'manager' || $role === 'store_keeper'): ?>
            <!-- Store Keeper & Manager -->
            <a href="drug-inventory.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'drug-inventory.php' ? 'active' : ''; ?>">
                <i class="fas fa-capsules w-5 mr-3"></i> Inventory
            </a>
            <a href="stock-transfers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'stock-transfers.php' ? 'active' : ''; ?>">
                <i class="fas fa-exchange-alt w-5 mr-3"></i> Transfers
            </a>
        <?php endif; ?>

        <?php if ($role === 'manager' || $role === 'pharmacist'): ?>
            <!-- Pharmacist & Manager -->
            <a href="sales.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'sales.php' ? 'active' : ''; ?>">
                <i class="fas fa-clock-rotate-left w-5 mr-3"></i> Sales History
            </a>
        <?php endif; ?>

        <?php if ($role === 'pharmacist'): ?>
            <!-- Pharmacist Only -->
            <a href="new-sale.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'new-sale.php' ? 'active' : ''; ?>">
                <i class="fas fa-plus-circle w-5 mr-3"></i> New Sale
            </a>
        <?php endif; ?>

        <?php if ($role === 'manager'): ?>
            <!-- Manager Only -->
            <a href="reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar w-5 mr-3"></i> Reports
            </a>
        <?php endif; ?>

        <a href="notifications.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : ''; ?> justify-between">
            <span class="flex items-center">
                <i class="fas fa-bell w-5 mr-3"></i> Notifications
            </span>
            <span id="notifCount" class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full hidden shadow-sm">0</span>
        </a>
    </nav>
    <div class="absolute bottom-0 w-full p-4 bg-slate-900/50 backdrop-blur-sm border-t border-white/5">
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const openBtn = document.getElementById('navMobileMenuBtn');
        const closeBtn = document.getElementById('closeSidebarBtn');

        function openSidebar() {
            sidebar.classList.add('open');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (openBtn) openBtn.addEventListener('click', openSidebar);
        if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);
    });
</script>