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
    <div class="p-6 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-slide-up">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Drug Inventory</h2>
                <p class="text-slate-500 font-medium">Manage and monitor your pharmaceutical stock.</p>
            </div>
            <?php if ($_SESSION['role'] == 'manager' || $_SESSION['role'] == 'store_keeper'): ?>
                <button id="add-drug-btn" class="btn btn-primary shadow-lg shadow-blue-500/20">
                    <i class="fas fa-plus"></i> Add New Drug
                </button>
            <?php endif; ?>
        </div>

        <div class="card animate-slide-up" style="animation-delay: 0.1s;">
            <div class="flex flex-col md:flex-row gap-4 mb-6">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" id="searchDrug" placeholder="Search by name, batch, or category..." class="pl-10 !bg-slate-50 border-slate-200">
                </div>
                <div class="relative w-full md:w-64">
                    <i class="fas fa-filter absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <select id="branchFilter" class="pl-10 !bg-slate-50 border-slate-200">
                        <option value="">All Branches</option>
                    </select>
                </div>
            </div>

            <div class="table-container">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th>Drug</th>
                            <th>Category</th>
                            <th>Mfr / Supplier</th>
                            <th>Batch</th>
                            <th>Store</th>
                            <th>Shelf</th>
                            <th>Cost</th>
                            <th>Price</th>
                            <th>Rx</th>
                            <th>Expiry</th>
                            <th>Branch</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="drugsTable"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Drug Modal -->
<div id="drugModal" class="modal-backdrop hidden z-50">
    <div class="modal-content !max-w-xl">
        <div class="flex items-center justify-between p-6 border-b border-slate-100">
            <h3 class="text-xl font-bold text-slate-800" id="drugModalTitle">Add New Drug</h3>
            <button onclick="closeDrugModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fas fa-times-circle text-xl"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="drugId">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Drug Name</label>
                    <input type="text" id="drugName" placeholder="e.g. Paracetamol" class="!bg-slate-50" required>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Category</label>
                    <input type="text" id="drugCategory" placeholder="e.g. Analgesic" class="!bg-slate-50">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Manufacturer</label>
                    <input type="text" id="drugManufacturer" placeholder="Manufacturer Name" class="!bg-slate-50">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Supplier</label>
                    <input type="text" id="drugSupplier" placeholder="Supplier Name" class="!bg-slate-50">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Batch Number</label>
                    <input type="text" id="drugBatch" placeholder="Batch ID" class="!bg-slate-50" required>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Stock Quantity</label>
                    <input type="number" id="drugStock" placeholder="0" class="!bg-slate-50">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Selling Price</label>
                    <input type="number" step="0.01" id="drugPrice" placeholder="0.00" class="!bg-slate-50" required <?php echo $_SESSION['role'] !== 'manager' ? 'disabled' : ''; ?>>
                    <?php if ($_SESSION['role'] !== 'manager'): ?>
                        <p class="text-[10px] text-slate-400">Only managers can update pricing.</p>
                    <?php endif; ?>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Cost Price</label>
                    <input type="number" step="0.01" id="drugCostPrice" placeholder="0.00" class="!bg-slate-50" required <?php echo $_SESSION['role'] !== 'manager' ? 'disabled' : ''; ?>>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Expiry Date</label>
                    <input type="date" id="drugExpiry" class="!bg-slate-50" required>
                </div>
                <div class="flex items-center gap-2 pt-6">
                    <input type="checkbox" id="drugRequiresPrescription" class="w-4 h-4 text-blue-600 rounded">
                    <label class="text-xs font-bold text-slate-600 uppercase">Requires Prescription</label>
                </div>
            </div>
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Branch Assignment</label>
                <select id="drugBranch" class="!bg-slate-50"></select>
            </div>
        </div>
        <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 rounded-b-lg">
            <button onclick="closeDrugModal()" class="btn !bg-white border border-slate-200 text-slate-600 hover:bg-slate-100">Cancel</button>
            <button onclick="saveDrug()" class="btn btn-primary px-8">Save Drug Details</button>
        </div>
    </div>
</div>

<script src="../assets/js/utils.js"></script>
<script src="../assets/js/api.js"></script>
<script src="../assets/js/inventory.js"></script>
<?php include '../includes/footer.php'; ?>