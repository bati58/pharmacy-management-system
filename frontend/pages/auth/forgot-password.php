<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - PharmaFlow System</title>
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
            overflow-y: auto;
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

    <div class="login-card w-full max-w-[400px] rounded-2xl overflow-hidden shadow-2xl">
        <!-- Header -->
        <div class="bg-slate-900 p-7 text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full opacity-10">
                <svg width="100%" height="100%" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <path d="M0 0 L100 0 L100 100 Z" fill="white"></path>
                </svg>
            </div>
            <div class="relative z-10">
                <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center text-white mx-auto mb-4 shadow-xl shadow-blue-900/50">
                    <i class="fas fa-unlock-alt text-xl"></i>
                </div>
                <h2 class="text-2xl font-extrabold text-white tracking-tight">Reset Password</h2>
                <p class="text-blue-400 text-[10px] font-bold uppercase tracking-[0.2em] mt-1">PharmaFlow System</p>
            </div>
        </div>

        <!-- Form Section -->
        <div class="p-8">
            <p class="text-slate-500 text-xs mb-6 text-center leading-relaxed">Enter your email address and we'll send you a secure link to reset your password.</p>

            <!-- Alert messages -->
            <div id="resetAlert" class="hidden items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium mb-4">
                <i id="resetAlertIcon" class="text-lg"></i>
                <span id="resetAlertMsg"></span>
            </div>

            <form id="resetForm" class="space-y-5">
                <div class="input-group">
                    <label class="block text-slate-700 text-xs font-bold mb-2 transition-colors uppercase tracking-wider">Email Address</label>
                    <div class="relative">
                        <i class="fas fa-envelope icon absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors"></i>
                        <input type="email" id="email"
                            class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm text-slate-900"
                            placeholder="you@example.com" required>
                    </div>
                </div>

                <button type="submit" id="resetBtn"
                    class="btn-primary w-full text-white font-bold py-3.5 rounded-xl shadow-lg shadow-indigo-500/30 flex items-center justify-center gap-2 text-sm">
                    <span>Send Reset Link</span>
                    <i class="fas fa-paper-plane text-[10px] opacity-70"></i>
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="login.php" class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center justify-center gap-2">
                    <i class="fas fa-arrow-left text-[10px]"></i> Back to Sign In
                </a>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                <p class="text-slate-400 text-[10px] font-medium">
                    &copy; 2026 PharmaFlow Systems. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <script src="../../assets/js/api.js?v=<?php echo time(); ?>"></script>
    <script>
        function showResetAlert(msg, type = 'error') {
            const box = document.getElementById('resetAlert');
            const icon = document.getElementById('resetAlertIcon');
            const msgEl = document.getElementById('resetAlertMsg');
            msgEl.textContent = msg;
            if (type === 'success') {
                box.className = 'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium mb-4 bg-green-50 border border-green-200 text-green-700';
                icon.className = 'fas fa-circle-check text-green-500 text-lg';
            } else {
                box.className = 'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium mb-4 bg-red-50 border border-red-200 text-red-700';
                icon.className = 'fas fa-circle-xmark text-red-500 text-lg';
            }
        }

        document.getElementById('resetForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('resetBtn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Sending...';

            const email = document.getElementById('email').value;
            try {
                const data = await API.resetPassword(email);
                if (data.success) {
                    showResetAlert(data.message || 'Reset link sent! Please check your inbox.', 'success');
                    btn.innerHTML = '<i class="fas fa-check"></i> Email Sent';
                } else {
                    showResetAlert(data.message || 'Request could not be processed.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (err) {
                showResetAlert(err.message || 'Failed to send reset link. Please try again.', 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    </script>
</body>

</html>
