<?php
require_once __DIR__ . '/common/header.php';

$success = '';

// Handle comment deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment'])) {
    $commentId = (int)($_POST['comment_id'] ?? 0);
    if ($commentId) {
        $conn->query("DELETE FROM comments WHERE id = $commentId");
        $success = 'Comment deleted successfully';
    }
}

$comments = $conn->query("SELECT c.*, u.username, v.title as video_title
    FROM comments c
    JOIN users u ON c.user_id = u.id
    JOIN videos v ON c.video_id = v.id
    ORDER BY c.created_at DESC
    LIMIT 100");
?>

<div>
    <h1 class="text-3xl font-bold mb-8">Comments Management</h1>

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
                        <th class="text-left p-4">Comment</th>
                        <th class="text-left p-4">User</th>
                        <th class="text-left p-4">Video</th>
                        <th class="text-left p-4">Date</th>
                        <th class="text-left p-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($comment = $comments->fetch_assoc()): ?>
                        <tr class="border-t border-gray-800">
                            <td class="p-4 max-w-md">
                                <p class="text-gray-300"><?php echo htmlspecialchars($comment['comment']); ?></p>
                            </td>
                            <td class="p-4">
                                <span class="font-semibold"><?php echo htmlspecialchars($comment['username']); ?></span>
                            </td>
                            <td class="p-4 max-w-xs">
                                <p class="truncate"><?php echo htmlspecialchars($comment['video_title']); ?></p>
                            </td>
                            <td class="p-4 text-gray-400 text-sm">
                                <?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?>
                            </td>
                            <td class="p-4">
                                <div class="flex gap-2">
                                    <a href="/watch.php?v=<?php echo $comment['video_id']; ?>" class="text-green-400 hover:text-green-300" title="View Video">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form method="POST" class="inline" onsubmit="return confirm('Delete this comment?')">
                                        <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                        <button type="submit" name="delete_comment" class="text-red-400 hover:text-red-300" title="Delete Comment">
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
