<?php
$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';
if (empty($token) || empty($email)) {
    header('Location: login.php');
    exit;
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
        <p class="mb-4">Email: <?php echo htmlspecialchars($email); ?></p>
        <form id="setPasswordForm">
            <input type="hidden" id="token" value="<?php echo htmlspecialchars($token); ?>">
            <input type="hidden" id="email" value="<?php echo htmlspecialchars($email); ?>">
            <div class="mb-3">
                <label>Password (min. 6 chars)</label>
                <input type="password" id="password" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="mb-3">
                <label>Confirm Password</label>
                <input type="password" id="confirm" class="w-full border rounded px-3 py-2" required>
            </div>
            <button type="submit" class="bg-blue-600 text-white w-full py-2 rounded">Activate Account</button>
        </form>
    </div>
    <script src="../../assets/js/api.js"></script>
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
            const email = document.getElementById('email').value;
            try {
                const data = await API.activateInvitation({ token, email, password });
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
</body>

</html>