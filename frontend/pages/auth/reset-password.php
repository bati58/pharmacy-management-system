<?php
$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';
if (empty($token) || empty($email)) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Create New Password - PharmaFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/auth-recovery.css">
</head>

<body>
    <div class="recovery-layout">
        <main class="recovery-main">
            <div class="recovery-content">
                <header class="brand-lockup">
                    <div class="brand-mark" aria-hidden="true">
                        <i class="fas fa-prescription-bottle-alt"></i>
                    </div>
                    <p class="brand-name">PharmaFlow</p>
                    <p class="brand-caption">Pharmacy Management</p>
                </header>

                <section class="recovery-card" aria-labelledby="recoveryHeading">
                    <div class="card-heading">
                        <p class="card-eyebrow">Account security</p>
                        <h1 class="card-title" id="recoveryHeading">Create a new password</h1>
                        <p class="card-description">Choose a new password for your account. Use at least 6 characters.</p>
                    </div>

                    <div id="messageBox" class="recovery-notice" role="status" aria-live="polite" aria-atomic="true" hidden>
                        <span class="notice-icon" aria-hidden="true"></span>
                        <p id="messageText"></p>
                    </div>

                    <form id="resetConfirmForm" class="recovery-form">
                        <input type="hidden" id="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" id="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">

                        <div>
                            <label class="field-label" for="password">New password</label>
                            <div class="field-wrap">
                                <input class="field-input" type="password" id="password" name="password"
                                    autocomplete="new-password" minlength="6" placeholder="Enter a new password" required>
                                <button class="visibility-toggle" type="button" data-toggle-password="password"
                                    aria-label="Show new password" aria-pressed="false" title="Show password">
                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            <p class="field-hint">At least 6 characters.</p>
                        </div>

                        <div>
                            <label class="field-label" for="confirmPassword">Confirm new password</label>
                            <div class="field-wrap">
                                <input class="field-input" type="password" id="confirmPassword" name="confirmPassword"
                                    autocomplete="new-password" minlength="6" placeholder="Re-enter your new password" required>
                                <button class="visibility-toggle" type="button" data-toggle-password="confirmPassword"
                                    aria-label="Show confirmation password" aria-pressed="false" title="Show password">
                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" id="submitButton" class="submit-button">
                            <i id="submitIcon" class="fas fa-key" aria-hidden="true"></i>
                            <span id="submitLabel">Update password</span>
                        </button>
                    </form>

                    <a class="text-link back-link" href="login.php">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i>
                        Back to sign in
                    </a>
                </section>

                <p class="security-note"><i class="fas fa-shield-alt" aria-hidden="true"></i> Your account credentials stay private</p>
            </div>
        </main>

        <footer class="site-footer">
            <span>&copy; <?php echo date('Y'); ?> PharmaFlow Systems</span>
            <span class="footer-note">Secure staff portal</span>
        </footer>
    </div>

    <script>
        const form = document.getElementById('resetConfirmForm');
        const submitButton = document.getElementById('submitButton');
        const submitIcon = document.getElementById('submitIcon');
        const submitLabel = document.getElementById('submitLabel');
        const messageBox = document.getElementById('messageBox');
        const messageText = document.getElementById('messageText');

        function showMessage(state, message) {
            messageBox.dataset.state = state;
            messageBox.querySelector('.notice-icon').className = state === 'success'
                ? 'notice-icon fas fa-check-circle'
                : 'notice-icon fas fa-exclamation-circle';
            messageText.textContent = message;
            messageBox.hidden = false;
        }

        document.querySelectorAll('[data-toggle-password]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.togglePassword);
                const icon = button.querySelector('i');
                const isVisible = input.type === 'password';
                input.type = isVisible ? 'text' : 'password';
                button.setAttribute('aria-pressed', String(isVisible));
                button.setAttribute('aria-label', `${isVisible ? 'Hide' : 'Show'} ${button.dataset.togglePassword === 'password' ? 'new' : 'confirmation'} password`);
                button.title = `${isVisible ? 'Hide' : 'Show'} password`;
                icon.className = isVisible ? 'fas fa-eye-slash' : 'fas fa-eye';
            });
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (password.length < 6) {
                showMessage('error', 'Password must be at least 6 characters.');
                return;
            }

            if (password !== confirmPassword) {
                showMessage('error', 'The passwords do not match. Please check and try again.');
                return;
            }

            submitButton.disabled = true;
            submitIcon.className = 'fas fa-circle-notch fa-spin';
            submitLabel.textContent = 'Updating password...';
            messageBox.hidden = true;

            try {
                const response = await fetch('../../../backend/index.php/auth/reset-password-confirm', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        email: document.getElementById('email').value,
                        token: document.getElementById('token').value,
                        password
                    })
                });
                const data = await response.json();

                if (data.success) {
                    showMessage('success', data.message || 'Your password has been updated. You can now sign in.');
                    form.hidden = true;
                    window.setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2500);
                    return;
                }

                showMessage('error', data.message || 'We couldn’t update your password. Please try again.');
            } catch (error) {
                showMessage('error', 'Unable to contact the server. Check your connection and try again.');
            }

            submitButton.disabled = false;
            submitIcon.className = 'fas fa-key';
            submitLabel.textContent = 'Update password';
        });
    </script>
</body>

</html>
