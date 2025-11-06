<?php
require_once __DIR__ . '/common/config.php';
require_once __DIR__ . '/common/functions.php';

if (isLoggedIn()) {
    header('Location: /home.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $result = $conn->query("SELECT * FROM users WHERE username = '$username' OR email = '$username'");

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                header('Location: /home.php');
                exit();
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'User not found';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sign In - VidZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#ff0000',
                        dark: { bg: '#0f0f0f', card: '#1f1f1f', hover: '#2f2f2f' }
                    }
                }
            }
        }
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('gesturestart', e => e.preventDefault());
    </script>
    <style>
        * {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        input, textarea {
            -webkit-user-select: text;
            -moz-user-select: text;
            user-select: text;
        }
        body { touch-action: pan-x pan-y; }
    </style>
</head>
<body class="bg-dark-bg text-white min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="/index.php" class="inline-flex items-center gap-2 text-primary font-bold text-3xl mb-6">
                <i class="fas fa-play-circle"></i>
                <span>VidZone</span>
            </a>
            <h2 class="text-2xl font-bold">Sign In</h2>
        </div>

        <div class="bg-dark-card rounded-lg p-8 shadow-xl">
            <?php if ($error): ?>
                <div class="bg-red-900/20 border border-red-500 text-red-500 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login.php">
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium mb-2">Username or Email</label>
                        <input type="text" name="username" required
                            class="w-full bg-dark-bg border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-primary"
                            placeholder="Enter your username or email">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Password</label>
                        <input type="password" name="password" required
                            class="w-full bg-dark-bg border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-primary"
                            placeholder="Enter your password">
                    </div>

                    <button type="submit" class="w-full bg-primary hover:bg-red-700 py-3 rounded-lg font-semibold transition">
                        Sign In
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center text-gray-400">
                <p>Don't have an account?
                    <a href="/signup.php" class="text-primary hover:underline">Sign Up</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
