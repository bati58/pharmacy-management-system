<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['manager', 'pharmacist'])) {
    header('Location: dashboard.php');
    exit;
}
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<?php include '../includes/navbar.php'; ?>
<div class="animate-fade-in">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Sales History</h2>
            <p class="text-slate-500 mt-1 font-medium">Track and review all transactions across your branches.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="new-sale.php" class="btn-premium btn-premium-primary shadow-indigo-200">
                <i class="fas fa-plus"></i> Process New Sale
            </a>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="flex flex-col lg:flex-row gap-8 items-start">
        <!-- Sales Table Column -->
        <div class="flex-1 w-full overflow-hidden">
            <div class="card overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-history text-indigo-500"></i> Recent Transactions
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer / Date</th>
                                <th>Items</th>
                                <th>Total Amount</th>
                                <th>Payment</th>
                                <th>Pharmacist</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sales-table-body">
                            <!-- Data will be injected here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Details Panel Column -->
        <div id="saleDetailsPanel" class="w-full lg:w-[400px] hidden animate-slide-in-right sticky top-8">
            <div class="card p-8 bg-white border-indigo-100 shadow-xl shadow-indigo-100/20 relative overflow-hidden">
                <!-- Decoration -->
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-50 rounded-full blur-3xl opacity-60"></div>
                
                <div class="flex justify-between items-start mb-8 relative">
                    <div>
                        <h3 class="text-2xl font-black text-slate-800 tracking-tight">Invoice Details</h3>
                        <p class="text-indigo-600 font-bold text-xs uppercase tracking-widest mt-1" id="detailInvoiceNo">#INV-0000</p>
                    </div>
                    <button onclick="closeDetails()" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all flex items-center justify-center">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div id="detailContent" class="space-y-8 relative">
                    <!-- Dynamic content will be injected here -->
                </div>
                
                <div class="mt-8 pt-6 border-t border-slate-100 flex gap-3">
                    <button id="printBtn" onclick="printInvoice()" class="flex-1 btn-premium bg-slate-100 text-slate-600">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button id="pdfBtn" onclick="downloadPDF()" class="flex-1 btn-premium btn-premium-primary">
                        <i class="fas fa-download"></i> PDF
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