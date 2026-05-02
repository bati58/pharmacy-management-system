<?php
session_start();
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
            background-color: #0f172a;
            background-image: 
                linear-gradient(135deg, rgba(79, 70, 229, 0.85) 0%, rgba(124, 58, 237, 0.85) 100%),
                url('../../assets/login-bg.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: 'Inter', sans-serif;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(20px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .animate-scale-in {
            animation: scaleIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Remove default eye icon in Edge browser */
        input::-ms-reveal,
        input::-ms-clear {
            display: none;
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen p-4">
    <!-- Login Card -->
    <div class="glass-card rounded-[2.5rem] shadow-2xl w-full max-w-md p-10 m-4 animate-scale-in relative overflow-hidden">
        <!-- Decoration -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-purple-100 rounded-full blur-3xl opacity-50"></div>

        <div class="text-center mb-10 relative">
            <div class="w-16 h-16 bg-gradient-to-tr from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-indigo-200">
                <i class="fas fa-hand-holding-medical text-white text-2xl"></i>
            </div>
            <h2 class="text-4xl font-black text-slate-800 tracking-tight">Welcome Back</h2>
            <p class="text-slate-400 font-medium mt-1">BatiFlow Pharma Ecosystem</p>
        </div>

        <form id="loginForm" class="space-y-6 relative">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 px-1">Email Address</label>
                <div class="relative group">
                    <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors"></i>
                    <input type="email" id="email"
                        class="w-full pl-12 pr-4 py-4 bg-slate-50 border-0 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 transition-all font-medium text-slate-700"
                        placeholder="you@gmail.com" required>
                </div>
            </div>
            <div>
                <div class="flex justify-between items-center mb-2 px-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">Password</label>
                    <a href="forgot-password.php" class="text-[10px] font-bold text-indigo-500 hover:text-indigo-600 uppercase tracking-wider">Forgot?</a>
                </div>
                <div class="relative group">
                    <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors"></i>
                    <input type="password" id="password"
                        class="w-full pl-12 pr-4 py-4 bg-slate-50 border-0 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 transition-all font-medium text-slate-700"
                        placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" id="submitBtn"
                class="w-full bg-indigo-600 text-white font-black py-4 rounded-2xl hover:bg-indigo-700 shadow-xl shadow-indigo-200 transition-all transform hover:-translate-y-1 active:scale-[0.98] flex items-center justify-center gap-2">
                <span>Sign Into Portal</span>
                <i class="fas fa-arrow-right text-xs opacity-50"></i>
            </button>
        </form>

        <div class="mt-8 text-center text-[10px] font-bold text-slate-300 uppercase tracking-widest">
            &copy; 2026 BatiFlow Pharma Systems. Enterprise Edition.
        </div>
    </div>

    <!-- Success Overlay -->
    <div id="successOverlay" class="fixed inset-0 bg-white/80 backdrop-blur-xl z-[100] hidden items-center justify-center">
        <div class="text-center animate-scale-in">
            <div class="w-24 h-24 bg-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-emerald-200">
                <i class="fas fa-check text-white text-4xl"></i>
            </div>
            <h3 class="text-4xl font-black text-slate-800 tracking-tight mb-2">Login Successful</h3>
            <p class="text-slate-500 font-medium" id="welcomeMessage">Preparing your personal dashboard...</p>
        </div>
    </div>

    <script src="../../assets/js/utils.js"></script>
    <script>

        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            // Loading state
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Authenticating...';

            try {
                const response = await fetch('../../../backend/index.php/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email,
                        password
                    }),
                    credentials: 'include'
                });

                const data = await response.json();

                if (data.success) {
                    // Store user info
                    localStorage.setItem('user', JSON.stringify({
                        name: data.data.name,
                        role: data.data.role,
                        branch_id: data.data.branch_id
                    }));

                    // Show Premium Success Overlay
                    document.getElementById('welcomeMessage').innerText = `Welcome back, ${data.data.name}!`;
                    const overlay = document.getElementById('successOverlay');
                    overlay.classList.remove('hidden');
                    overlay.classList.add('flex');

                    setTimeout(() => {
                        window.location.href = '../dashboard.php';
                    }, 2000);
                } else {
                    showToast(data.message, 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<span>Sign Into Portal</span> <i class="fas fa-arrow-right text-xs opacity-50"></i>';
                }
            } catch (err) {
                showToast('Network error. Please try again.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<span>Sign Into Portal</span> <i class="fas fa-arrow-right text-xs opacity-50"></i>';
            }
        });
    </script>
</body>

</html>