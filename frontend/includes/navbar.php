<!-- Top bar (visible on all pages) -->
<div class="bg-white/80 backdrop-blur-md sticky top-0 z-30 border-b border-slate-200 px-4 md:px-6 py-3 flex justify-between items-center no-print">
    <div class="flex items-center gap-4">
        <!-- Sidebar Toggle for Mobile/Tablet -->
        <button id="navMobileMenuBtn" class="p-2 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg lg:hidden transition-all">
            <i class="fas fa-bars text-xl"></i>
        </button>

        <!-- Breadcrumbs or Page Title -->
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">
            <?php
            $page = basename($_SERVER['PHP_SELF'], '.php');
            echo ucwords(str_replace('-', ' ', $page));
            ?>
        </h1>
    </div>
    
    <div class="flex items-center space-x-3">
        <!-- Quick Actions or Info -->
        <div class="hidden md:flex flex-col items-end mr-2">
            <!-- <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Logged in as</span> -->
            <span class="text-sm font-semibold text-slate-700"><?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?></span>
        </div>
        
        <div class="w-px h-8 bg-slate-200 mx-2 hidden md:block"></div>
        
        <button onclick="location.href='notifications.php'" class="p-2 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-full transition-all flex items-center justify-center" title="Notifications">
            <div class="relative">
                <i class="fas fa-bell text-lg"></i>
                <span id="navNotifBadge" class="notification-badge hidden" style="border-color: white;">0</span>
            </div>
        </button>
    </div>
</div>