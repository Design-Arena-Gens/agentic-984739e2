<?php
require_once __DIR__ . '/common/header.php';
requireLogin();

$userId = getCurrentUserId();

// Mark all as read if requested
if (isset($_POST['mark_all_read'])) {
    $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $userId");
    header('Location: /notifications.php');
    exit();
}

// Mark single notification as read
if (isset($_POST['mark_read'])) {
    $notifId = (int)$_POST['notif_id'];
    $conn->query("UPDATE notifications SET is_read = 1 WHERE id = $notifId AND user_id = $userId");
    header('Location: /notifications.php');
    exit();
}

// Delete notification
if (isset($_POST['delete'])) {
    $notifId = (int)$_POST['notif_id'];
    $conn->query("DELETE FROM notifications WHERE id = $notifId AND user_id = $userId");
    header('Location: /notifications.php');
    exit();
}

// Get notifications
$notifications = $conn->query("SELECT * FROM notifications WHERE user_id = $userId ORDER BY created_at DESC LIMIT 50");
$unreadCount = getUnreadNotificationCount($userId);
?>

<div class="container mx-auto px-4 py-6 max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold mb-2">Notifications</h1>
            <?php if ($unreadCount > 0): ?>
                <p class="text-gray-400"><?php echo $unreadCount; ?> unread notification<?php echo $unreadCount > 1 ? 's' : ''; ?></p>
            <?php endif; ?>
        </div>

        <?php if ($unreadCount > 0): ?>
            <form method="POST">
                <button type="submit" name="mark_all_read" class="text-sm text-primary hover:underline">
                    Mark all as read
                </button>
            </form>
        <?php endif; ?>
    </div>

    <?php if ($notifications->num_rows > 0): ?>
        <div class="space-y-2">
            <?php while ($notif = $notifications->fetch_assoc()): ?>
                <div class="bg-dark-card rounded-lg p-4 <?php echo !$notif['is_read'] ? 'border-l-4 border-primary' : ''; ?>">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 text-2xl">
                            <?php
                            $icon = 'fa-bell';
                            switch ($notif['type']) {
                                case 'subscription':
                                    $icon = 'fa-user-plus';
                                    break;
                                case 'comment':
                                    $icon = 'fa-comment';
                                    break;
                                case 'like':
                                    $icon = 'fa-thumbs-up';
                                    break;
                                case 'video':
                                    $icon = 'fa-video';
                                    break;
                            }
                            ?>
                            <i class="fas <?php echo $icon; ?> <?php echo !$notif['is_read'] ? 'text-primary' : 'text-gray-600'; ?>"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="<?php echo !$notif['is_read'] ? 'font-semibold' : 'text-gray-300'; ?>">
                                <?php echo htmlspecialchars($notif['message']); ?>
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                <?php echo timeAgo($notif['created_at']); ?>
                            </p>
                            <?php if ($notif['link']): ?>
                                <a href="<?php echo htmlspecialchars($notif['link']); ?>" class="text-sm text-primary hover:underline mt-2 inline-block">
                                    View <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="flex gap-2">
                            <?php if (!$notif['is_read']): ?>
                                <form method="POST">
                                    <input type="hidden" name="notif_id" value="<?php echo $notif['id']; ?>">
                                    <button type="submit" name="mark_read" class="text-gray-400 hover:text-white" title="Mark as read">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" onsubmit="return confirm('Delete this notification?')">
                                <input type="hidden" name="notif_id" value="<?php echo $notif['id']; ?>">
                                <button type="submit" name="delete" class="text-gray-400 hover:text-red-500" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-16">
            <i class="fas fa-bell-slash text-6xl text-gray-600 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">No notifications</h3>
            <p class="text-gray-400">When you get notifications, they'll show up here</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/common/footer.php'; ?>
