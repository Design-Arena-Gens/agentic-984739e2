<?php
require_once __DIR__ . '/common/header.php';
requireLogin();

$userId = getCurrentUserId();

// Get subscribed channels
$channels = $conn->query("SELECT u.*,
    (SELECT COUNT(*) FROM subscriptions WHERE channel_id = u.id) as subscriber_count,
    (SELECT COUNT(*) FROM videos WHERE user_id = u.id AND status = 'published') as video_count
    FROM users u
    JOIN subscriptions s ON u.id = s.channel_id
    WHERE s.user_id = $userId
    ORDER BY u.username");

// Get latest videos from subscribed channels
$videos = $conn->query("SELECT v.*, u.username, u.profile_pic,
    (SELECT COUNT(*) FROM likes WHERE video_id = v.id AND type = 1) as likes,
    (SELECT COUNT(*) FROM comments WHERE video_id = v.id) as comment_count
    FROM videos v
    JOIN users u ON v.user_id = u.id
    JOIN subscriptions s ON v.user_id = s.channel_id
    WHERE s.user_id = $userId AND v.status = 'published'
    ORDER BY v.created_at DESC
    LIMIT 50");
?>

<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-2">Subscriptions</h1>
        <p class="text-gray-400">Channels you're subscribed to</p>
    </div>

    <!-- Subscribed Channels -->
    <?php if ($channels->num_rows > 0): ?>
        <div class="mb-8">
            <h2 class="text-xl font-bold mb-4">Your Channels</h2>
            <div class="flex gap-4 overflow-x-auto pb-4">
                <?php while ($channel = $channels->fetch_assoc()): ?>
                    <a href="/channel.php?id=<?php echo $channel['id']; ?>" class="flex-shrink-0 text-center hover:opacity-80 transition">
                        <?php if ($channel['profile_pic']): ?>
                            <img src="/uploads/<?php echo htmlspecialchars($channel['profile_pic']); ?>"
                                alt="<?php echo htmlspecialchars($channel['username']); ?>"
                                class="w-24 h-24 rounded-full object-cover mx-auto mb-2">
                        <?php else: ?>
                            <div class="w-24 h-24 rounded-full bg-gray-700 flex items-center justify-center mx-auto mb-2">
                                <i class="fas fa-user text-3xl"></i>
                            </div>
                        <?php endif; ?>
                        <p class="font-semibold text-sm"><?php echo htmlspecialchars($channel['username']); ?></p>
                        <p class="text-xs text-gray-400"><?php echo formatNumber($channel['subscriber_count']); ?> subs</p>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Latest Videos -->
        <?php if ($videos->num_rows > 0): ?>
            <div>
                <h2 class="text-xl font-bold mb-4">Latest Videos</h2>
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
                                    <div class="flex gap-3">
                                        <div class="flex-shrink-0">
                                            <?php if ($video['profile_pic']): ?>
                                                <img src="/uploads/<?php echo htmlspecialchars($video['profile_pic']); ?>"
                                                    alt="<?php echo htmlspecialchars($video['username']); ?>"
                                                    class="w-9 h-9 rounded-full object-cover">
                                            <?php else: ?>
                                                <div class="w-9 h-9 rounded-full bg-gray-700 flex items-center justify-center">
                                                    <i class="fas fa-user text-sm"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold line-clamp-2 group-hover:text-primary transition">
                                                <?php echo htmlspecialchars($video['title']); ?>
                                            </h3>
                                            <p class="text-sm text-gray-400 mt-1">
                                                <?php echo htmlspecialchars($video['username']); ?>
                                            </p>
                                            <div class="flex items-center gap-3 text-xs text-gray-400 mt-1">
                                                <span><i class="fas fa-eye mr-1"></i><?php echo formatNumber($video['views']); ?></span>
                                                <span><i class="fas fa-thumbs-up mr-1"></i><?php echo formatNumber($video['likes']); ?></span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">
                                                <?php echo timeAgo($video['created_at']); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-16">
                <i class="fas fa-video text-6xl text-gray-600 mb-4"></i>
                <h3 class="text-xl font-semibold mb-2">No videos yet</h3>
                <p class="text-gray-400">Your subscribed channels haven't posted any videos yet</p>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center py-16">
            <i class="fas fa-users text-6xl text-gray-600 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">No subscriptions</h3>
            <p class="text-gray-400 mb-6">Subscribe to channels to see their latest videos here</p>
            <a href="/explore.php" class="inline-block bg-primary hover:bg-red-700 px-6 py-3 rounded-full transition">
                Explore Channels
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/common/footer.php'; ?>
