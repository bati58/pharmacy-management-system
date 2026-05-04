<?php
$token = trim($_GET['token'] ?? '');
$email = trim($_GET['email'] ?? '');
$isValidToken = false;
$errorMessage = '';

if (empty($token) || empty($email)) {
    $errorMessage = 'Invalid reset link. Please request a new one.';
} else {
    require_once __DIR__ . '/../../../backend/config/database.php';
    $stmt = $pdo->prepare("
        SELECT id FROM password_resets
        WHERE email = ? AND token = ? AND expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute([$email, $token]);
    $isValidToken = (bool)$stmt->fetch();
    if (!$isValidToken) {
        $errorMessage = 'This reset link is invalid or has expired. Please request a new one.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - PharmaFlow system</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #0f172a; }
        .login-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at 50% 50%, rgba(79,70,229,0.15) 0%, transparent 50%),
                        radial-gradient(circle at 100% 0%, rgba(6,182,212,0.1) 0%, transparent 40%);
            z-index: -1;
        }
        .card {
            background: rgba(255,255,255,0.97);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            animation: slideUp 0.5s cubic-bezier(0.16,1,0.3,1);
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .btn-primary {
            background: linear-gradient(to right, #4f46e5, #4338ca);
            transition: all 0.3s;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(79,70,229,0.4); }
        input:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
        #strengthBar { transition: width 0.3s, background 0.3s; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="login-bg"></div>
    <div class="card w-full max-w-md rounded-2xl overflow-hidden">
        <!-- Header -->
        <div class="bg-slate-900 p-8 text-center">
            <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center text-white mx-auto mb-4 shadow-xl shadow-blue-900/50">
                <i class="fas fa-lock text-2xl"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-white tracking-tight">Reset Password</h2>
            <p class="text-blue-400 text-xs font-bold uppercase tracking-widest mt-1">PharmaFlow sytem</p>
        </div>

        <!-- Body -->
        <div class="p-8">
            <?php if (!$isValidToken): ?>
                <!-- Error state -->
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-4 mb-6 flex items-start gap-3">
                    <i class="fas fa-exclamation-circle mt-0.5 flex-shrink-0"></i>
                    <div>
                        <p class="font-bold text-sm">Link Invalid or Expired</p>
                        <p class="text-sm mt-1"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>
                <a href="forgot-password.php"
                   class="btn-primary w-full text-white font-bold py-3 rounded-xl flex items-center justify-center gap-2">
                    <i class="fas fa-redo text-sm"></i> Request New Reset Link
                </a>
            <?php else: ?>
                <!-- Success state — form -->
                <p class="text-slate-500 text-sm mb-6">Enter your new password below. Must be at least 8 characters including letters and numbers.</p>

                <form id="resetForm" class="space-y-5">
                    <input type="hidden" id="resetToken" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" id="resetEmail" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">

                    <div>
                        <label class="block text-slate-700 text-sm font-bold mb-2">New Password</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="password" id="password"
                                class="w-full pl-12 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-xl transition-all text-slate-900"
                                placeholder="Min. 8 chars, letters + numbers" required minlength="8"
                                oninput="checkStrength(this.value)">
                            <button type="button" onclick="toggleVis('password', this)"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <!-- Strength bar -->
                        <div class="mt-2 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                            <div id="strengthBar" class="h-full rounded-full w-0 bg-red-500"></div>
                        </div>
                        <p id="strengthLabel" class="text-xs text-slate-400 mt-1"></p>
                    </div>

                    <div>
                        <label class="block text-slate-700 text-sm font-bold mb-2">Confirm New Password</label>
                        <div class="relative">
                            <i class="fas fa-lock-open absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="password" id="confirm"
                                class="w-full pl-12 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-xl transition-all text-slate-900"
                                placeholder="Repeat password" required minlength="8">
                            <button type="button" onclick="toggleVis('confirm', this)"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div id="alertBox" class="hidden rounded-xl px-4 py-3 text-sm font-medium"></div>

                    <button type="submit" id="submitBtn"
                        class="btn-primary w-full text-white font-bold py-3 rounded-xl flex items-center justify-center gap-2">
                        <i class="fas fa-check-circle"></i> Reset Password
                    </button>
                </form>
            <?php endif; ?>

            <p class="mt-6 text-center text-sm text-slate-500">
                <a href="login.php" class="text-blue-600 font-semibold hover:underline">
                    <i class="fas fa-arrow-left text-xs"></i> Back to Login
                </a>
            </p>
        </div>
    </div>

    <script src="../../assets/js/api.js?v=<?php echo time(); ?>"></script>
    <?php if ($isValidToken): ?>
    <script>
        function toggleVis(id, btn) {
            const input = document.getElementById(id);
            const isPass = input.type === 'password';
            input.type = isPass ? 'text' : 'password';
            btn.querySelector('i').className = isPass ? 'fas fa-eye-slash' : 'fas fa-eye';
        }

        function checkStrength(val) {
            const bar   = document.getElementById('strengthBar');
            const label = document.getElementById('strengthLabel');
            let score = 0;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/\d/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            const levels = [
                { pct: '0%',   cls: 'bg-slate-300', txt: '' },
                { pct: '25%',  cls: 'bg-red-500',   txt: 'Weak' },
                { pct: '50%',  cls: 'bg-yellow-500', txt: 'Fair' },
                { pct: '75%',  cls: 'bg-blue-500',  txt: 'Good' },
                { pct: '100%', cls: 'bg-green-500', txt: 'Strong' },
            ];
            const l = levels[val.length ? score : 0];
            bar.style.width = l.pct;
            bar.className = 'h-full rounded-full transition-all ' + l.cls;
            label.textContent = l.txt;
        }

        function showAlert(msg, type) {
            const box = document.getElementById('alertBox');
            box.className = `rounded-xl px-4 py-3 text-sm font-medium ${
                type === 'error'
                    ? 'bg-red-50 border border-red-200 text-red-700'
                    : 'bg-green-50 border border-green-200 text-green-700'
            }`;
            box.textContent = msg;
            box.classList.remove('hidden');
        }

        document.getElementById('resetForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const password = document.getElementById('password').value;
            const confirm  = document.getElementById('confirm').value;
            const token    = document.getElementById('resetToken').value;
            const email    = document.getElementById('resetEmail').value;
            const btn      = document.getElementById('submitBtn');

            if (password !== confirm) {
                showAlert('Passwords do not match.', 'error');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Resetting...';

            try {
                const res = await API.confirmReset({ token, email, password });
                if (res.success) {
                    showAlert('Password reset successfully! Redirecting to login...', 'success');
                    setTimeout(() => window.location.href = 'login.php', 2000);
                } else {
                    showAlert(res.message || 'Reset failed.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check-circle"></i> Reset Password';
                }
            } catch (err) {
                showAlert(err.message || 'Something went wrong.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle"></i> Reset Password';
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
