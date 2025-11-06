<?php
require_once __DIR__ . '/common/config.php';

// Redirect to home if logged in, otherwise show landing page
if (isLoggedIn()) {
    header('Location: /home.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Welcome to VidZone</title>
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
        body { touch-action: pan-x pan-y; }
    </style>
</head>
<body class="bg-dark-bg text-white min-h-screen">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-dark-card border-b border-gray-800">
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <div class="flex items-center gap-2 text-primary font-bold text-2xl">
                    <i class="fas fa-play-circle"></i>
                    <span>VidZone</span>
                </div>
                <div class="flex gap-3">
                    <a href="/login.php" class="px-6 py-2 rounded-full border border-primary text-primary hover:bg-primary hover:text-white transition">
                        Sign In
                    </a>
                    <a href="/signup.php" class="px-6 py-2 rounded-full bg-primary hover:bg-red-700 transition">
                        Sign Up
                    </a>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="flex-1 flex items-center justify-center p-4">
            <div class="text-center max-w-4xl">
                <div class="mb-8">
                    <i class="fas fa-play-circle text-primary text-8xl md:text-9xl mb-6"></i>
                    <h1 class="text-4xl md:text-6xl font-bold mb-4">Welcome to VidZone</h1>
                    <p class="text-xl md:text-2xl text-gray-400 mb-8">
                        Share your moments, discover amazing content
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                    <div class="bg-dark-card p-6 rounded-lg">
                        <i class="fas fa-upload text-primary text-4xl mb-4"></i>
                        <h3 class="text-xl font-bold mb-2">Upload Videos</h3>
                        <p class="text-gray-400">Share your creativity with the world</p>
                    </div>
                    <div class="bg-dark-card p-6 rounded-lg">
                        <i class="fas fa-users text-primary text-4xl mb-4"></i>
                        <h3 class="text-xl font-bold mb-2">Build Community</h3>
                        <p class="text-gray-400">Connect with creators and fans</p>
                    </div>
                    <div class="bg-dark-card p-6 rounded-lg">
                        <i class="fas fa-compass text-primary text-4xl mb-4"></i>
                        <h3 class="text-xl font-bold mb-2">Explore Content</h3>
                        <p class="text-gray-400">Discover videos you'll love</p>
                    </div>
                </div>

                <a href="/signup.php" class="inline-block bg-primary hover:bg-red-700 px-8 py-4 rounded-full text-xl font-bold transition">
                    Get Started Now
                </a>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-dark-card border-t border-gray-800 py-6">
            <div class="container mx-auto px-4 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> VidZone. All rights reserved.</p>
            </div>
        </footer>
    </div>
</body>
</html>
