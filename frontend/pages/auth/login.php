<?php
require_once __DIR__ . '/../../includes/init_session.php';
// If already logged in, redirect to dashboard
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
    <title>Login - BatiFlow Pharma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-8 m-4">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800">BatiFlow</h2>
            <p class="text-gray-500">Pharmacy Management System</p>
        </div>
        <form id="loginForm">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-3 top-3 text-gray-400"></i>
                    <input type="email" id="email"
                        class="w-full pl-10 pr-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                        placeholder="you@example.com" required>
                </div>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-3 text-gray-400"></i>
                    <input type="password" id="password"
                        class="w-full pl-10 pr-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                        placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit"
                class="w-full bg-purple-600 text-white font-bold py-2 rounded-lg hover:bg-purple-700 transition duration-200">
                Sign In
            </button>
        </form>
        <div class="mt-6 text-center text-sm">
            <a href="forgot-password.php" class="text-purple-600 hover:underline">Forgot password?</a>
        </div>
        <div class="mt-4 text-center text-xs text-gray-500">
            &copy; 2026 BatiFlow Pharma. All rights reserved.
        </div>
    </div>

    <script src="../../assets/js/api.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            try {
                const data = await API.login(email, password);
                if (data.success && data.data) {
                    localStorage.setItem('user', JSON.stringify({
                        name: data.data.name,
                        role: data.data.role,
                        branch_id: data.data.branch_id
                    }));
                    window.location.href = '../dashboard.php';
                } else {
                    alert(data.message || 'Login failed');
                }
            } catch (err) {
                alert(err.message || 'Login failed. Please try again.');
            }
        });
    </script>
</body>

</html>