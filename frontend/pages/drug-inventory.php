<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<?php include '../includes/navbar.php'; ?>
<div class="animate-fade-in">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Drug Inventory</h2>
            <p class="text-slate-500 mt-1 font-medium">Manage and monitor your pharmaceutical stock across all branches.</p>
        </div>
        <div class="flex items-center gap-3">
            <?php if ($_SESSION['role'] == 'store_keeper'): ?>
                <button id="add-drug-btn" onclick="showDrugModal()" class="btn-premium btn-premium-primary shadow-indigo-200">
                    <i class="fas fa-plus"></i> Add New Drug
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($_SESSION['role'] === 'store_keeper'): ?>
    <!-- Stock Report Summary (SRS §3.2: Generate stock reports) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="card p-5 flex items-center gap-4 border-l-4 border-amber-400">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Low Stock Items</p>
                <p class="text-2xl font-black text-slate-800" id="sk-low-stock-count">—</p>
            </div>
        </div>
        <div class="card p-5 flex items-center gap-4 border-l-4 border-rose-400">
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-calendar-times text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Expiring in 30 Days</p>
                <p class="text-2xl font-black text-slate-800" id="sk-expiring-count">—</p>
            </div>
        </div>
        <div class="card p-5 flex items-center gap-4 border-l-4 border-indigo-400">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-500 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-boxes text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Drug Lines</p>
                <p class="text-2xl font-black text-slate-800" id="sk-total-drugs">—</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card p-4 mb-8 flex flex-col md:flex-row gap-4 items-center bg-white/50 backdrop-blur-sm border-slate-200/60">
        <div class="relative flex-1 w-full">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" id="searchDrug" placeholder="Search by name, category, or batch number..." 
                class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
        </div>
        <div class="w-full md:w-64">
            <select id="branchFilter" class="w-full py-2.5 bg-slate-50 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium text-slate-600">
                <option value="">All Branches</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <th>Drug Details</th>
                        <th>Category</th>
                        <th>Batch</th>
                        <th>Stock Level</th>
                        <th>Price</th>
                        <th>Expiry Date</th>
                        <th>Branch</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="drugsTable">
                    <!-- Data will be injected here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Drug Modal -->
<div id="drugModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-[100] p-4">
    <div class="bg-white/95 backdrop-blur shadow-2xl rounded-3xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto animate-fade-in border border-white/20">
        <div class="flex items-center justify-between mb-8">
            <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight" id="drugModalTitle">Add New Drug</h3>
            <button onclick="closeDrugModal()" class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <input type="hidden" id="drugId">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Drug Name</label>
                <input type="text" id="drugName" placeholder="e.g. Paracetamol" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Category</label>
                <input type="text" id="drugCategory" placeholder="e.g. Analgesic" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Batch Number</label>
                <input type="text" id="drugBatch" placeholder="e.g. B-2024-001" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Stock Quantity</label>
                <input type="number" id="drugStock" placeholder="0" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Selling Price</label>
                <input type="number" step="0.01" id="drugPrice" placeholder="0.00" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Cost Price</label>
                <input type="number" step="0.01" id="drugCostPrice" placeholder="0.00" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Manufacturer</label>
                <input type="text" id="drugManufacturer" placeholder="e.g. Pfizer" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Supplier</label>
                <input type="text" id="drugSupplier" placeholder="e.g. Global Meds" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Expiry Date</label>
                <input type="date" id="drugExpiry" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium" required>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Assign to Branch</label>
                <select id="drugBranch" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium text-slate-600"></select>
            </div>
        </div>
        
        <div class="mt-8 flex gap-3">
            <button onclick="closeDrugModal()" class="flex-1 px-6 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition-all">Cancel</button>
            <button onclick="saveDrug()" class="flex-1 px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all">Save Medication</button>
        </div>
    </div>
</div>

<!-- Stock Update Modal -->
<div id="stockModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-[100] p-4">
    <div class="bg-white/95 backdrop-blur shadow-2xl rounded-3xl p-8 w-full max-w-sm animate-fade-in border border-white/20 text-center">
        <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center mb-6 mx-auto">
            <i class="fas fa-boxes text-2xl"></i>
        </div>
        <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight mb-2">Adjust Inventory</h3>
        <p class="text-sm text-slate-500 font-medium mb-8" id="stockDrugName">Update medication count</p>
        
        <input type="hidden" id="stockDrugId">
        <div class="space-y-4 text-left">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Quantity Adjustment</label>
                <input type="number" id="stockChange" placeholder="e.g. 50 or -10" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium">
                <p class="text-[10px] text-slate-400 mt-1 px-1">Use positive for additions, negative for removals.</p>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Adjustment Reason</label>
                <select id="stockReason" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium text-slate-600">
                    <option value="manual">Manual Adjustment</option>
                    <option value="restock">New Stock Received</option>
                    <option value="damaged">Damaged / Expired</option>
                    <option value="return">Customer Return</option>
                </select>
            </div>
        </div>
        
        <div class="mt-8 flex gap-3">
            <button onclick="closeStockModal()" class="flex-1 px-6 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition-all">Cancel</button>
            <button onclick="saveStockUpdate()" class="flex-1 px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all">Update Stock</button>
        </div>
    </div>
</div>

<script src="../assets/js/utils.js"></script>
<script src="../assets/js/api.js"></script>
<script src="../assets/js/inventory.js"></script>
<?php include '../includes/footer.php'; ?>