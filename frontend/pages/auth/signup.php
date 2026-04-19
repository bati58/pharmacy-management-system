<?php
require_once __DIR__ . '/../../includes/init_session.php';
if (isset($_SESSION['user_id'])) {
    header('Location: ../dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - BatiFlow Pharma</title>
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
            <h2 class="text-2xl font-bold text-gray-800">Create your account</h2>
            <p class="text-gray-500">Join BatiFlow Pharma</p>
        </div>
        <form id="signupForm">
            <div class="mb-3">
                <label class="block text-gray-700 text-sm font-bold mb-1">Full Name</label>
                <input type="text" id="name"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                    required>
            </div>
            <div class="mb-3">
                <label class="block text-gray-700 text-sm font-bold mb-1">Email</label>
                <input type="email" id="email"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                    required>
            </div>
            <div class="mb-3">
                <label class="block text-gray-700 text-sm font-bold mb-1">Password (min. 6 characters)</label>
                <input type="password" id="password"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                    minlength="6" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-1">Confirm Password</label>
                <input type="password" id="confirm"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                    required>
            </div>
            <button type="submit"
                class="w-full bg-green-600 text-white font-bold py-2 rounded-lg hover:bg-green-700 transition duration-200">
                Create Account
            </button>
        </form>
        <p class="mt-4 text-center text-sm">
            Already have an account? <a href="login.php" class="text-purple-600 hover:underline">Sign in</a>
        </p>
    </div>

    <script src="../../assets/js/api.js"></script>
    <script>
        document.getElementById('signupForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm').value;
            if (password !== confirm) {
                alert('Passwords do not match');
                return;
            }
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            try {
                const data = await API.register({ name, email, password });
                if (data.success) {
                    alert(data.message || 'Account created! Please wait for manager approval.');
                    window.location.href = 'login.php';
                } else {
                    alert(data.message || 'Signup failed');
                }
            } catch (err) {
                alert(err.message || 'Signup failed');
            }
        });
    </script>
</body>

</html>