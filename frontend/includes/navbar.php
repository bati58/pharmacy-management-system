<!-- Top bar (visible on all pages) -->
<div class="main-content">
    <div class="bg-white shadow-sm px-4 md:px-6 py-3 flex justify-between items-center">
        <h1 class="text-xl font-semibold text-gray-800">
            <?php
            $page = basename($_SERVER['PHP_SELF'], '.php');
            echo ucwords(str_replace('-', ' ', $page));
            ?>
        </h1>
        <div class="flex items-center space-x-4">
            <i class="fas fa-bell text-gray-500 cursor-pointer hover:text-gray-700" onclick="location.href='notifications.php'"></i>
            <span class="text-gray-700"><?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?></span>
        </div>
    </div>