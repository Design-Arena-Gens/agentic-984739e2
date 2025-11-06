<?php
require_once __DIR__ . '/config.php';
requireAdminLogin();

$admin = null;
if (isAdminLoggedIn()) {
    $adminId = getAdminId();
    $result = $conn->query("SELECT * FROM users WHERE id = $adminId");
    $admin = $result->fetch_assoc();
}

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
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
                        dark: { bg: '#0f0f0f', card: '#1f1f1f', hover: '#2f2f2f' }
                    }
                }
            }
        }
    </script>
    <style>
        * { -webkit-user-select: none; -moz-user-select: none; user-select: none; }
        input, textarea { -webkit-user-select: text; -moz-user-select: text; user-select: text; }
        body { touch-action: pan-x pan-y; }
    </style>
    <script>
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('gesturestart', e => e.preventDefault());
    </script>
</head>
<body class="bg-dark-bg text-white min-h-screen">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-dark-card min-h-screen fixed left-0 top-0 overflow-y-auto">
            <div class="p-6 border-b border-gray-800">
                <a href="/admin/index.php" class="flex items-center gap-2 text-primary font-bold text-xl">
                    <i class="fas fa-play-circle"></i>
                    <span>VidZone Admin</span>
                </a>
            </div>

            <nav class="p-4">
                <a href="/admin/index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition <?php echo $currentPage === 'index' ? 'bg-primary text-white' : 'text-gray-400 hover:bg-dark-hover hover:text-white'; ?>">
                    <i class="fas fa-dashboard w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/admin/users.php" class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition <?php echo $currentPage === 'users' ? 'bg-primary text-white' : 'text-gray-400 hover:bg-dark-hover hover:text-white'; ?>">
                    <i class="fas fa-users w-5"></i>
                    <span>Users</span>
                </a>
                <a href="/admin/channels.php" class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition <?php echo $currentPage === 'channels' ? 'bg-primary text-white' : 'text-gray-400 hover:bg-dark-hover hover:text-white'; ?>">
                    <i class="fas fa-tv w-5"></i>
                    <span>Channels</span>
                </a>
                <a href="/admin/videos.php" class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition <?php echo $currentPage === 'videos' ? 'bg-primary text-white' : 'text-gray-400 hover:bg-dark-hover hover:text-white'; ?>">
                    <i class="fas fa-video w-5"></i>
                    <span>Videos</span>
                </a>
                <a href="/admin/comments.php" class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition <?php echo $currentPage === 'comments' ? 'bg-primary text-white' : 'text-gray-400 hover:bg-dark-hover hover:text-white'; ?>">
                    <i class="fas fa-comments w-5"></i>
                    <span>Comments</span>
                </a>
                <a href="/admin/ads.php" class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition <?php echo $currentPage === 'ads' ? 'bg-primary text-white' : 'text-gray-400 hover:bg-dark-hover hover:text-white'; ?>">
                    <i class="fas fa-ad w-5"></i>
                    <span>Ads</span>
                </a>
                <a href="/admin/notifications.php" class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition <?php echo $currentPage === 'notifications' ? 'bg-primary text-white' : 'text-gray-400 hover:bg-dark-hover hover:text-white'; ?>">
                    <i class="fas fa-bell w-5"></i>
                    <span>Notifications</span>
                </a>
                <a href="/admin/settings.php" class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition <?php echo $currentPage === 'settings' ? 'bg-primary text-white' : 'text-gray-400 hover:bg-dark-hover hover:text-white'; ?>">
                    <i class="fas fa-cog w-5"></i>
                    <span>Settings</span>
                </a>

                <div class="border-t border-gray-800 my-4"></div>

                <a href="/home.php" class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 text-gray-400 hover:bg-dark-hover hover:text-white transition">
                    <i class="fas fa-home w-5"></i>
                    <span>View Site</span>
                </a>
                <form method="POST" action="/admin/login.php">
                    <button type="submit" name="logout" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-dark-hover hover:text-red-500 transition">
                        <i class="fas fa-sign-out-alt w-5"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="ml-64 flex-1 p-8">
