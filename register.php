<?php
/**
 * Registration page for invited users
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Registration - PharmaFlow</title>
    <link rel="stylesheet" href="frontend/assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --bg-glass: rgba(255, 255, 255, 0.85);
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #eef2ff 0%, #f8fafc 50%, #f1f5f9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .glass-card {
            background: var(--bg-glass);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
        }
        .input-premium {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .input-premium:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            background: white;
        }
        .btn-premium {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            transition: all 0.3s ease;
        }
        .btn-premium:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4);
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body>
    <div id="loader" class="fixed inset-0 bg-white z-[200] flex items-center justify-center">
        <div class="w-12 h-12 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
    </div>

    <div id="content" class="w-full max-w-lg hidden">
        <div class="glass-card rounded-[2.5rem] p-10 md:p-12">
            <div class="text-center mb-10">
                <div class="w-20 h-20 bg-indigo-600 rounded-2xl mx-auto flex items-center justify-center shadow-xl shadow-indigo-200 mb-6 animate-float">
                    <i class="fas fa-prescription-bottle-medical text-3xl text-white"></i>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-2">Complete Your Account</h1>
                <p class="text-slate-500 font-medium" id="inviteSubtitle">Join the PharmaFlow team</p>
            </div>

            <div id="errorState" class="hidden text-center py-8">
                <div class="w-16 h-16 bg-rose-50 text-rose-500 rounded-full mx-auto flex items-center justify-center mb-4">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">Invitation Invalid</h3>
                <p class="text-slate-500 mb-6" id="errorMessage">This link may have expired or already been used.</p>
                <a href="frontend/pages/auth/login.php" class="inline-flex items-center gap-2 text-indigo-600 font-bold hover:text-indigo-700">
                    Go to Login <i class="fas fa-arrow-right text-sm"></i>
                </a>
            </div>

            <form id="registerForm" class="space-y-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 px-1">Email Address</label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="email" id="email" class="w-full pl-11 pr-4 py-4 rounded-2xl input-premium text-slate-500 bg-slate-100 font-medium cursor-not-allowed" readonly>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 px-1">Full Name</label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" id="name" placeholder="John Doe" class="w-full pl-11 pr-4 py-4 rounded-2xl input-premium text-slate-900 font-medium" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 px-1">Set Password</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="password" id="password" placeholder="••••••••" class="w-full pl-11 pr-4 py-4 rounded-2xl input-premium text-slate-900 font-medium" required minlength="6">
                        </div>
                        <p class="text-[10px] text-slate-400 mt-2 px-1 font-medium italic">Must be at least 6 characters long.</p>
                    </div>
                </div>

                <button type="submit" id="submitBtn" class="w-full py-5 btn-premium text-white font-extrabold rounded-2xl shadow-lg flex items-center justify-center gap-3 group">
                    <span>Activate Account</span>
                    <i class="fas fa-chevron-right text-xs group-hover:translate-x-1 transition-transform"></i>
                </button>
            </form>

            <div id="successState" class="hidden text-center py-8">
                <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full mx-auto flex items-center justify-center mb-6 scale-up animate-bounce">
                    <i class="fas fa-check text-3xl"></i>
                </div>
                <h3 class="text-2xl font-extrabold text-slate-900 mb-3">Welcome Aboard!</h3>
                <p class="text-slate-500 font-medium mb-8">Your account has been successfully activated. You can now access the management system.</p>
                <a href="frontend/pages/auth/login.php" class="block w-full py-5 bg-slate-900 text-white font-extrabold rounded-2xl hover:bg-slate-800 transition-all shadow-xl">
                    Proceed to Login
                </a>
            </div>
        </div>
        
        <p class="text-center text-slate-400 text-sm mt-8 font-medium">
            &copy; 2026 PharmaFlow. All rights reserved.
        </p>
    </div>

    <!-- Scripts -->
    <script src="frontend/assets/js/utils.js"></script>
    <script src="frontend/assets/js/api.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const urlParams = new URLSearchParams(window.location.search);
            const token = urlParams.get('token');
            const loader = document.getElementById('loader');
            const content = document.getElementById('content');
            const registerForm = document.getElementById('registerForm');
            const errorState = document.getElementById('errorState');
            const successState = document.getElementById('successState');
            const emailInput = document.getElementById('email');
            const subtitle = document.getElementById('inviteSubtitle');

            if (!token) {
                showError("No invitation token found in the URL.");
                return;
            }

            try {
                // Validate token
                const res = await API.validateInvitation(token);
                if (res.success) {
                    emailInput.value = res.data.email;
                    subtitle.innerText = `Registering as ${res.data.role.replace('_', ' ')}`;
                    loader.style.display = 'none';
                    content.classList.remove('hidden');
                } else {
                    showError(res.message);
                }
            } catch (err) {
                showError(err.message || "Failed to validate invitation.");
            }

            function showError(msg) {
                loader.style.display = 'none';
                content.classList.remove('hidden');
                registerForm.classList.add('hidden');
                errorState.classList.remove('hidden');
                document.getElementById('errorMessage').innerText = msg;
            }

            registerForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const name = document.getElementById('name').value.trim();
                const password = document.getElementById('password').value;
                const submitBtn = document.getElementById('submitBtn');

                if (!name || !password) return;

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-circle-notch animate-spin"></i> Activating...';

                try {
                    const res = await API.activateInvitation({
                        token: token,
                        name: name,
                        password: password
                    });

                    if (res.success) {
                        registerForm.classList.add('hidden');
                        successState.classList.remove('hidden');
                        document.querySelector('h1').innerText = "Success!";
                    } else {
                        alert(res.message);
                    }
                } catch (err) {
                    alert(err.message || "Registration failed.");
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span>Activate Account</span> <i class="fas fa-chevron-right text-xs"></i>';
                }
            });
        });
    </script>
</body>
</html>
