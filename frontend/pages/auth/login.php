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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Login - PharmaFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            color-scheme: light;
            --page: #eef2f5;
            --surface: #ffffff;
            --ink: #182230;
            --muted: #667085;
            --line: #d7dee7;
            --brand: #1769d2;
            --brand-hover: #1258b5;
            --success: #0d9b77;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            min-height: 100svh;
            margin: 0;
            color: var(--ink);
            background: var(--page);
            font-family: 'Inter', sans-serif;
        }

        .auth-layout {
            min-height: 100vh;
            min-height: 100svh;
            display: flex;
            flex-direction: column;
        }

        .auth-main {
            flex: 1;
            display: grid;
            place-items: center;
            padding: 48px 20px;
        }

        .auth-content {
            width: min(100%, 480px);
            animation: enter 300ms ease-out both;
        }

        .brand-lockup {
            display: flex;
            align-items: center;
            flex-direction: column;
            margin-bottom: 22px;
            text-align: center;
        }

        .brand-mark {
            width: 62px;
            height: 62px;
            display: grid;
            place-items: center;
            margin-bottom: 12px;
            color: var(--brand);
            background: #e6effb;
            border: 1px solid #d5e4f7;
            border-radius: 14px;
            font-size: 28px;
        }

        .brand-name {
            margin: 0;
            color: #263445;
            font-size: 30px;
            font-weight: 700;
            line-height: 1.2;
        }

        .brand-caption {
            margin: 5px 0 0;
            color: #687789;
            font-size: 14px;
            font-weight: 500;
        }

        .login-card {
            padding: 32px 30px 28px;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: 0 10px 28px rgba(31, 48, 69, 0.08);
        }

        .card-title {
            margin: 0 0 26px;
            color: #354254;
            font-size: 18px;
            font-weight: 600;
            text-align: center;
        }

        .login-form { display: grid; gap: 18px; }

        .field-label {
            display: block;
            margin-bottom: 7px;
            color: #49586b;
            font-size: 13px;
            font-weight: 600;
        }

        .password-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .field-wrap { position: relative; }

        .field-input {
            width: 100%;
            min-height: 50px;
            padding: 12px 44px 12px 14px;
            color: var(--ink);
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 6px;
            outline: none;
            font: inherit;
            font-size: 15px;
            transition: border-color 150ms ease, box-shadow 150ms ease;
        }

        .field-input::placeholder { color: #98a2b3; }

        .field-input:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(23, 105, 210, 0.13);
        }

        .field-icon {
            position: absolute;
            top: 50%;
            right: 15px;
            color: #7b8795;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .forgot-link {
            color: var(--brand);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .forgot-link:hover { text-decoration: underline; }

        .submit-button {
            width: 100%;
            min-height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            margin-top: 4px;
            padding: 12px 18px;
            color: #fff;
            background: var(--brand);
            border: 1px solid var(--brand);
            border-radius: 6px;
            font: inherit;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 150ms ease, transform 150ms ease;
        }

        .submit-button:hover { background: var(--brand-hover); }
        .submit-button:active { transform: translateY(1px); }
        .submit-button:disabled { opacity: 0.7; cursor: wait; }

        .access-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            margin: 20px 0 0;
            color: #7b8795;
            font-size: 12px;
        }

        .access-note i { color: var(--success); }

        .site-footer {
            min-height: 54px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 12px 24px;
            color: #7b8795;
            background: #fff;
            border-top: 1px solid #e1e6ec;
            font-size: 12px;
        }

        .success-overlay {
            position: fixed;
            inset: 0;
            z-index: 100;
            display: grid;
            place-items: center;
            padding: 24px;
            background: rgba(255, 255, 255, 0.96);
            text-align: center;
        }

        .success-overlay[hidden] { display: none; }

        .success-icon {
            width: 68px;
            height: 68px;
            display: grid;
            place-items: center;
            margin: 0 auto 18px;
            color: #fff;
            background: var(--success);
            border-radius: 50%;
            font-size: 26px;
        }

        .success-overlay h2 { margin: 0 0 8px; font-size: 25px; }
        .success-overlay p { margin: 0; color: var(--muted); font-size: 14px; }

        @keyframes enter {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 520px) {
            .auth-main { padding: 28px 16px; }
            .brand-lockup { margin-bottom: 18px; }
            .brand-mark { width: 54px; height: 54px; font-size: 24px; }
            .brand-name { font-size: 26px; }
            .login-card { padding: 26px 20px 22px; }
            .site-footer { justify-content: center; text-align: center; }
            .footer-note { display: none; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
        }

        input::-ms-reveal,
        input::-ms-clear { display: none; }
    </style>
</head>

<body>
    <div class="auth-layout">
        <main class="auth-main">
            <div class="auth-content">
                <header class="brand-lockup">
                    <div class="brand-mark" aria-hidden="true">
                        <i class="fas fa-prescription-bottle-alt"></i>
                    </div>
                    <h1 class="brand-name">PharmaFlow</h1>
                    <p class="brand-caption">Pharmacy Management</p>
                </header>

                <section class="login-card" aria-labelledby="loginHeading">
                    <h2 class="card-title" id="loginHeading">Staff sign in</h2>
                    <form id="loginForm" class="login-form">
                        <div>
                            <label class="field-label" for="email">Email address</label>
                            <div class="field-wrap">
                                <input class="field-input" type="email" id="email" name="email"
                                    placeholder="name@pharmacy.com" autocomplete="username" required>
                                <i class="field-icon fas fa-envelope" aria-hidden="true"></i>
                            </div>
                        </div>

                        <div>
                            <div class="password-label-row">
                                <label class="field-label" for="password">Password</label>
                                <a class="forgot-link" href="forgot-password.php">Forgot password?</a>
                            </div>
                            <div class="field-wrap">
                                <input class="field-input" type="password" id="password" name="password"
                                    placeholder="Enter your password" autocomplete="current-password" required>
                                <i class="field-icon fas fa-lock" aria-hidden="true"></i>
                            </div>
                        </div>

                        <button type="submit" id="submitBtn" class="submit-button">
                            <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                            <span>Sign in</span>
                        </button>
                    </form>
                    <p class="access-note"><i class="fas fa-shield-alt" aria-hidden="true"></i> Authorized staff access</p>
                </section>
            </div>
        </main>

        <footer class="site-footer">
            <span>&copy; <?php echo date('Y'); ?> PharmaFlow Systems</span>
            <span class="footer-note">Secure staff portal</span>
        </footer>
    </div>

    <div id="successOverlay" class="success-overlay" role="status" aria-live="polite" hidden>
        <div>
            <div class="success-icon"><i class="fas fa-check" aria-hidden="true"></i></div>
            <h2>Sign in successful</h2>
            <p id="welcomeMessage">Preparing your dashboard...</p>
        </div>
    </div>

    <script src="../../assets/js/utils.js"></script>
    <script>

        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i><span>Signing in...</span>';

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
                    localStorage.setItem('user', JSON.stringify({
                        name: data.data.name,
                        role: data.data.role,
                        branch_id: data.data.branch_id
                    }));

                    document.getElementById('welcomeMessage').innerText = `Welcome back, ${data.data.name}!`;
                    const overlay = document.getElementById('successOverlay');
                    overlay.hidden = false;

                    setTimeout(() => {
                        window.location.href = '../dashboard.php';
                    }, 2000);
                } else {
                    showToast(data.message, 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-sign-in-alt" aria-hidden="true"></i><span>Sign in</span>';
                }
            } catch (err) {
                showToast('Network error. Please try again.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-sign-in-alt" aria-hidden="true"></i><span>Sign in</span>';
            }
        });
    </script>
</body>

</html>