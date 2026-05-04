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
    <title>Invitation Required - BatiFlow Pharma</title>
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
            <h2 class="text-2xl font-bold text-gray-800">Invitation Required</h2>
            <p class="text-gray-500">Public registration is disabled.</p>
        </div>
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg px-4 py-3">
            Contact your Manager to receive an email invitation link and complete account setup.
        </div>
        <p class="mt-4 text-center text-sm">
            <a href="login.php" class="text-purple-600 hover:underline">Back to sign in</a>
        </p>
    </div>
</body>

</html>