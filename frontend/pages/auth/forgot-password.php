<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Reset Password - PharmaFlow</title>
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
                        <p class="card-eyebrow">Account recovery</p>
                        <h1 class="card-title" id="recoveryHeading">Reset your password</h1>
                        <p class="card-description">Enter the email address linked to your account and we’ll send you a secure reset link.</p>
                    </div>

                    <div id="messageBox" class="recovery-notice" role="status" aria-live="polite" aria-atomic="true" hidden>
                        <span class="notice-icon" aria-hidden="true"></span>
                        <p id="messageText"></p>
                    </div>

                    <form id="resetForm" class="recovery-form">
                        <div>
                            <label class="field-label" for="email">Email address</label>
                            <div class="field-wrap">
                                <input class="field-input" type="email" id="email" name="email"
                                    placeholder="name@pharmacy.com" autocomplete="email" required>
                                <i class="field-icon fas fa-envelope" aria-hidden="true"></i>
                            </div>
                        </div>

                        <button type="submit" id="submitButton" class="submit-button">
                            <i id="submitIcon" class="fas fa-paper-plane" aria-hidden="true"></i>
                            <span id="submitLabel">Send reset link</span>
                        </button>
                    </form>

                    <a class="text-link back-link" href="login.php">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i>
                        Back to sign in
                    </a>
                </section>

                <p class="security-note"><i class="fas fa-shield-alt" aria-hidden="true"></i> Secure staff account recovery</p>
            </div>
        </main>

        <footer class="site-footer">
            <span>&copy; <?php echo date('Y'); ?> PharmaFlow Systems</span>
            <span class="footer-note">Secure staff portal</span>
        </footer>
    </div>

    <script>
        const form = document.getElementById('resetForm');
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

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            submitButton.disabled = true;
            submitIcon.className = 'fas fa-circle-notch fa-spin';
            submitLabel.textContent = 'Sending link...';
            messageBox.hidden = true;

            try {
                const response = await fetch('../../../backend/index.php/auth/reset-password', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: document.getElementById('email').value })
                });
                const data = await response.json();

                if (data.success) {
                    showMessage('success', data.message || 'Check your inbox for a password reset link.');
                    form.reset();
                } else {
                    showMessage('error', data.message || 'We couldn’t send the reset link. Please try again.');
                }
            } catch (error) {
                showMessage('error', 'Unable to contact the server. Check your connection and try again.');
            } finally {
                submitButton.disabled = false;
                submitIcon.className = 'fas fa-paper-plane';
                submitLabel.textContent = 'Send reset link';
            }
        });
    </script>
</body>

</html>