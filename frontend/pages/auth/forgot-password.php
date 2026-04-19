<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - BatiFlow Pharma</title>
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

    <script src="../../assets/js/api.js"></script>
    <script>
        document.getElementById('resetForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            try {
                const data = await API.resetPassword(email);
                alert(data.message || 'Request sent');
                if (data.success) {
                    window.location.href = 'login.php';
                }
            } catch (err) {
                alert(err.message || 'Failed to send reset link.');
            }
        });
    </script>
</body>

</html>