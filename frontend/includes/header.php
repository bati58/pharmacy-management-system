<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="BatiFlow Smart Pharma - Professional Pharmacy Management System for efficient inventory, sales, and branch management.">
    <title>BatiFlow Smart Pharma | Enterprise Pharmacy Management</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Sidebar fixed and other overrides */
        .sidebar {
            background-color: #1e293b;
            min-height: 100vh;
            width: 260px;
            transition: transform 0.3s ease;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 50;
        }

        .sidebar a {
            color: #cbd5e1;
            transition: 0.2s;
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #334155;
            color: white;
        }

        .main-content {
            margin-left: 260px;
            background-color: #f1f5f9;
            min-height: 100vh;
        }

        .card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1rem;
        }

        .btn-primary {
            background-color: #3b82f6;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
        }

        .btn-primary:hover {
            background-color: #2563eb;
        }

        table th,
        table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .notification-badge {
            background-color: #ef4444;
            color: white;
            border-radius: 9999px;
            padding: 0.125rem 0.5rem;
            font-size: 0.75rem;
            margin-left: auto;
        }

        /* Role-based visibility (hidden by default) */
        .role-manager,
        .role-pharmacist,
        .role-storekeeper {
            display: none;
        }

        /* Overlay for mobile sidebar */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 45;
        }

        .overlay.active {
            display: block;
        }
    </style>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>

<body class="font-sans bg-gray-100">
    <div class="overlay" id="overlay"></div>

    <!-- Global Confirmation Modal -->
    <div id="confirmModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-[200] p-4">
        <div class="bg-white/95 backdrop-blur shadow-2xl rounded-3xl p-8 w-full max-w-sm animate-fade-in border border-white/20">
            <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center mb-6 mx-auto">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
            </div>
            <div class="text-center mb-8">
                <h3 class="text-xl font-black text-slate-800 tracking-tight mb-2" id="confirmTitle">Are you sure?</h3>
                <p class="text-sm text-slate-500 font-medium leading-relaxed" id="confirmMessage">This action cannot be undone. Please confirm to proceed.</p>
            </div>
            <div class="flex gap-3">
                <button id="confirmCancelBtn" class="flex-1 px-6 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition-all">Cancel</button>
                <button id="confirmOkBtn" class="flex-1 px-6 py-3 bg-rose-500 text-white font-bold rounded-xl hover:bg-rose-600 shadow-lg shadow-rose-200 transition-all">Confirm</button>
            </div>
        </div>
    </div>