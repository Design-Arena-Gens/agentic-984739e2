<?php
require_once __DIR__ . '/common/header.php';

$success = '';

// Handle video actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $videoId = (int)($_POST['video_id'] ?? 0);

    if (isset($_POST['delete_video']) && $videoId) {
        $conn->query("DELETE FROM videos WHERE id = $videoId");
        $success = 'Video deleted successfully';
    } elseif (isset($_POST['toggle_status']) && $videoId) {
        $conn->query("UPDATE videos SET status = IF(status = 'published', 'draft', 'published') WHERE id = $videoId");
        $success = 'Video status updated';
    }
}

$search = sanitize($_GET['search'] ?? '');
$query = "SELECT v.*, u.username FROM videos v JOIN users u ON v.user_id = u.id WHERE 1=1";
if ($search) {
    $query .= " AND (v.title LIKE '%$search%' OR u.username LIKE '%$search%')";
}
$query .= " ORDER BY v.created_at DESC";

$videos = $conn->query($query);
?>

<div>
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold">Videos Management</h1>
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Search videos..."
                class="bg-dark-card border border-gray-700 rounded-lg px-4 py-2 focus:outline-none focus:border-primary">
            <button type="submit" class="bg-primary hover:bg-red-700 px-6 py-2 rounded-lg transition">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>

    <?php if ($success): ?>
        <div class="bg-green-900/20 border border-green-500 text-green-500 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <div class="bg-dark-card rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark-hover">
                    <tr>
                        <th class="text-left p-4">Video</th>
                        <th class="text-left p-4">Creator</th>
                        <th class="text-left p-4">Views</th>
                        <th class="text-left p-4">Status</th>
                        <th class="text-left p-4">Date</th>
                        <th class="text-left p-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($video = $videos->fetch_assoc()): ?>
                        <tr class="border-t border-gray-800">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-24 h-16 bg-gray-800 rounded overflow-hidden flex-shrink-0">
                                        <?php if ($video['thumbnail']): ?>
                                            <img src="/uploads/<?php echo htmlspecialchars($video['thumbnail']); ?>"
                                                alt="<?php echo htmlspecialchars($video['title']); ?>"
                                                class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center">
                                                <i class="fas fa-play-circle text-2xl text-gray-600"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold truncate"><?php echo htmlspecialchars($video['title']); ?></p>
                                        <?php if ($video['category']): ?>
                                            <span class="text-xs bg-gray-700 px-2 py-1 rounded mt-1 inline-block">
                                                <?php echo htmlspecialchars($video['category']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4"><?php echo htmlspecialchars($video['username']); ?></td>
                            <td class="p-4"><?php echo number_format($video['views']); ?></td>
                            <td class="p-4">
                                <?php if ($video['status'] === 'published'): ?>
                                    <span class="bg-green-900/20 text-green-400 px-3 py-1 rounded-full text-sm">Published</span>
                                <?php else: ?>
                                    <span class="bg-gray-700 text-gray-300 px-3 py-1 rounded-full text-sm">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-gray-400 text-sm"><?php echo date('M d, Y', strtotime($video['created_at'])); ?></td>
                            <td class="p-4">
                                <div class="flex gap-2">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                        <button type="submit" name="toggle_status" class="text-blue-400 hover:text-blue-300" title="Toggle Status">
                                            <i class="fas fa-toggle-on"></i>
                                        </button>
                                    </form>
                                    <a href="/watch.php?v=<?php echo $video['id']; ?>" class="text-green-400 hover:text-green-300" title="View Video">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form method="POST" class="inline" onsubmit="return confirm('Delete this video?')">
                                        <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                        <button type="submit" name="delete_video" class="text-red-400 hover:text-red-300" title="Delete Video">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/common/footer.php'; ?>
