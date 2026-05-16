<?php
require_once __DIR__ . '/../includes/init_session.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacist') {
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
        <div class="animate-slide-up">
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">New Sale Transaction</h2>
            <p class="text-slate-500 font-medium">Process prescriptions and OTC sales for customers.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Add Items -->
            <div class="lg:col-span-5 space-y-6 animate-slide-up" style="animation-delay: 0.1s;">
                <div class="card">
                    <div class="flex items-center gap-2 mb-6 text-blue-600">
                        <i class="fas fa-plus-circle"></i>
                        <h3 class="font-bold text-slate-800">Add Items to Cart</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="space-y-1" id="saleBranchWrap" style="display:none;">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Branch</label>
                            <select id="saleBranch" class="!bg-slate-50 border-slate-200"></select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Search Inventory</label>
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input type="text" id="drugSearch" placeholder="Type name or category..." class="pl-10 !bg-slate-50 border-slate-200">
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Select Drug Batch</label>
                            <select id="drugSelect" class="!bg-slate-50 border-slate-200"></select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Quantity</label>
                            <input type="number" id="itemQty" value="1" min="1" class="!bg-slate-50 border-slate-200">
                        </div>
                        <button onclick="addToCart()" class="btn btn-primary w-full mt-4 py-3 shadow-lg shadow-blue-500/20">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </div>

            <!-- Cart -->
            <div class="lg:col-span-7 animate-slide-up" style="animation-delay: 0.2s;">
                <div class="card !p-0 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <div class="flex items-center gap-2 text-slate-800">
                            <i class="fas fa-shopping-basket text-blue-600"></i>
                            <h3 class="font-bold">Checkout Summary</h3>
                        </div>
                        <span id="cartCountBadge" class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-1 rounded-full uppercase tracking-wider">0 Items</span>
                    </div>
                    
                    <div class="p-6">
                        <div class="table-container !border-slate-100 mb-6">
                            <table class="min-w-full !text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="!py-3">Item Description</th>
                                        <th class="!py-3 text-center">Qty</th>
                                        <th class="!py-3 text-right">Unit</th>
                                        <th class="!py-3 text-right">Subtotal</th>
                                        <th class="!py-3"></th>
                                    </tr>
                                </thead>
                                <tbody id="cartBody" class="divide-y divide-slate-50"></tbody>
                            </table>
                        </div>

                        <div class="bg-slate-900 rounded-2xl p-6 text-white mb-8 shadow-xl shadow-slate-900/20 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -mr-16 -mt-16"></div>
                            <div class="flex justify-between items-center relative z-10">
                                <span class="text-slate-400 font-bold uppercase tracking-[0.2em] text-[10px]">Grand Total Payable</span>
                                <span id="cartTotal" class="text-3xl font-black tracking-tight">$0.00</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div class="space-y-4">
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Customer Name</label>
                                    <input type="text" id="customerName" placeholder="Walk-in Customer" class="!bg-slate-50">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Payment Method</label>
                                    <select id="paymentMethod" class="!bg-slate-50">
                                        <option>Cash</option>
                                        <option>Card</option>
                                        <option>Mobile Money</option>
                                    </select>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Discount Amount ($)</label>
                                    <input type="number" id="discountAmount" value="0" min="0" step="0.01" class="!bg-slate-50 !text-red-600 font-bold">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Prescription Ref #</label>
                                    <input type="text" id="prescriptionReference" placeholder="RX-000000" class="!bg-slate-50">
                                </div>
                            </div>
                        </div>

                        <button onclick="completeSale()" class="btn btn-primary w-full py-4 text-base shadow-xl shadow-blue-500/30">
                            <i class="fas fa-check-circle"></i> Complete & Finalize Sale
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../assets/js/utils.js"></script>
<script src="../assets/js/api.js"></script>
<script src="../assets/js/sales.js"></script>
<?php include '../includes/footer.php'; ?>