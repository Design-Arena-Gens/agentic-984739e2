<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$currentUser = null;
if (isLoggedIn()) {
    $currentUser = getUserById(getCurrentUserId());
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#ff0000',
                        dark: {
                            bg: '#0f0f0f',
                            card: '#1f1f1f',
                            hover: '#2f2f2f'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        * {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-touch-callout: none;
        }
        input, textarea {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }
        body {
            touch-action: pan-x pan-y;
        }
    </style>
    <script>
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('gesturestart', e => e.preventDefault());
        document.addEventListener('touchmove', function(e) {
            if (e.scale !== 1) { e.preventDefault(); }
        }, { passive: false });
    </script>
</head>
<body class="bg-dark-bg text-white min-h-screen">
    <header class="bg-dark-card sticky top-0 z-50 border-b border-gray-800">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between gap-4">
                <!-- Logo -->
                <a href="/home.php" class="flex items-center gap-2 text-primary font-bold text-xl md:text-2xl">
                    <i class="fas fa-play-circle"></i>
                    <span><?php echo SITE_NAME; ?></span>
                </a>

                <!-- Search Bar -->
                <form action="/explore.php" method="GET" class="flex-1 max-w-2xl">
                    <div class="relative">
                        <input type="text" name="q" placeholder="Search videos..."
                            class="w-full bg-dark-bg text-white px-4 py-2 rounded-full border border-gray-700 focus:outline-none focus:border-primary"
                            value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                <?php if ($currentUser): ?>
                    <!-- User Menu -->
                    <div class="flex items-center gap-4">
                        <a href="/upload.php" class="hidden md:flex items-center gap-2 bg-primary hover:bg-red-700 px-4 py-2 rounded-full transition">
                            <i class="fas fa-upload"></i>
                            <span>Upload</span>
                        </a>
                        <a href="/notifications.php" class="relative text-gray-300 hover:text-white text-xl">
                            <i class="fas fa-bell"></i>
                            <?php
                            $notifCount = getUnreadNotificationCount($currentUser['id']);
                            if ($notifCount > 0):
                            ?>
                                <span class="absolute -top-1 -right-1 bg-primary text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                    <?php echo $notifCount > 9 ? '9+' : $notifCount; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        <a href="/profile.php?id=<?php echo $currentUser['id']; ?>" class="w-8 h-8 rounded-full overflow-hidden border-2 border-gray-700 hover:border-primary transition">
                            <?php if ($currentUser['profile_pic']): ?>
                                <img src="/uploads/<?php echo htmlspecialchars($currentUser['profile_pic']); ?>" alt="Profile" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full bg-gray-700 flex items-center justify-center">
                                    <i class="fas fa-user text-xs"></i>
                                </div>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="flex items-center gap-3">
                        <a href="/login.php" class="px-4 py-2 rounded-full border border-primary text-primary hover:bg-primary hover:text-white transition">
                            Sign In
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Mobile Bottom Navigation -->
    <?php if ($currentUser): ?>
    <nav class="fixed bottom-0 left-0 right-0 bg-dark-card border-t border-gray-800 md:hidden z-50">
        <div class="flex justify-around items-center py-3">
            <a href="/home.php" class="flex flex-col items-center gap-1 text-gray-400 hover:text-white">
                <i class="fas fa-home text-xl"></i>
                <span class="text-xs">Home</span>
            </a>
            <a href="/explore.php" class="flex flex-col items-center gap-1 text-gray-400 hover:text-white">
                <i class="fas fa-compass text-xl"></i>
                <span class="text-xs">Explore</span>
            </a>
            <a href="/upload.php" class="flex flex-col items-center gap-1 text-primary">
                <i class="fas fa-plus-circle text-3xl"></i>
            </a>
            <a href="/subscriptions.php" class="flex flex-col items-center gap-1 text-gray-400 hover:text-white">
                <i class="fas fa-users text-xl"></i>
                <span class="text-xs">Subscriptions</span>
            </a>
            <a href="/settings.php" class="flex flex-col items-center gap-1 text-gray-400 hover:text-white">
                <i class="fas fa-cog text-xl"></i>
                <span class="text-xs">Settings</span>
            </a>
        </div>
    </nav>
    <?php endif; ?>

    <main class="pb-20 md:pb-8">
