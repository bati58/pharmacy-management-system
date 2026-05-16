<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacist') {
    header('Location: dashboard.php');
    exit;
}
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<?php include '../includes/navbar.php'; ?>
<div class="animate-fade-in">
    <div class="mb-8">
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Point of Sale</h2>
        <p class="text-slate-500 mt-1 font-medium">Create new transactions and process customer payments.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Add Items Section -->
        <div class="lg:col-span-5 space-y-6">
            <div class="card p-6">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="fas fa-plus"></i>
                    </div>
                    <h3 class="font-bold text-slate-800">Add Items to Cart</h3>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Select Medication</label>
                        <select id="drugSelect" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium text-slate-600">
                            <option value="">Searching drugs...</option>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Quantity</label>
                            <input type="number" id="itemQty" value="1" min="1" class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium">
                        </div>
                        <div class="flex items-end">
                            <button onclick="addToCart()" class="w-full h-[52px] btn-premium btn-premium-primary shadow-indigo-200">
                                <i class="fas fa-cart-plus mr-1"></i> Add
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats / Info -->
            <div class="card p-6 bg-indigo-600 text-white overflow-hidden relative">
                <div class="relative z-10">
                    <p class="text-indigo-100 font-bold text-xs uppercase tracking-widest mb-1">Pharmacist Session</p>
                    <h4 class="text-2xl font-bold"><?php echo $_SESSION['name']; ?></h4>
                    <p class="text-indigo-100 mt-2 text-sm opacity-80">Branch: <span class="font-bold">Main Branch</span></p>
                </div>
                <i class="fas fa-user-md absolute -right-4 -bottom-4 text-8xl text-white/10 rotate-12"></i>
            </div>
        </div>

        <!-- Checkout Section -->
        <div class="lg:col-span-7">
            <div class="card flex flex-col h-full overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-shopping-basket text-indigo-500"></i> Active Basket
                    </h3>
                    <button onclick="clearCart()" class="text-xs font-bold text-rose-500 hover:text-rose-600 uppercase tracking-wider transition-colors">Clear All</button>
                </div>
                
                <!-- Cart Items List -->
                <div class="flex-1 overflow-y-auto max-h-[400px]">
                    <table class="w-full border-separate border-spacing-0">
                        <thead class="sticky top-0 bg-white shadow-sm z-10">
                            <tr>
                                <th class="bg-white">Item</th>
                                <th class="bg-white">Price</th>
                                <th class="bg-white">Qty</th>
                                <th class="bg-white text-right">Subtotal</th>
                                <th class="bg-white w-12"></th>
                            </tr>
                        </thead>
                        <tbody id="cartBody">
                            <!-- Items will be injected here -->
                        </tbody>
                    </table>
                </div>

                <!-- Checkout Form -->
                <div class="p-6 bg-slate-50/50 border-t border-slate-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Customer Details</label>
                                <input type="text" id="customerName" placeholder="e.g. Walk-in Customer" class="w-full px-4 py-3 bg-white border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Payment Method</label>
                                <select id="paymentMethod" class="w-full px-4 py-3 bg-white border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium text-slate-600">
                                    <option>Cash</option>
                                    <option>Card</option>
                                    <option>Mobile Money</option>
                                </select>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Discount</label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                        <input type="number" id="discountAmount" value="0" min="0" step="0.01" class="w-full pl-8 pr-4 py-3 bg-white border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium" oninput="updateCartDisplay()">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Prescription #</label>
                                    <input type="text" id="prescriptionRef" placeholder="Ref ID" class="w-full px-4 py-3 bg-white border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium">
                                </div>
                                <div class="flex items-center gap-2 px-1 mt-1">
                                    <input type="checkbox" id="prescriptionValidated" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                                    <label for="prescriptionValidated" class="text-[10px] font-bold text-slate-500 uppercase tracking-wider cursor-pointer">Prescription Validated</label>
                                </div>
                            </div>
                            
                            <div class="bg-indigo-50 p-4 rounded-2xl border border-indigo-100/50">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-indigo-500 font-bold text-xs uppercase tracking-wider">Payable Total</span>
                                    <span id="cartTotal" class="text-2xl font-black text-indigo-700">$0.00</span>
                                </div>
                                <p class="text-[10px] text-indigo-400 font-medium">All taxes and discounts are included.</p>
                            </div>
                        </div>
                    </div>
                    
                    <button onclick="completeSale()" class="w-full py-4 btn-premium btn-premium-primary shadow-indigo-200 text-lg">
                        <i class="fas fa-check-circle mr-2"></i> Complete Transaction
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../assets/js/utils.js"></script>
<script src="../assets/js/api.js"></script>
<script src="../assets/js/sales.js"></script>
<?php include '../includes/footer.php'; ?>