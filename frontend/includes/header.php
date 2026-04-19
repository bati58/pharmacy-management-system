<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>BatiFlow Smart Pharma</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
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
            margin-left: 0;
            background-color: #f1f5f9;
            min-height: 100vh;
            width: 100%;
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

        /* Mobile menu button (visible only on small screens) */
        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 60;
            background: #3b82f6;
            color: white;
            padding: 0.5rem;
            border-radius: 0.375rem;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
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
</head>

<body class="font-sans bg-gray-100">
    <div class="overlay" id="overlay"></div>
    <div class="mobile-menu-btn" id="mobileMenuBtn">
        <i class="fas fa-bars text-xl"></i>
    </div>