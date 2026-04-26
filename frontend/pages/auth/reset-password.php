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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Password - BatiFlow Pharma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        /* Remove default eye icon in Edge browser */
        input::-ms-reveal,
        input::-ms-clear {
            display: none;
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-8 m-4">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Create New Password</h2>
            <p class="text-gray-500 text-sm mt-1">Please enter your new password below.</p>
        </div>

        <div id="messageBox" class="hidden mb-4 p-4 rounded-lg text-sm font-medium"></div>

        <form id="resetConfirmForm">
            <input type="hidden" id="token" value="<?php echo htmlspecialchars($token); ?>">
            <input type="hidden" id="email" value="<?php echo htmlspecialchars($email); ?>">
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-1">New Password</label>
                <div class="relative">
                    <input type="password" id="password"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 pr-10"
                        placeholder="••••••••" required>
                    <button type="button" onclick="togglePassword('password', 'eyeIcon1')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                        <i class="fas fa-eye" id="eyeIcon1"></i>
                    </button>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-1">Confirm New Password</label>
                <div class="relative">
                    <input type="password" id="confirmPassword"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 pr-10"
                        placeholder="••••••••" required>
                    <button type="button" onclick="togglePassword('confirmPassword', 'eyeIcon2')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                        <i class="fas fa-eye" id="eyeIcon2"></i>
                    </button>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-blue-600 text-white font-bold py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                Update Password
            </button>
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

        document.getElementById('resetConfirmForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const token = document.getElementById('token').value;
            const email = document.getElementById('email').value;
            
            const btn = document.querySelector('button[type="submit"]');
            const messageBox = document.getElementById('messageBox');
            
            if (password.length < 6) {
                messageBox.classList.remove('hidden');
                messageBox.className = 'mb-4 p-4 rounded-lg text-sm font-medium bg-red-100 text-red-800 border border-red-300';
                messageBox.innerHTML = `<p><i class="fas fa-exclamation-circle mr-2"></i>Password must be at least 6 characters.</p>`;
                return;
            }
            
            if (password !== confirmPassword) {
                messageBox.classList.remove('hidden');
                messageBox.className = 'mb-4 p-4 rounded-lg text-sm font-medium bg-red-100 text-red-800 border border-red-300';
                messageBox.innerHTML = `<p><i class="fas fa-exclamation-circle mr-2"></i>Passwords do not match.</p>`;
                return;
            }
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            messageBox.classList.add('hidden');

            try {
                const response = await fetch('../../../backend/index.php/auth/reset-password-confirm', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, token, password })
                });
                const data = await response.json();
                
                messageBox.classList.remove('hidden');
                if (data.success) {
                    messageBox.className = 'mb-4 p-4 rounded-lg text-sm font-medium bg-green-100 text-green-800 border border-green-300';
                    messageBox.innerHTML = `<p><i class="fas fa-check-circle mr-2"></i>${data.message}</p>`;
                    document.getElementById('resetConfirmForm').style.display = 'none';
                    
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2500);
                } else {
                    messageBox.className = 'mb-4 p-4 rounded-lg text-sm font-medium bg-red-100 text-red-800 border border-red-300';
                    messageBox.innerHTML = `<p><i class="fas fa-exclamation-circle mr-2"></i>${data.message}</p>`;
                }
            } catch (err) {
                messageBox.classList.remove('hidden');
                messageBox.className = 'mb-4 p-4 rounded-lg text-sm font-medium bg-red-100 text-red-800 border border-red-300';
                messageBox.innerHTML = `<p><i class="fas fa-exclamation-circle mr-2"></i>Failed to update password. Please try again.</p>`;
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Update Password';
            }
        });
    </script>
</body>

</html>
