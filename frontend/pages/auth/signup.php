<?php
require_once __DIR__ . '/../../includes/init_session.php';
if (isset($_SESSION['user_id'])) {
    header('Location: ../dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation Required - PharmaFlow System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a;
            overflow-y: auto;
        }
        .login-bg {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at 50% 50%, rgba(79, 70, 229, 0.15) 0%, transparent 50%);
            z-index: -1;
        }
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen p-4">
    <div class="login-bg"></div>
    <div class="card w-full max-w-[400px] rounded-2xl overflow-hidden shadow-2xl">
        <div class="bg-slate-900 p-7 text-center">
            <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center text-white mx-auto mb-4">
                <i class="fas fa-user-lock text-xl"></i>
            </div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Access Restricted</h2>
            <p class="text-blue-400 text-[10px] font-bold uppercase tracking-widest mt-1">PharmaFlow System</p>
        </div>
        <div class="p-8 text-center">
            <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-4 text-sm font-medium leading-relaxed">
                <i class="fas fa-circle-info text-amber-500 mb-2 block text-lg"></i>
                Public registration is disabled. Please contact your manager to receive a secure email invitation link.
            </div>
            <p class="mt-6">
                <a href="login.php" class="text-sm font-semibold text-blue-600 hover:text-blue-700 flex items-center justify-center gap-2">
                    <i class="fas fa-arrow-left text-xs"></i> Back to sign in
                </a>
            </p>
            <div class="mt-8 pt-6 border-t border-slate-100">
                <p class="text-slate-400 text-[10px] font-medium">&copy; 2026 PharmaFlow Systems.</p>
            </div>
        </div>
    </div>
</body>

</html>
