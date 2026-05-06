<?php
require_once __DIR__ . '/../../backend/middleware/AuthMiddleware.php';
AuthMiddleware::check();
AuthMiddleware::requireRole(['manager', 'store_keeper']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaFlow - Stock Report</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media print {
            .no-print { display: none; }
            body { background: white; padding: 0; }
            .print-container { box-shadow: none; border: none; width: 100%; max-width: 100%; margin: 0; padding: 20px; }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
    <div class="print-container max-w-4xl mx-auto my-10 bg-white p-10 rounded-2xl shadow-xl border border-slate-100">
        
        <div class="flex justify-between items-start mb-10">
            <div>
                <h1 class="text-3xl font-extrabold text-blue-600 mb-1">PHARMAFLOW</h1>
                <p class="text-slate-500 text-sm">Pharmacy Management System</p>
                <div class="mt-4">
                    <h2 class="text-xl font-bold text-slate-800">Drug Inventory / Stock Report</h2>
                    <p class="text-slate-500 text-sm" id="reportDate"></p>
                </div>
            </div>
            <div class="text-right">
                <button onclick="window.print()" class="no-print btn btn-primary flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print Report
                </button>
                <div id="branchInfo" class="text-slate-600 font-medium"></div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-2 border-slate-100 bg-slate-50">
                        <th class="py-3 px-2 text-sm font-bold text-slate-700 uppercase">Drug Name</th>
                        <th class="py-3 px-2 text-sm font-bold text-slate-700 uppercase">Batch</th>
                        <th class="py-3 px-2 text-sm font-bold text-slate-700 uppercase">Store</th>
                        <th class="py-3 px-2 text-sm font-bold text-slate-700 uppercase">Shelf</th>
                        <th class="py-3 px-2 text-sm font-bold text-slate-700 uppercase">Total</th>
                        <th class="py-3 px-2 text-sm font-bold text-slate-700 uppercase">Expiry</th>
                        <th class="py-3 px-2 text-sm font-bold text-slate-700 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody id="stockTableBody">
                    <!-- Loaded via JS -->
                </tbody>
            </table>
        </div>

        <div class="mt-16 grid grid-cols-2 gap-10">
            <div class="border-t border-slate-200 pt-4">
                <p class="text-slate-400 text-xs uppercase mb-8">Prepared By (Store Keeper)</p>
                <div class="w-48 border-b border-slate-400"></div>
            </div>
            <div class="border-t border-slate-200 pt-4 text-right">
                <p class="text-slate-400 text-xs uppercase mb-8">Approved By (Manager)</p>
                <div class="w-48 border-b border-slate-400 ml-auto"></div>
            </div>
        </div>

        <div class="mt-10 pt-6 border-t border-slate-100 text-center text-slate-400 text-xs">
            PharmaFlow System - Official Inventory Document
        </div>
    </div>

    <script src="../assets/js/utils.js"></script>
    <script src="../assets/js/api.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async function() {
            document.getElementById('reportDate').textContent = new Date().toLocaleDateString('en-US', { 
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' 
            });

            try {
                const user = JSON.parse(localStorage.getItem('user') || '{}');
                const drugs = await API.getDrugs(user.branch_id);
                const tbody = document.getElementById('stockTableBody');
                
                if (drugs.data && drugs.data.length) {
                    drugs.data.forEach(drug => {
                        const total = parseInt(drug.stock) + parseInt(drug.dispensary_stock);
                        const isExpired = new Date(drug.expiry_date) < new Date();
                        const isLow = total <= 10;
                        
                        let statusText = 'Good';
                        let statusClass = 'text-green-600';
                        
                        if (isExpired) {
                            statusText = 'EXPIRED';
                            statusClass = 'text-red-600 font-bold';
                        } else if (isLow) {
                            statusText = 'LOW STOCK';
                            statusClass = 'text-orange-600 font-semibold';
                        }

                        tbody.innerHTML += `
                            <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                                <td class="py-4 px-2 font-medium text-slate-800">${escapeHtml(drug.name)}</td>
                                <td class="py-4 px-2 text-slate-600">${escapeHtml(drug.batch)}</td>
                                <td class="py-4 px-2 text-slate-600">${drug.stock}</td>
                                <td class="py-4 px-2 text-slate-600">${drug.dispensary_stock}</td>
                                <td class="py-4 px-2 font-bold text-slate-800">${total}</td>
                                <td class="py-4 px-2 text-slate-600">${drug.expiry_date}</td>
                                <td class="py-4 px-2 ${statusClass}">${statusText}</td>
                            </tr>
                        `;
                    });

                    if (drugs.data[0] && drugs.data[0].branch_name) {
                        document.getElementById('branchInfo').textContent = 'Branch: ' + drugs.data[0].branch_name;
                    }
                } else {
                    tbody.innerHTML = '<tr><td colspan="7" class="py-10 text-center text-slate-400">No drugs found in inventory.</td></tr>';
                }

            } catch (error) {
                console.error(error);
                alert('Error loading stock report data');
            }
        });
    </script>
</body>
</html>
