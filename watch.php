<?php
require_once __DIR__ . '/common/header.php';
requireLogin();

$videoId = (int)($_GET['v'] ?? 0);
if (!$videoId) {
    header('Location: /home.php');
    exit();
}

$video = getVideoById($videoId);
if (!$video) {
    header('Location: /home.php');
    exit();
}

// Increment view count
$conn->query("UPDATE videos SET views = views + 1 WHERE id = $videoId");

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $userId = getCurrentUserId();
    $comment = sanitize($_POST['comment']);
    if (!empty($comment)) {
        $conn->query("INSERT INTO comments (video_id, user_id, comment) VALUES ($videoId, $userId, '$comment')");
        header("Location: /watch.php?v=$videoId");
        exit();
    }
}

// Handle like/dislike
if (isset($_POST['action'])) {
    $userId = getCurrentUserId();
    $action = $_POST['action'];

    if ($action === 'like' || $action === 'dislike') {
        $type = $action === 'like' ? 1 : 0;
        $conn->query("DELETE FROM likes WHERE user_id = $userId AND video_id = $videoId");
        $conn->query("INSERT INTO likes (user_id, video_id, type) VALUES ($userId, $videoId, $type)");
    }
    header("Location: /watch.php?v=$videoId");
    exit();
}

// Get comments
$comments = $conn->query("SELECT c.*, u.username, u.profile_pic FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.video_id = $videoId
    ORDER BY c.created_at DESC");

// Get related videos
$relatedVideos = $conn->query("SELECT v.*, u.username FROM videos v
    JOIN users u ON v.user_id = u.id
    WHERE v.id != $videoId AND v.status = 'published'
    ORDER BY RAND()
    LIMIT 10");

$currentUserId = getCurrentUserId();
$userLikeStatus = hasUserLikedVideo($currentUserId, $videoId);
$isSubscribed = isSubscribed($currentUserId, $video['user_id']);
$subscriberCount = getSubscriberCount($video['user_id']);
?>

<div class="container mx-auto px-4 py-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Video Player -->
            <div class="bg-black rounded-lg overflow-hidden aspect-video">
                <video controls class="w-full h-full">
                    <source src="/uploads/<?php echo htmlspecialchars($video['video_file']); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>

            <!-- Video Info -->
            <div class="mt-4">
                <h1 class="text-2xl font-bold mb-3"><?php echo htmlspecialchars($video['title']); ?></h1>

                <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                    <div class="flex items-center gap-2 text-gray-400">
                        <span><?php echo formatNumber($video['views']); ?> views</span>
                        <span>•</span>
                        <span><?php echo timeAgo($video['created_at']); ?></span>
                    </div>

                    <div class="flex gap-2">
                        <form method="POST" class="inline">
                            <input type="hidden" name="action" value="like">
                            <button type="submit" class="px-4 py-2 bg-dark-card hover:bg-dark-hover rounded-full transition <?php echo $userLikeStatus === 1 ? 'text-primary' : ''; ?>">
                                <i class="fas fa-thumbs-up mr-2"></i><?php echo formatNumber($video['likes']); ?>
                            </button>
                        </form>
                        <form method="POST" class="inline">
                            <input type="hidden" name="action" value="dislike">
                            <button type="submit" class="px-4 py-2 bg-dark-card hover:bg-dark-hover rounded-full transition <?php echo $userLikeStatus === 0 ? 'text-primary' : ''; ?>">
                                <i class="fas fa-thumbs-down mr-2"></i><?php echo formatNumber($video['dislikes']); ?>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Channel Info -->
                <div class="bg-dark-card rounded-lg p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <a href="/channel.php?id=<?php echo $video['user_id']; ?>">
                                <?php if ($video['profile_pic']): ?>
                                    <img src="/uploads/<?php echo htmlspecialchars($video['profile_pic']); ?>"
                                        alt="<?php echo htmlspecialchars($video['username']); ?>"
                                        class="w-12 h-12 rounded-full object-cover">
                                <?php else: ?>
                                    <div class="w-12 h-12 rounded-full bg-gray-700 flex items-center justify-center">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <div>
                                <a href="/channel.php?id=<?php echo $video['user_id']; ?>" class="font-semibold hover:text-primary">
                                    <?php echo htmlspecialchars($video['username']); ?>
                                </a>
                                <p class="text-sm text-gray-400"><?php echo formatNumber($subscriberCount); ?> subscribers</p>
                            </div>
                        </div>

                        <?php if ($video['user_id'] != $currentUserId): ?>
                            <form method="POST" action="/channel.php?id=<?php echo $video['user_id']; ?>">
                                <input type="hidden" name="action" value="<?php echo $isSubscribed ? 'unsubscribe' : 'subscribe'; ?>">
                                <button type="submit" class="px-6 py-2 rounded-full font-semibold transition <?php echo $isSubscribed ? 'bg-gray-700 hover:bg-gray-600' : 'bg-primary hover:bg-red-700'; ?>">
                                    <?php echo $isSubscribed ? 'Subscribed' : 'Subscribe'; ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <?php if ($video['description']): ?>
                        <div class="mt-4 text-gray-300 whitespace-pre-wrap">
                            <?php echo htmlspecialchars($video['description']); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Comments Section -->
                <div class="mt-6">
                    <h2 class="text-xl font-bold mb-4">
                        <?php echo $video['comment_count']; ?> Comments
                    </h2>

                    <!-- Comment Form -->
                    <form method="POST" class="mb-6">
                        <textarea name="comment" rows="3" required
                            class="w-full bg-dark-card border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-primary mb-3"
                            placeholder="Add a comment..."></textarea>
                        <button type="submit" class="bg-primary hover:bg-red-700 px-6 py-2 rounded-full transition">
                            Post Comment
                        </button>
                    </form>

                    <!-- Comments List -->
                    <div class="space-y-4">
                        <?php while ($comment = $comments->fetch_assoc()): ?>
                            <div class="flex gap-3">
                                <?php if ($comment['profile_pic']): ?>
                                    <img src="/uploads/<?php echo htmlspecialchars($comment['profile_pic']); ?>"
                                        alt="<?php echo htmlspecialchars($comment['username']); ?>"
                                        class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                                <?php else: ?>
                                    <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-user text-sm"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-semibold"><?php echo htmlspecialchars($comment['username']); ?></span>
                                        <span class="text-xs text-gray-500"><?php echo timeAgo($comment['created_at']); ?></span>
                                    </div>
                                    <p class="text-gray-300"><?php echo htmlspecialchars($comment['comment']); ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar - Related Videos -->
        <div class="space-y-3">
            <h3 class="font-bold text-lg mb-4">Related Videos</h3>
            <?php while ($relVideo = $relatedVideos->fetch_assoc()): ?>
                <a href="/watch.php?v=<?php echo $relVideo['id']; ?>" class="flex gap-2 hover:bg-dark-card p-2 rounded-lg transition">
                    <div class="w-40 aspect-video bg-gray-800 rounded overflow-hidden flex-shrink-0">
                        <?php if ($relVideo['thumbnail']): ?>
                            <img src="/uploads/<?php echo htmlspecialchars($relVideo['thumbnail']); ?>"
                                alt="<?php echo htmlspecialchars($relVideo['title']); ?>"
                                class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fas fa-play-circle text-3xl text-gray-600"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-semibold text-sm line-clamp-2 mb-1">
                            <?php echo htmlspecialchars($relVideo['title']); ?>
                        </h4>
                        <p class="text-xs text-gray-400"><?php echo htmlspecialchars($relVideo['username']); ?></p>
                        <p class="text-xs text-gray-500"><?php echo formatNumber($relVideo['views']); ?> views</p>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/common/footer.php'; ?>
