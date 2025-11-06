<?php
require_once __DIR__ . '/common/header.php';

// Get statistics
$userCount = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$videoCount = $conn->query("SELECT COUNT(*) as count FROM videos")->fetch_assoc()['count'];
$commentCount = $conn->query("SELECT COUNT(*) as count FROM comments")->fetch_assoc()['count'];
$totalViews = $conn->query("SELECT SUM(views) as total FROM videos")->fetch_assoc()['total'] ?? 0;

// Recent users
$recentUsers = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");

// Recent videos
$recentVideos = $conn->query("SELECT v.*, u.username FROM videos v JOIN users u ON v.user_id = u.id ORDER BY v.created_at DESC LIMIT 5");

// Top videos
$topVideos = $conn->query("SELECT v.*, u.username FROM videos v JOIN users u ON v.user_id = u.id ORDER BY v.views DESC LIMIT 5");
?>

<div>
    <h1 class="text-3xl font-bold mb-8">Dashboard</h1>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-dark-card rounded-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm mb-1">Total Users</p>
                    <p class="text-3xl font-bold"><?php echo number_format($userCount); ?></p>
                </div>
                <i class="fas fa-users text-4xl text-blue-500"></i>
            </div>
        </div>

        <div class="bg-dark-card rounded-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm mb-1">Total Videos</p>
                    <p class="text-3xl font-bold"><?php echo number_format($videoCount); ?></p>
                </div>
                <i class="fas fa-video text-4xl text-green-500"></i>
            </div>
        </div>

        <div class="bg-dark-card rounded-lg p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm mb-1">Total Comments</p>
                    <p class="text-3xl font-bold"><?php echo number_format($commentCount); ?></p>
                </div>
                <i class="fas fa-comments text-4xl text-yellow-500"></i>
            </div>
        </div>

        <div class="bg-dark-card rounded-lg p-6 border-l-4 border-primary">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm mb-1">Total Views</p>
                    <p class="text-3xl font-bold"><?php echo number_format($totalViews); ?></p>
                </div>
                <i class="fas fa-eye text-4xl text-primary"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Users -->
        <div class="bg-dark-card rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Recent Users</h2>
            <div class="space-y-3">
                <?php while ($user = $recentUsers->fetch_assoc()): ?>
                    <div class="flex items-center justify-between p-3 bg-dark-bg rounded-lg">
                        <div class="flex items-center gap-3">
                            <?php if ($user['profile_pic']): ?>
                                <img src="/uploads/<?php echo htmlspecialchars($user['profile_pic']); ?>"
                                    alt="<?php echo htmlspecialchars($user['username']); ?>"
                                    class="w-10 h-10 rounded-full object-cover">
                            <?php else: ?>
                                <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <p class="font-semibold"><?php echo htmlspecialchars($user['username']); ?></p>
                                <p class="text-sm text-gray-400"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                        </div>
                        <a href="/admin/users.php" class="text-primary hover:underline text-sm">View</a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Recent Videos -->
        <div class="bg-dark-card rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Recent Videos</h2>
            <div class="space-y-3">
                <?php while ($video = $recentVideos->fetch_assoc()): ?>
                    <div class="flex items-center justify-between p-3 bg-dark-bg rounded-lg">
                        <div class="flex-1">
                            <p class="font-semibold truncate"><?php echo htmlspecialchars($video['title']); ?></p>
                            <p class="text-sm text-gray-400">
                                by <?php echo htmlspecialchars($video['username']); ?> •
                                <?php echo number_format($video['views']); ?> views
                            </p>
                        </div>
                        <a href="/admin/videos.php" class="text-primary hover:underline text-sm ml-4">View</a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Top Videos -->
        <div class="bg-dark-card rounded-lg p-6 lg:col-span-2">
            <h2 class="text-xl font-bold mb-4">Top Videos by Views</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left border-b border-gray-800">
                            <th class="pb-3">Title</th>
                            <th class="pb-3">Creator</th>
                            <th class="pb-3">Views</th>
                            <th class="pb-3">Date</th>
                            <th class="pb-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($video = $topVideos->fetch_assoc()): ?>
                            <tr class="border-b border-gray-800/50">
                                <td class="py-3"><?php echo htmlspecialchars($video['title']); ?></td>
                                <td class="py-3"><?php echo htmlspecialchars($video['username']); ?></td>
                                <td class="py-3"><?php echo number_format($video['views']); ?></td>
                                <td class="py-3 text-sm text-gray-400"><?php echo date('M d, Y', strtotime($video['created_at'])); ?></td>
                                <td class="py-3">
                                    <a href="/watch.php?v=<?php echo $video['id']; ?>" class="text-primary hover:underline text-sm">View</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/common/footer.php'; ?>
