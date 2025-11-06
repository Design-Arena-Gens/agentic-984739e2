<?php
require_once __DIR__ . '/common/header.php';
requireLogin();

// Get videos from subscribed channels
$userId = getCurrentUserId();
$query = "SELECT v.*, u.username, u.profile_pic,
    (SELECT COUNT(*) FROM likes WHERE video_id = v.id AND type = 1) as likes,
    (SELECT COUNT(*) FROM comments WHERE video_id = v.id) as comment_count
    FROM videos v
    JOIN users u ON v.user_id = u.id
    WHERE v.status = 'published'
    ORDER BY v.created_at DESC
    LIMIT 50";

$videos = $conn->query($query);
?>

<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-2">Home</h1>
        <p class="text-gray-400">Latest videos from creators</p>
    </div>

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
                                        <span><i class="fas fa-comment mr-1"></i><?php echo formatNumber($video['comment_count']); ?></span>
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
    <?php else: ?>
        <div class="text-center py-16">
            <i class="fas fa-video text-6xl text-gray-600 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">No videos yet</h3>
            <p class="text-gray-400 mb-6">Be the first to upload content!</p>
            <a href="/upload.php" class="inline-block bg-primary hover:bg-red-700 px-6 py-3 rounded-full transition">
                Upload Video
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/common/footer.php'; ?>
