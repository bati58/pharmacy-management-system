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
    <title>Set Password - PharmaFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                <div class="relative">
                    <input type="password" id="password" class="w-full border rounded px-3 py-2 pr-10" required>
                    <button type="button" onclick="togglePassword('password', 'eyeIcon1')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                        <i class="fas fa-eye" id="eyeIcon1"></i>
                    </button>
                </div>
            </div>
            <div class="mb-3">
                <label>Confirm Password</label>
                <div class="relative">
                    <input type="password" id="confirm" class="w-full border rounded px-3 py-2 pr-10" required>
                    <button type="button" onclick="togglePassword('confirm', 'eyeIcon2')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                        <i class="fas fa-eye" id="eyeIcon2"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="bg-blue-600 text-white w-full py-2 rounded">Activate Account</button>
        </form>
    </div>
    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

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
                const response = await fetch('../../../backend/index.php/auth/activate-invitation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        token,
                        email,
                        password
                    })
                });
                const data = await response.json();
                if (data.success) {
                    alert('Account activated! Please login.');
                    window.location.href = 'login.php';
                } else {
                    alert(data.message);
                }
            } catch (err) {
                alert('Error activating account');
            }
        });
    </script>
</body>

</html>