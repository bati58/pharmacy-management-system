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
    <title>Login - PharmaFlow System</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a;
            overflow: hidden;
        }

        .login-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 50% 50%, rgba(79, 70, 229, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 100% 0%, rgba(6, 182, 212, 0.1) 0%, transparent 40%);
            z-index: -1;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .input-group:focus-within label {
            color: var(--primary);
        }

        .input-group:focus-within .icon {
            color: var(--primary);
        }

        .btn-primary {
            background: linear-gradient(to right, var(--primary), var(--primary-dark));
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen p-4">
    <div class="login-bg"></div>

    <div class="login-card w-full max-w-[440px] rounded-2xl overflow-hidden">
        <!-- Logo/Header Section -->
        <div class="bg-slate-900 p-8 text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full opacity-10">
                <svg width="100%" height="100%" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <path d="M0 0 L100 0 L100 100 Z" fill="white"></path>
                </svg>
            </div>
            <div class="relative z-10">
                <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center text-white mx-auto mb-4 shadow-xl shadow-blue-900/50">
                    <i class="fas fa-prescription-bottle-alt text-2xl"></i>
                </div>
                <h2 class="text-3xl font-extrabold text-white tracking-tight">PharmaFlow</h2>
                <p class="text-blue-400 text-xs font-bold uppercase tracking-[0.2em] mt-1">Smart Pharma Management</p>
            </div>
        </div>

        <!-- Form Section -->
        <div class="p-8 sm:p-10">
            <form id="loginForm" class="space-y-6">
                <div id="loginError" class="hidden items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm font-medium">
                    <i class="fas fa-circle-xmark text-red-500"></i>
                    <span id="loginErrorMsg"></span>
                </div>

                <div class="input-group">
                    <label class="block text-slate-700 text-sm font-bold mb-2 transition-colors">Email Address</label>
                    <div class="relative">
                        <i class="fas fa-envelope icon absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors"></i>
                        <input type="email" id="email"
                            class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-900"
                            placeholder="you@example.com" required>
                    </div>
                </div>

                <div class="input-group">
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-slate-700 text-sm font-bold transition-colors">Password</label>
                        <a href="forgot-password.php" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Forgot?</a>
                    </div>
                    <div class="relative">
                        <i class="fas fa-lock icon absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors"></i>
                        <input type="password" id="password"
                            class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-900"
                            placeholder="&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;" required>
                    </div>
                </div>

                <button type="submit" id="loginBtn"
                    class="btn-primary w-full text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-500/30 flex items-center justify-center gap-2">
                    <span>Sign In to Dashboard</span>
                    <i class="fas fa-arrow-right text-xs opacity-70"></i>
                </button>
            </form>

            <div class="mt-8 pt-8 border-t border-slate-100 text-center">
                <p class="text-slate-500 text-xs font-medium">
                    &copy; 2026 PharmaFlow Systems. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <script src="../../assets/js/api.js?v=<?php echo time(); ?>"></script>
    <script>
        function showLoginError(msg) {
            const box = document.getElementById('loginError');
            document.getElementById('loginErrorMsg').textContent = msg;
            box.classList.remove('hidden');
            box.classList.add('flex');
        }

        function hideLoginError() {
            const box = document.getElementById('loginError');
            box.classList.add('hidden');
            box.classList.remove('flex');
        }

        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            hideLoginError();
            const btn = document.getElementById('loginBtn');
            const originalText = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Authenticating...';

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
                    btn.innerHTML = '<i class="fas fa-check"></i> Redirecting...';
                    window.location.href = '../dashboard.php';
                } else {
                    showLoginError(data.message || 'Invalid credentials. Please try again.');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (err) {
                showLoginError(err.message || 'Login failed. Please try again.');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    </script>
</body>

</html>
