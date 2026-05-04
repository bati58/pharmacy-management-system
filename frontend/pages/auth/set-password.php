<?php
$token = trim($_GET['token'] ?? '');
$isValidToken = false;
$errorMessage = '';

if (empty($token)) {
    $errorMessage = 'Invalid invitation link.';
} else {
    require_once __DIR__ . '/../../../backend/config/database.php';
    $stmt = $pdo->prepare("
        SELECT id
        FROM users
        WHERE invite_token = ?
          AND status = 'pending'
          AND token_expiry IS NOT NULL
          AND token_expiry > NOW()
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $isValidToken = (bool)$stmt->fetch();
    if (!$isValidToken) {
        $errorMessage = 'This invitation link is invalid or expired. Please contact your manager.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activate Account - PharmaFlow system</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a;
        }
        .login-bg {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            background: radial-gradient(circle at 50% 50%, rgba(79, 70, 229, 0.15) 0%, transparent 50%),
                        radial-gradient(circle at 0% 100%, rgba(6, 182, 212, 0.1) 0%, transparent 40%);
            z-index: -1;
        }
        .card {
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(10px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .btn-primary {
            background: linear-gradient(to right, #4f46e5, #4338ca);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
        }
        .input-field {
            width: 100%; padding: 0.75rem 1rem 0.75rem 3rem;
            border: 1px solid #e2e8f0; border-radius: 0.75rem;
            font-size: 0.875rem; outline: none; transition: all 0.2s;
            background: #f8fafc; font-family: inherit;
        }
        .input-field:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen p-4">
    <div class="login-bg"></div>

    <div class="card w-full max-w-[440px] rounded-2xl overflow-hidden">
        <!-- Header -->
        <div class="bg-slate-900 p-8 text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full opacity-10">
                <svg width="100%" height="100%" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <path d="M0 0 L100 0 L100 100 Z" fill="white"></path>
                </svg>
            </div>
            <div class="relative z-10">
                <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center text-white mx-auto mb-4 shadow-xl shadow-blue-900/50">
                    <i class="fas fa-user-shield text-2xl"></i>
                </div>
                <h2 class="text-3xl font-extrabold text-white tracking-tight">Activate Account</h2>
                <p class="text-blue-400 text-xs font-bold uppercase tracking-[0.2em] mt-1">PharmaFlow Smart Pharmacy</p>
            </div>
        </div>

        <div class="p-8 sm:p-10">
            <?php if (!$isValidToken): ?>
                <!-- Invalid Token State -->
                <div class="flex flex-col items-center text-center py-4">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-link-slash text-red-500 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-2">Link Invalid or Expired</h3>
                    <p class="text-slate-500 text-sm mb-6"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
                    <a href="login.php" class="btn-primary text-white font-bold py-3 px-8 rounded-xl flex items-center gap-2">
                        <i class="fas fa-arrow-left text-xs"></i>
                        Back to Sign In
                    </a>
                </div>
            <?php else: ?>
                <!-- Set Password Form -->
                <p class="text-slate-500 text-sm mb-6 text-center">Create a strong password to activate your PharmaFlow account.</p>

                <!-- Alert messages -->
                <div id="setPassAlert" class="hidden items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium mb-4">
                    <i id="setPassAlertIcon" class="text-lg"></i>
                    <span id="setPassAlertMsg"></span>
                </div>

                <form id="setPasswordForm" class="space-y-5">
                    <input type="hidden" id="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

                    <div>
                        <label class="block text-slate-700 text-sm font-bold mb-2">
                            New Password <span class="font-normal text-slate-400">(min. 8 chars, letters + numbers)</span>
                        </label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="password" id="password" class="input-field" placeholder="&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;" required minlength="8">
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-700 text-sm font-bold mb-2">Confirm Password</label>
                        <div class="relative">
                            <i class="fas fa-shield-check absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="password" id="confirm" class="input-field" placeholder="&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;" required minlength="8">
                        </div>
                    </div>

                    <button type="submit" id="activateBtn"
                        class="btn-primary w-full text-white font-bold py-4 rounded-xl shadow-lg shadow-indigo-500/30 flex items-center justify-center gap-2">
                        <span>Activate My Account</span>
                        <i class="fas fa-check-circle text-xs opacity-70"></i>
                    </button>
                </form>
            <?php endif; ?>

            <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                <p class="text-slate-500 text-xs font-medium">
                    &copy; 2026 PharmaFlow Systems. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <script src="../../assets/js/api.js?v=<?php echo time(); ?>"></script>
    <?php if ($isValidToken): ?>
    <script>
        function showSetPassAlert(msg, type = 'error') {
            const box = document.getElementById('setPassAlert');
            const icon = document.getElementById('setPassAlertIcon');
            const msgEl = document.getElementById('setPassAlertMsg');
            msgEl.textContent = msg;
            if (type === 'success') {
                box.className = 'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium mb-4 bg-green-50 border border-green-200 text-green-700';
                icon.className = 'fas fa-circle-check text-green-500 text-lg';
            } else {
                box.className = 'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium mb-4 bg-red-50 border border-red-200 text-red-700';
                icon.className = 'fas fa-circle-xmark text-red-500 text-lg';
            }
        }

        document.getElementById('setPasswordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm').value;
            const btn = document.getElementById('activateBtn');
            const originalText = btn.innerHTML;

            if (password !== confirm) {
                showSetPassAlert('Passwords do not match. Please try again.', 'error');
                return;
            }

            const token = document.getElementById('token').value;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Activating...';

            try {
                const data = await API.activateInvitation({ token, password });
                if (data.success) {
                    showSetPassAlert('Account activated successfully! Redirecting to login...', 'success');
                    btn.innerHTML = '<i class="fas fa-check"></i> Activated!';
                    setTimeout(() => { window.location.href = 'login.php'; }, 2000);
                } else {
                    showSetPassAlert(data.message || 'Account activation failed. Please try again.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (err) {
                showSetPassAlert(err.message || 'An error occurred. Please try again.', 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    </script>
    <?php endif; ?>
</body>

</html>
