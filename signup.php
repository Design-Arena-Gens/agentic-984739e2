<?php
require_once __DIR__ . '/common/config.php';
require_once __DIR__ . '/common/functions.php';

if (isLoggedIn()) {
    header('Location: /home.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $fullName = sanitize($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($fullName)) {
        $error = 'Please fill in all fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        // Check if username exists
        $result = $conn->query("SELECT id FROM users WHERE username = '$username'");
        if ($result->num_rows > 0) {
            $error = 'Username already exists';
        } else {
            // Check if email exists
            $result = $conn->query("SELECT id FROM users WHERE email = '$email'");
            if ($result->num_rows > 0) {
                $error = 'Email already exists';
            } else {
                // Create user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $query = "INSERT INTO users (username, email, password, full_name) VALUES ('$username', '$email', '$hashedPassword', '$fullName')";

                if ($conn->query($query)) {
                    $_SESSION['user_id'] = $conn->insert_id;
                    header('Location: /home.php');
                    exit();
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sign Up - VidZone</title>
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
            <h2 class="text-2xl font-bold">Create Account</h2>
        </div>

        <div class="bg-dark-card rounded-lg p-8 shadow-xl">
            <?php if ($error): ?>
                <div class="bg-red-900/20 border border-red-500 text-red-500 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/signup.php">
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium mb-2">Full Name</label>
                        <input type="text" name="full_name" required
                            class="w-full bg-dark-bg border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-primary"
                            placeholder="Enter your full name">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Username</label>
                        <input type="text" name="username" required
                            class="w-full bg-dark-bg border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-primary"
                            placeholder="Choose a username">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Email</label>
                        <input type="email" name="email" required
                            class="w-full bg-dark-bg border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-primary"
                            placeholder="Enter your email">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Password</label>
                        <input type="password" name="password" required
                            class="w-full bg-dark-bg border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-primary"
                            placeholder="Create a password">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Confirm Password</label>
                        <input type="password" name="confirm_password" required
                            class="w-full bg-dark-bg border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-primary"
                            placeholder="Confirm your password">
                    </div>

                    <button type="submit" class="w-full bg-primary hover:bg-red-700 py-3 rounded-lg font-semibold transition">
                        Sign Up
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center text-gray-400">
                <p>Already have an account?
                    <a href="/login.php" class="text-primary hover:underline">Sign In</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
