<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - PharmaFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-8 m-4">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Reset your password</h2>
            <p class="text-gray-500 text-sm">Enter your email and we'll send you a link to reset your password.</p>
        </div>

        <div id="messageBox" class="hidden mb-4 p-4 rounded-lg text-sm font-medium"></div>

        <form id="resetForm">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-1">Email</label>
                <input type="email" id="email"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                    placeholder="you@example.com" required>
            </div>
            <button type="submit"
                class="w-full bg-blue-600 text-white font-bold py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                Send reset link
            </button>
        </form>
        <p class="mt-4 text-center text-sm">
            <a href="login.php" class="text-purple-600 hover:underline">Back to sign in</a>
        </p>
    </div>

    <script>
        document.getElementById('resetForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const btn = document.querySelector('button[type="submit"]');
            const messageBox = document.getElementById('messageBox');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            messageBox.className = 'hidden mb-4 p-4 rounded-lg text-sm font-medium';
            messageBox.innerHTML = '';

            try {
                const response = await fetch('../../../backend/index.php/auth/reset-password', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email })
                });
                const data = await response.json();
                
                messageBox.classList.remove('hidden');
                if (data.success) {
                    messageBox.classList.add('bg-green-100', 'text-green-800', 'border', 'border-green-300');
                    messageBox.innerHTML = `<p><i class="fas fa-check-circle mr-2"></i>${data.message}</p>`;
                    document.getElementById('resetForm').reset();
                } else {
                    messageBox.classList.add('bg-red-100', 'text-red-800', 'border', 'border-red-300');
                    messageBox.innerHTML = `<p><i class="fas fa-exclamation-circle mr-2"></i>${data.message}</p>`;
                }
            } catch (err) {
                messageBox.classList.remove('hidden');
                messageBox.classList.add('bg-red-100', 'text-red-800', 'border', 'border-red-300');
                messageBox.innerHTML = `<p><i class="fas fa-exclamation-circle mr-2"></i>Failed to send reset link. Please try again.</p>`;
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Send reset link';
            }
        });
    </script>
</body>

</html>