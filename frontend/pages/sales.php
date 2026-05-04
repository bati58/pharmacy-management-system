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
    <div class="p-6 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-slide-up">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Sales History</h2>
                <p class="text-slate-500 font-medium">Review and manage past transactions.</p>
            </div>
            <a href="new-sale.php" class="btn btn-primary shadow-lg shadow-blue-500/20">
                <i class="fas fa-plus"></i> Process New Sale
            </a>
        </div>

        <div class="card animate-slide-up" style="animation-delay: 0.1s;">
            <div class="table-container">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Pharmacist</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sales-table-body"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Sale Details Modal -->
<div id="saleDetailsModal" class="modal-backdrop hidden z-50 p-4">
    <div class="modal-content !max-w-3xl">
        <div class="flex items-center justify-between p-6 border-b border-slate-100">
            <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-file-invoice-dollar text-blue-600"></i>
                Invoice Details
            </h3>
            <button id="closeSaleDetailsBtn" class="text-slate-400 hover:text-slate-600 transition-colors" aria-label="Close">
                <i class="fas fa-times-circle text-xl"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8 p-4 bg-slate-50 rounded-xl border border-slate-100">
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Invoice Number</p>
                    <p id="detailInvoiceNo" class="text-sm font-bold text-slate-800">-</p>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Date & Time</p>
                    <p id="detailDate" class="text-sm font-semibold text-slate-700">-</p>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Customer</p>
                    <p id="detailCustomer" class="text-sm font-semibold text-slate-700">-</p>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Payment Method</p>
                    <p id="detailPayment" class="text-sm font-semibold text-slate-700">-</p>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pharmacist</p>
                    <p id="detailPharmacist" class="text-sm font-semibold text-slate-700">-</p>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Prescription Ref</p>
                    <p id="detailPrescription" class="text-sm font-semibold text-slate-700">-</p>
                </div>
            </div>

            <div class="table-container mb-6 !rounded-xl">
                <table class="min-w-full !text-xs">
                    <thead>
                        <tr>
                            <th>Drug Item</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody id="saleDetailsItems" class="divide-y divide-slate-50"></tbody>
                </table>
            </div>

            <div class="flex justify-end">
                <div class="w-full sm:w-64 space-y-2">
                    <div class="flex justify-between text-sm text-slate-500 px-2">
                        <span>Subtotal</span>
                        <span id="detailSubtotal" class="font-semibold text-slate-700">$0.00</span>
                    </div>
                    <div class="flex justify-between text-sm text-slate-500 px-2">
                        <span>Discount</span>
                        <span id="detailDiscount" class="font-semibold text-red-500">-$0.00</span>
                    </div>
                    <div class="flex justify-between items-center bg-blue-600 text-white p-4 rounded-xl shadow-lg shadow-blue-600/20 mt-4">
                        <span class="text-xs font-bold uppercase tracking-widest">Grand Total</span>
                        <span id="detailTotal" class="text-xl font-bold">$0.00</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-between items-center rounded-b-lg">
            <button class="btn !bg-white border border-slate-200 text-slate-600 hover:bg-slate-100" id="printReceiptBtn">
                <i class="fas fa-print"></i> Print Invoice
            </button>
            <button id="closeSaleDetailsBtnBottom" class="btn btn-primary px-8" onclick="closeModal()">Close Details</button>
        </div>
    </div>
</div>

<script src="../assets/js/utils.js"></script>
<script src="../assets/js/api.js"></script>
<script src="../assets/js/sales.js"></script>
<?php include '../includes/footer.php'; ?>