<!-- Top bar -->
<div class="main-content min-h-screen flex flex-col transition-all duration-300">
    <header class="h-20 bg-white/80 backdrop-blur-md sticky top-0 z-40 border-b border-slate-200/60 px-8 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">
                <?php
                $page = basename($_SERVER['PHP_SELF'], '.php');
                echo ucwords(str_replace(['-', '_'], ' ', $page));
                ?>
            </h1>
            <div class="hidden md:flex items-center bg-slate-100 rounded-xl px-3 py-1.5 gap-2 border border-slate-200/50">
                <i class="fas fa-search text-slate-400 text-xs"></i>
                <input type="text" placeholder="Search anything..." class="bg-transparent border-none text-xs focus:ring-0 w-64 text-slate-600 font-medium">
            </div>
        </div>
        
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-4 border-r border-slate-200 pr-6 mr-2">
                <button class="relative w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 transition-all duration-200" onclick="location.href='notifications.php'">
                    <i class="fas fa-bell"></i>
                    <span class="absolute top-2.5 right-2.5 w-2 h-2 bg-rose-500 rounded-full border-2 border-white"></span>
                </button>
                <button class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 transition-all duration-200">
                    <i class="fas fa-cog"></i>
                </button>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-bold text-slate-800 leading-none"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></p>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter mt-1"><?php echo ucfirst(str_replace('_', ' ', $_SESSION['role'] ?? 'Guest')); ?></p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg shadow-indigo-200 flex items-center justify-center text-white font-bold text-sm">
                    <?php echo strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)); ?>
                </div>
            </div>
        </div>
    </header>
    <div class="p-8 flex-1">