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
        <div class="page-toolbar flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold">Sales</h2>
            <a href="new-sale.php" class="bg-green-600 text-white px-4 py-2 rounded">+ New Sale</a>
        </div>
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2">Invoice</th>
                        <th class="px-4 py-2">Customer</th>
                        <th class="px-4 py-2">Items</th>
                        <th class="px-4 py-2">Amount</th>
                        <th class="px-4 py-2">Payment</th>
                        <th class="px-4 py-2">Pharmacist</th>
                        <th class="px-4 py-2">Date</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody id="sales-table-body"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Sale Details Modal -->
<div id="saleDetailsModal" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="text-xl font-bold flex items-center gap-2">
                <i class="fas fa-file-invoice-dollar text-gray-700"></i>
                Invoice Details
            </h3>
            <button id="closeSaleDetailsBtn" class="text-gray-500 hover:text-gray-700 text-xl" aria-label="Close">
                <i class="fas fa-times-circle"></i>
            </button>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-2 gap-3 text-sm mb-4">
                <p><span class="font-semibold">Invoice:</span> <span id="detailInvoiceNo">-</span></p>
                <p><span class="font-semibold">Date:</span> <span id="detailDate">-</span></p>
                <p><span class="font-semibold">Customer:</span> <span id="detailCustomer">-</span></p>
                <p><span class="font-semibold">Payment:</span> <span id="detailPayment">-</span></p>
                <p><span class="font-semibold">Pharmacist:</span> <span id="detailPharmacist">-</span></p>
                <p><span class="font-semibold">Prescription:</span> <span id="detailPrescription">-</span></p>
            </div>

            <div class="overflow-x-auto border rounded">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left">Drug</th>
                            <th class="px-3 py-2 text-left">Qty</th>
                            <th class="px-3 py-2 text-left">Price</th>
                            <th class="px-3 py-2 text-left">Total</th>
                        </tr>
                    </thead>
                    <tbody id="saleDetailsItems"></tbody>
                </table>
            </div>

            <div class="mt-4 border rounded p-3 bg-gray-50 text-sm space-y-1">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span id="detailSubtotal">$0.00</span>
                </div>
                <div class="flex justify-between">
                    <span>Discount</span>
                    <span id="detailDiscount">$0.00</span>
                </div>
                <div class="flex justify-between font-bold text-base border-t pt-2 mt-2">
                    <span>Total</span>
                    <span id="detailTotal">$0.00</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/utils.js"></script>
<script src="../assets/js/api.js"></script>
<script src="../assets/js/sales.js"></script>
<?php include '../includes/footer.php'; ?>