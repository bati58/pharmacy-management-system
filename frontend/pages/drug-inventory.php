<?php
require_once __DIR__ . '/../includes/init_session.php';
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['manager', 'store_keeper'])) {
    header('Location: dashboard.php');
    exit;
}
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<script>
    window.APP_ROLE = '<?php echo htmlspecialchars($_SESSION['role'] ?? '', ENT_QUOTES, 'UTF-8'); ?>';
    window.APP_BRANCH_ID = <?php echo (int)($_SESSION['branch_id'] ?? 0); ?>;
</script>
<div class="ml-64 flex-1">
    <?php include '../includes/navbar.php'; ?>
    <div class="p-6">
        <div class="page-toolbar flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold">Drug Inventory</h2>
            <?php if ($_SESSION['role'] == 'manager' || $_SESSION['role'] == 'store_keeper'): ?>
                <button id="add-drug-btn" class="bg-blue-600 text-white px-4 py-2 rounded">+ Add Drug</button>
            <?php endif; ?>
        </div>
        <div class="mb-4 flex gap-2">
            <input type="text" id="searchDrug" placeholder="Search by name, batch, or category..." class="flex-1 border rounded px-3 py-2">
            <select id="branchFilter" class="border rounded px-3 py-2">
                <option value="">All Branches</option>
            </select>
        </div>
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Drug</th>
                        <th class="px-4 py-2 text-left">Category</th>
                        <th class="px-4 py-2 text-left">Mfr / Supplier</th>
                        <th class="px-4 py-2 text-left">Batch</th>
                        <th class="px-4 py-2 text-left">Stock</th>
                        <th class="px-4 py-2 text-left">Price</th>
                        <th class="px-4 py-2 text-left">Expiry</th>
                        <th class="px-4 py-2 text-left">Branch</th>
                        <th class="px-4 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody id="drugsTable"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Drug Modal -->
<div id="drugModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-96 max-h-screen overflow-y-auto">
        <h3 class="text-lg font-bold mb-4" id="drugModalTitle">Add Drug</h3>
        <input type="hidden" id="drugId">
        <input type="text" id="drugName" placeholder="Drug Name" class="w-full border rounded px-3 py-2 mb-3" required>
        <input type="text" id="drugCategory" placeholder="Category" class="w-full border rounded px-3 py-2 mb-3">
        <input type="text" id="drugManufacturer" placeholder="Manufacturer" class="w-full border rounded px-3 py-2 mb-3">
        <input type="text" id="drugSupplier" placeholder="Supplier" class="w-full border rounded px-3 py-2 mb-3">
        <input type="text" id="drugBatch" placeholder="Batch Number" class="w-full border rounded px-3 py-2 mb-3" required>
        <input type="number" id="drugStock" placeholder="Stock Quantity" class="w-full border rounded px-3 py-2 mb-3">
        <input type="number" step="0.01" id="drugPrice" placeholder="Price" class="w-full border rounded px-3 py-2 mb-3" required>
        <input type="date" id="drugExpiry" class="w-full border rounded px-3 py-2 mb-3" required>
        <select id="drugBranch" class="w-full border rounded px-3 py-2 mb-3"></select>
        <div class="flex justify-end space-x-2">
            <button onclick="closeDrugModal()" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
            <button onclick="saveDrug()" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
        </div>
    </div>
</div>

<script src="../assets/js/utils.js"></script>
<script src="../assets/js/api.js"></script>
<script src="../assets/js/inventory.js"></script>
<?php include '../includes/footer.php'; ?>