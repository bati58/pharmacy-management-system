<?php
$role = $_SESSION['role'] ?? '';
$name = $_SESSION['name'] ?? 'User';
?>
<div class="sidebar flex flex-col h-screen fixed left-0 top-0 z-50 w-[260px]" id="sidebar">
    <!-- Brand -->
    <div class="p-6 border-b border-slate-800/50 flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-500 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/30">
                <i class="fas fa-prescription-bottle-alt text-white text-xl"></i>
            </div>
            <div>
                <h2 class="text-white text-lg font-bold leading-tight">PharmaFlow</h2>
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Smart Pharma</p>
            </div>
            <button class="ml-auto text-slate-400 hover:text-white md:hidden" id="closeSidebarBtn">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
    </div>

    <!-- Navigation (Scrollable) -->
    <nav class="flex-1 overflow-y-auto px-3 py-6 custom-scrollbar">
        <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'; ?>">
            <i class="fas fa-th-large text-lg"></i> <span class="text-sm">Dashboard</span>
        </a>
        
        <!-- Manager only -->
        <div class="role-manager">
            <a href="branches.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'branches.php' ? 'active' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'; ?>">
                <i class="fas fa-building text-lg"></i> <span class="text-sm">Branches</span>
            </a>
            <a href="user-management.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'user-management.php' ? 'active' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'; ?>">
                <i class="fas fa-user-shield text-lg"></i> <span class="text-sm">User Management</span>
            </a>
        </div>

        <div class="text-slate-500 text-[10px] font-bold uppercase tracking-[0.2em] px-4 mt-6 mb-2">Inventory & Sales</div>
        <!-- Drug Inventory: Store Keeper & Manager only -->
        <div class="role-storekeeper role-manager">
            <a href="drug-inventory.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'drug-inventory.php' ? 'active' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'; ?>">
                <i class="fas fa-capsules text-lg"></i> <span class="text-sm">Drug Inventory</span>
            </a>
        </div>
        <!-- Stock Transfers: Store Keeper, Manager & Pharmacist (read-only for pharmacist) -->
        <div class="role-storekeeper role-manager role-pharmacist">
            <a href="stock-transfers.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'stock-transfers.php' ? 'active' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'; ?>">
                <i class="fas fa-sync text-lg"></i> <span class="text-sm">Stock Transfers</span>
            </a>
        </div>

        <!-- Sales History: Pharmacist & Manager -->
        <div class="role-pharmacist role-manager">
            <a href="sales.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'sales.php' ? 'active' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'; ?>">
                <i class="fas fa-file-invoice-dollar text-lg"></i> <span class="text-sm">Sales History</span>
            </a>
        </div>
        <!-- New Sale: Pharmacist only (SRS §3.3) -->
        <div class="role-pharmacist">
            <a href="new-sale.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'new-sale.php' ? 'active' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'; ?>">
                <i class="fas fa-cart-plus text-lg"></i> <span class="text-sm">New Sale</span>
            </a>
        </div>

        <!-- Reports: Manager only (no section header needed) -->
        <div class="role-manager">
            <a href="reports.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'; ?>">
                <i class="fas fa-chart-bar text-lg"></i> <span class="text-sm">Reports</span>
            </a>
        </div>
        
        <!-- All roles -->
        <div class="text-slate-500 text-[10px] font-bold uppercase tracking-[0.2em] px-4 mt-6 mb-2">Account</div>
        <a href="settings.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'; ?>">
            <i class="fas fa-user-cog text-lg"></i> <span class="text-sm">Settings</span>
        </a>
        <a href="notifications.php" class="flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'; ?>">
            <div class="flex items-center gap-3">
                <i class="fas fa-bell text-lg"></i>
                <span class="text-sm">Notifications</span>
            </div>
            <span id="sidebarNotifCount" class="bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full hidden">0</span>
        </a>
    </nav>

    <!-- Profile (Fixed Bottom) -->
    <div class="p-4 border-t border-slate-800/50 flex-shrink-0">
        <div class="bg-slate-800/40 rounded-2xl p-4 border border-slate-700/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold text-sm shadow-inner flex-shrink-0">
                    <?php echo strtoupper(substr($name, 0, 1)); ?>
                </div>
                <div class="overflow-hidden flex-1 min-w-0">
                    <p class="text-white text-sm font-semibold truncate"><?php echo htmlspecialchars($name); ?></p>
                    <p class="text-slate-400 text-[10px] uppercase font-bold tracking-tight"><?php echo ucfirst(str_replace('_', ' ', $role)); ?></p>
                </div>
                <a href="../../backend/index.php/auth/logout"
                   id="logout-btn"
                   title="Logout"
                   class="flex-shrink-0 w-9 h-9 rounded-xl bg-slate-700/50 hover:bg-rose-500 flex items-center justify-center text-slate-300 hover:text-white transition-all duration-200 group">
                    <i class="fas fa-power-off text-sm group-hover:scale-110 transition-transform"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show/hide menu items based on user role
        const role = '<?php echo $role; ?>';
        if (role === 'manager') {
            document.querySelectorAll('.role-manager').forEach(el => el.style.display = 'flex');
            // Manager sees Inventory and Transfers for oversight, but doesn't see pharmacist-only New Sale
            document.querySelectorAll('.role-storekeeper').forEach(el => el.style.display = 'flex');
        } else if (role === 'pharmacist') {
            document.querySelectorAll('.role-pharmacist').forEach(el => el.style.display = 'flex');
        } else if (role === 'store_keeper') {
            document.querySelectorAll('.role-storekeeper').forEach(el => el.style.display = 'flex');
        }

        // Mobile sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const openBtn = document.getElementById('mobileMenuBtn');
        const closeBtn = document.getElementById('closeSidebarBtn');

        function openSidebar() {
            if (sidebar) sidebar.classList.add('open');
            if (overlay) overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            if (sidebar) sidebar.classList.remove('open');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (openBtn) openBtn.addEventListener('click', openSidebar);
        if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);
    });
</script>