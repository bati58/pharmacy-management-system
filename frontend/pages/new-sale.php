<?php
require_once __DIR__ . '/../includes/init_session.php';
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['manager', 'pharmacist'])) {
    header('Location: dashboard.php');
    exit;
}
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="ml-64 flex-1">
    <?php include '../includes/navbar.php'; ?>
    <div class="p-6">
        <h2 class="text-2xl font-bold mb-4">New Sale</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Add Items -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold mb-3">Add Items</h3>
                <div class="mb-3">
                    <label>Drug</label>
                    <select id="drugSelect" class="w-full border rounded px-3 py-2"></select>
                </div>
                <div class="mb-3">
                    <label>Quantity</label>
                    <input type="number" id="itemQty" value="1" min="1" class="w-full border rounded px-3 py-2">
                </div>
                <button onclick="addToCart()" class="bg-blue-600 text-white px-4 py-2 rounded w-full">Add to
                    Cart</button>
            </div>
            <!-- Cart -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold mb-3">Shopping Cart</h3>
                <div class="overflow-x-auto max-h-64">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cartBody"></tbody>
                    </table>
                </div>
                <div class="mt-3 text-right font-bold">Total: <span id="cartTotal">$0.00</span></div>
                <hr class="my-3">
                <div class="mb-3">
                    <label>Customer Name</label>
                    <input type="text" id="customerName" placeholder="Walk-in customer"
                        class="w-full border rounded px-3 py-2">
                </div>
                <div class="mb-3">
                    <label>Payment Method</label>
                    <select id="paymentMethod" class="w-full border rounded px-3 py-2">
                        <option>Cash</option>
                        <option>Card</option>
                        <option>Mobile Money</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Discount Amount</label>
                    <input type="number" id="discountAmount" value="0" min="0" step="0.01" class="w-full border rounded px-3 py-2">
                </div>
                <div class="mb-3">
                    <label>Prescription Reference (optional)</label>
                    <input type="text" id="prescriptionReference" placeholder="RX-12345" class="w-full border rounded px-3 py-2">
                </div>
                <button onclick="completeSale()" class="bg-green-600 text-white px-4 py-2 rounded w-full">Complete
                    Sale</button>
            </div>
        </div>
    </div>
</div>
<script src="../assets/js/utils.js"></script>
<script src="../assets/js/api.js"></script>
<script src="../assets/js/sales.js"></script>
<?php include '../includes/footer.php'; ?>