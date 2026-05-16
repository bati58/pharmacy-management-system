<?php
require_once __DIR__ . '/../includes/init_session.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Receipt - PharmaFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none; }
            body { background: white; }
            .print-container { box-shadow: none; border: none; width: 100%; max-width: 100%; margin: 0; padding: 0; }
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-900 min-h-screen flex flex-col">
    <div class="flex-1 flex flex-col items-center p-4 md:p-8">
        <div class="no-print w-full max-w-2xl flex justify-between mb-6">
            <button onclick="window.history.back()" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 transition-colors">
                <i class="fas fa-arrow-left"></i> Back to Sales
            </button>
            <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors shadow-lg shadow-blue-500/20">
                <i class="fas fa-print mr-2"></i> Print Receipt
            </button>
        </div>

        <div id="receiptContent" class="print-container bg-white w-full max-w-2xl shadow-xl rounded-2xl p-8 md:p-12 border border-slate-100">
            <div class="text-center mb-10">
                <h1 class="text-3xl font-black text-blue-600 tracking-tighter mb-1">PharmaFlow</h1>
                <p class="text-slate-500 font-bold uppercase tracking-widest text-xs">Pharmacy Management System</p>
                <div class="h-1 w-12 bg-blue-600 mx-auto mt-4 rounded-full"></div>
            </div>

            <div class="flex flex-col md:flex-row justify-between gap-6 mb-10 pb-8 border-b border-slate-100">
                <div class="space-y-2">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Invoice Details</p>
                    <p class="text-xl font-bold text-slate-800" id="invoiceNo">Loading...</p>
                    <p class="text-sm text-slate-500" id="saleDate"></p>
                </div>
                <div class="md:text-right space-y-2">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Store Information</p>
                    <p class="text-sm font-bold text-slate-700" id="branchName"></p>
                    <p class="text-xs text-slate-500" id="pharmacistName"></p>
                </div>
            </div>

            <div class="mb-10">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Customer Info</p>
                <p class="text-slate-800 font-semibold" id="customerName"></p>
                <div id="prescriptionRow" class="hidden mt-2 text-sm text-red-600 font-medium">
                    <i class="fas fa-prescription mr-1"></i> Rx Ref: <span id="rxRef"></span>
                </div>
            </div>

            <table class="w-full mb-10">
                <thead>
                    <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3">Item Description</th>
                        <th class="py-3 text-center">Qty</th>
                        <th class="py-3 text-right">Price</th>
                        <th class="py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody id="receiptItems" class="text-sm">
                    <!-- Items injected here -->
                </tbody>
            </table>

            <div class="border-t border-slate-100 pt-8 space-y-3 max-w-xs ml-auto">
                <div class="flex justify-between text-sm text-slate-500">
                    <span>Subtotal</span>
                    <span id="subtotal">0.00</span>
                </div>
                <div class="flex justify-between text-sm text-red-500">
                    <span>Discount</span>
                    <span id="discount">0.00</span>
                </div>
                <div class="flex justify-between text-xl font-black text-slate-800 pt-3 border-t border-slate-100">
                    <span>Total</span>
                    <span id="grandTotal">0.00</span>
                </div>
                <div class="text-right pt-2">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 bg-slate-50 px-2 py-1 rounded" id="paymentMethod"></span>
                </div>
            </div>

            <div class="mt-20 text-center space-y-2">
                <p class="text-slate-400 text-sm italic font-medium">Thank you for your business!</p>
                <p class="text-[10px] text-slate-300 uppercase tracking-[0.2em] font-bold">This is a computer generated receipt</p>
            </div>
        </div>
    </div>

    <script src="../assets/js/utils.js"></script>
    <script src="../assets/js/api.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const urlParams = new URLSearchParams(window.location.search);
            const saleId = urlParams.get('id');
            if (!saleId) {
                alert('No sale ID provided');
                window.location.href = 'sales.php';
                return;
            }

            try {
                const response = await API.getSale(saleId);
                const sale = response.data;
                if (!sale) throw new Error('Sale not found');

                document.getElementById('invoiceNo').textContent = sale.invoice_no;
                document.getElementById('saleDate').textContent = formatDate(sale.sale_date);
                document.getElementById('customerName').textContent = sale.customer_name;
                document.getElementById('pharmacistName').textContent = 'Served by: ' + sale.pharmacist_name;
                document.getElementById('paymentMethod').textContent = sale.payment_method;
                
                // Note: branch name might not be in the sale object directly, need to check
                // If it's missing, we use the user's current branch or fetched data
                document.getElementById('branchName').textContent = 'Pharmacy Branch';

                if (sale.prescription_reference) {
                    document.getElementById('prescriptionRow').classList.remove('hidden');
                    document.getElementById('rxRef').textContent = sale.prescription_reference;
                }

                const tbody = document.getElementById('receiptItems');
                let subtotal = 0;
                sale.items.forEach(item => {
                    const total = item.quantity * item.price;
                    subtotal += total;
                    tbody.innerHTML += `
                        <tr class="border-b border-slate-50">
                            <td class="py-4 font-medium text-slate-700">${escapeHtml(item.drug_name)}</td>
                            <td class="py-4 text-center text-slate-500">${item.quantity}</td>
                            <td class="py-4 text-right text-slate-500">${formatCurrency(item.price)}</td>
                            <td class="py-4 text-right font-semibold text-slate-700">${formatCurrency(total)}</td>
                        </tr>
                    `;
                });

                document.getElementById('subtotal').textContent = formatCurrency(subtotal);
                document.getElementById('discount').textContent = '-' + formatCurrency(sale.discount_amount);
                document.getElementById('grandTotal').textContent = formatCurrency(sale.total_amount);

            } catch (error) {
                console.error(error);
                alert('Error loading receipt data');
            }
        });
    </script>
</body>
</html>
