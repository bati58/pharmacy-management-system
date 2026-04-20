<?php
$role = $_SESSION['role'] ?? '';
$name = $_SESSION['name'] ?? 'User';
?>
<div class="sidebar" id="sidebar">
    <div class="p-4 border-b border-gray-700 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center text-white">
                <i class="fas fa-prescription-bottle-alt"></i>
            </div>
            <div>
                <h2 class="text-white text-xl font-bold">BatiFlow Smart</h2>
                <p class="text-gray-400 text-sm">Pharma Management</p>
            </div>
        </div>
        <button class="text-gray-400 hover:text-white md:hidden" id="closeSidebarBtn">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>
    <nav class="mt-6">
        <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt w-5 mr-3"></i> Dashboard
        </a>
        <!-- Manager only -->
        <a href="branches.php" class="role-manager <?php echo basename($_SERVER['PHP_SELF']) == 'branches.php' ? 'active' : ''; ?>" style="display:none;">
            <i class="fas fa-store w-5 mr-3"></i> Branches
        </a>
        <a href="user-management.php" class="role-manager <?php echo basename($_SERVER['PHP_SELF']) == 'user-management.php' ? 'active' : ''; ?>" style="display:none;">
            <i class="fas fa-users w-5 mr-3"></i> User Management
        </a>
        <!-- Store Keeper & Manager -->
        <a href="drug-inventory.php" class="role-storekeeper role-manager <?php echo basename($_SERVER['PHP_SELF']) == 'drug-inventory.php' ? 'active' : ''; ?>" style="display:none;">
            <i class="fas fa-capsules w-5 mr-3"></i> Drug Inventory
        </a>
        <a href="stock-transfers.php" class="role-storekeeper role-manager <?php echo basename($_SERVER['PHP_SELF']) == 'stock-transfers.php' ? 'active' : ''; ?>" style="display:none;">
            <i class="fas fa-exchange-alt w-5 mr-3"></i> Stock Transfers
        </a>
        <!-- Pharmacist & Manager (sales list oversight for manager) -->
        <a href="sales.php" class="role-pharmacist role-manager <?php echo basename($_SERVER['PHP_SELF']) == 'sales.php' ? 'active' : ''; ?>" style="display:none;">
            <i class="fas fa-shopping-cart w-5 mr-3"></i> Sales
        </a>
        <!-- Pharmacist only -->
        <a href="new-sale.php" class="role-pharmacist <?php echo basename($_SERVER['PHP_SELF']) == 'new-sale.php' ? 'active' : ''; ?>" style="display:none;">
            <i class="fas fa-plus-circle w-5 mr-3"></i> New Sale
        </a>
        <!-- Manager only -->
        <a href="reports.php" class="role-manager <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>" style="display:none;">
            <i class="fas fa-chart-line w-5 mr-3"></i> Reports
        </a>
        <!-- All roles -->
        <a href="notifications.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : ''; ?>">
            <i class="fas fa-bell w-5 mr-3"></i> Notifications
            <span id="notifCount" class="notification-badge hidden">0</span>
        </a>
    </nav>
    <div class="absolute bottom-0 w-full p-4 border-t border-gray-700">
        <div class="flex items-center">
            <i class="fas fa-user-circle text-gray-400 text-2xl mr-2"></i>
            <div>
                <p class="text-white text-sm"><?php echo htmlspecialchars($name); ?></p>
                <p class="text-gray-400 text-xs"><?php echo ucfirst($role); ?></p>
            </div>
            <?php
            $pageDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
            $appWebRoot = dirname(dirname($pageDir));
            $logoutHref = rtrim(str_replace('\\', '/', $appWebRoot), '/') . '/backend/index.php/auth/logout';
            ?>
            <a href="<?php echo htmlspecialchars($logoutHref); ?>" class="ml-auto text-gray-400 hover:text-white" id="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</div>

<script>
    // Show/hide menu items based on user role
    const role = '<?php echo $role; ?>';
    if (role === 'manager') {
        document.querySelectorAll('.role-manager').forEach(el => el.style.display = 'flex');
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
</script>