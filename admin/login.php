<?php
require_once __DIR__ . '/common/config.php';

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: /admin/login.php');
    exit();
}

if (isAdminLoggedIn()) {
    header('Location: /admin/index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['logout'])) {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $result = $conn->query("SELECT * FROM users WHERE (username = '$username' OR email = '$username') AND is_admin = 1");

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['admin_id'] = $user['id'];
                header('Location: /admin/index.php');
                exit();
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'Admin account not found';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - VidZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#ff0000',
                        dark: { bg: '#0f0f0f', card: '#1f1f1f' }
                    }
                }
            }
        }
        document.addEventListener('contextmenu', e => e.preventDefault());
    </script>
    <style>
        * { -webkit-user-select: none; user-select: none; }
        input { -webkit-user-select: text; user-select: text; }
    </style>
</head>
<body class="bg-dark-bg text-white min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <i class="fas fa-shield-alt text-primary text-5xl mb-4"></i>
            <h1 class="text-3xl font-bold mb-2">Admin Login</h1>
            <p class="text-gray-400">VidZone Administration Panel</p>
        </div>

        <div class="bg-dark-card rounded-lg p-8 shadow-xl">
            <?php if ($error): ?>
                <div class="bg-red-900/20 border border-red-500 text-red-500 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium mb-2">Username or Email</label>
                        <input type="text" name="username" required
                            class="w-full bg-dark-bg border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Password</label>
                        <input type="password" name="password" required
                            class="w-full bg-dark-bg border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-primary">
                    </div>

                    <button type="submit" class="w-full bg-primary hover:bg-red-700 py-3 rounded-lg font-semibold transition">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <a href="/index.php" class="text-sm text-gray-400 hover:text-white">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Website
                </a>
            </div>
        </div>
    </div>
</body>
</html>
