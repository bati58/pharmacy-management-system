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
<html>

<head>
    <title>Set Password - BatiFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded shadow w-96">
        <h2 class="text-2xl font-bold mb-4">Set Your Password</h2>
        <?php if (!$isValidToken): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 rounded px-4 py-3 mb-4">
                <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <a href="login.php" class="text-blue-600 hover:underline">Back to login</a>
        <?php else: ?>
            <p class="mb-4 text-gray-600">Create a strong password to activate your account.</p>
            <form id="setPasswordForm">
                <input type="hidden" id="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="mb-3">
                    <label>New Password (min. 8 chars, letters + numbers)</label>
                    <input type="password" id="password" class="w-full border rounded px-3 py-2" required minlength="8">
                </div>
                <div class="mb-3">
                    <label>Confirm Password</label>
                    <input type="password" id="confirm" class="w-full border rounded px-3 py-2" required minlength="8">
                </div>
                <button type="submit" class="bg-blue-600 text-white w-full py-2 rounded">Activate Account</button>
            </form>
        <?php endif; ?>
    </div>
    <script src="../../assets/js/api.js"></script>
    <?php if ($isValidToken): ?>
    <script>
        document.getElementById('setPasswordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm').value;
            if (password !== confirm) {
                alert('Passwords do not match');
                return;
            }
            const token = document.getElementById('token').value;
            try {
                const data = await API.activateInvitation({ token, password });
                if (data.success) {
                    alert('Account activated! Please login.');
                    window.location.href = 'login.php';
                } else {
                    alert(data.message || 'Activation failed');
                }
            } catch (err) {
                alert(err.message || 'Error activating account');
            }
        });
    </script>
    <?php endif; ?>
</body>

</html>