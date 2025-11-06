<?php
require_once __DIR__ . '/common/header.php';
requireLogin();

$channelId = (int)($_GET['id'] ?? 0);
if (!$channelId) {
    header('Location: /home.php');
    exit();
}

$channel = getUserById($channelId);
if (!$channel) {
    header('Location: /home.php');
    exit();
}

$currentUserId = getCurrentUserId();

// Handle subscribe/unsubscribe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'subscribe') {
        $conn->query("INSERT IGNORE INTO subscriptions (user_id, channel_id) VALUES ($currentUserId, $channelId)");
        createNotification($channelId, 'subscription', getUserById($currentUserId)['username'] . ' subscribed to your channel', '/channel.php?id=' . $currentUserId);
    } elseif ($_POST['action'] === 'unsubscribe') {
        $conn->query("DELETE FROM subscriptions WHERE user_id = $currentUserId AND channel_id = $channelId");
    }
    header("Location: /channel.php?id=$channelId");
    exit();
}

$isSubscribed = isSubscribed($currentUserId, $channelId);
$subscriberCount = getSubscriberCount($channelId);

// Get channel videos
$videos = $conn->query("SELECT v.*,
    (SELECT COUNT(*) FROM likes WHERE video_id = v.id AND type = 1) as likes,
    (SELECT COUNT(*) FROM comments WHERE video_id = v.id) as comment_count
    FROM videos v
    WHERE v.user_id = $channelId AND v.status = 'published'
    ORDER BY v.created_at DESC");

// Get total views
$statsResult = $conn->query("SELECT SUM(views) as total_views, COUNT(*) as video_count FROM videos WHERE user_id = $channelId");
$stats = $statsResult->fetch_assoc();
?>

<div class="container mx-auto px-4 py-6">
    <!-- Channel Header -->
    <div class="bg-dark-card rounded-lg overflow-hidden mb-6">
        <!-- Cover Image -->
        <?php if ($channel['cover_pic']): ?>
            <div class="h-32 md:h-48 bg-gradient-to-r from-primary to-red-900">
                <img src="/uploads/<?php echo htmlspecialchars($channel['cover_pic']); ?>"
                    alt="Cover" class="w-full h-full object-cover">
            </div>
        <?php else: ?>
            <div class="h-32 md:h-48 bg-gradient-to-r from-primary to-red-900"></div>
        <?php endif; ?>

        <!-- Channel Info -->
        <div class="p-6">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                <div class="-mt-16 md:-mt-20">
                    <?php if ($channel['profile_pic']): ?>
                        <img src="/uploads/<?php echo htmlspecialchars($channel['profile_pic']); ?>"
                            alt="<?php echo htmlspecialchars($channel['username']); ?>"
                            class="w-32 h-32 rounded-full border-4 border-dark-card object-cover">
                    <?php else: ?>
                        <div class="w-32 h-32 rounded-full border-4 border-dark-card bg-gray-700 flex items-center justify-center">
                            <i class="fas fa-user text-5xl"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex-1 text-center md:text-left">
                    <div class="flex flex-col md:flex-row md:items-center gap-4 mb-3">
                        <h1 class="text-2xl md:text-3xl font-bold">
                            <?php echo htmlspecialchars($channel['full_name'] ?: $channel['username']); ?>
                            <?php if ($channel['is_verified']): ?>
                                <i class="fas fa-check-circle text-primary ml-2"></i>
                            <?php endif; ?>
                        </h1>
                        <?php if ($channelId != $currentUserId): ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="<?php echo $isSubscribed ? 'unsubscribe' : 'subscribe'; ?>">
                                <button type="submit" class="px-8 py-2 rounded-full font-semibold transition <?php echo $isSubscribed ? 'bg-gray-700 hover:bg-gray-600' : 'bg-primary hover:bg-red-700'; ?>">
                                    <?php echo $isSubscribed ? 'Subscribed' : 'Subscribe'; ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="/settings.php" class="px-6 py-2 rounded-full bg-gray-700 hover:bg-gray-600 transition">
                                <i class="fas fa-cog mr-2"></i>Edit Channel
                            </a>
                        <?php endif; ?>
                    </div>

                    <p class="text-gray-400 mb-2">@<?php echo htmlspecialchars($channel['username']); ?></p>

                    <div class="flex flex-wrap justify-center md:justify-start gap-4 text-sm text-gray-400 mb-3">
                        <span><i class="fas fa-users mr-2"></i><?php echo formatNumber($subscriberCount); ?> subscribers</span>
                        <span><i class="fas fa-video mr-2"></i><?php echo $stats['video_count']; ?> videos</span>
                        <span><i class="fas fa-eye mr-2"></i><?php echo formatNumber($stats['total_views'] ?: 0); ?> total views</span>
                    </div>

                    <?php if ($channel['bio']): ?>
                        <p class="text-gray-300"><?php echo nl2br(htmlspecialchars($channel['bio'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Videos Section -->
    <div>
        <h2 class="text-xl font-bold mb-4">Videos</h2>

        <?php if ($videos->num_rows > 0): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <?php while ($video = $videos->fetch_assoc()): ?>
                    <a href="/watch.php?v=<?php echo $video['id']; ?>" class="group">
                        <div class="bg-dark-card rounded-lg overflow-hidden hover:bg-dark-hover transition">
                            <!-- Thumbnail -->
                            <div class="relative aspect-video bg-gray-800">
                                <?php if ($video['thumbnail']): ?>
                                    <img src="/uploads/<?php echo htmlspecialchars($video['thumbnail']); ?>"
                                        alt="<?php echo htmlspecialchars($video['title']); ?>"
                                        class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fas fa-play-circle text-6xl text-gray-600"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="absolute bottom-2 right-2 bg-black/80 px-2 py-1 rounded text-xs">
                                    <?php echo formatNumber($video['views']); ?> views
                                </div>
                            </div>

                            <!-- Info -->
                            <div class="p-3">
                                <h3 class="font-semibold line-clamp-2 group-hover:text-primary transition mb-2">
                                    <?php echo htmlspecialchars($video['title']); ?>
                                </h3>
                                <div class="flex items-center gap-3 text-xs text-gray-400">
                                    <span><i class="fas fa-eye mr-1"></i><?php echo formatNumber($video['views']); ?></span>
                                    <span><i class="fas fa-thumbs-up mr-1"></i><?php echo formatNumber($video['likes']); ?></span>
                                    <span><i class="fas fa-comment mr-1"></i><?php echo formatNumber($video['comment_count']); ?></span>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    <?php echo timeAgo($video['created_at']); ?>
                                </p>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16">
                <i class="fas fa-video text-6xl text-gray-600 mb-4"></i>
                <h3 class="text-xl font-semibold mb-2">No videos yet</h3>
                <p class="text-gray-400">
                    <?php if ($channelId == $currentUserId): ?>
                        Start sharing your content with the world!
                    <?php else: ?>
                        This channel hasn't uploaded any videos yet.
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/common/footer.php'; ?>
