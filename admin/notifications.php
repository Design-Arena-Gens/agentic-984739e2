<?php
require_once __DIR__ . '/common/header.php';

$success = '';

// Handle bulk notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_notification'])) {
    $message = sanitize($_POST['message']);
    $type = sanitize($_POST['type']);
    $link = sanitize($_POST['link']);

    if (!empty($message)) {
        $users = $conn->query("SELECT id FROM users");
        while ($user = $users->fetch_assoc()) {
            $conn->query("INSERT INTO notifications (user_id, type, message, link) VALUES ({$user['id']}, '$type', '$message', '$link')");
        }
        $success = 'Notification sent to all users';
    }
}

$recentNotifications = $conn->query("SELECT n.*, u.username
    FROM notifications n
    JOIN users u ON n.user_id = u.id
    ORDER BY n.created_at DESC
    LIMIT 50");
?>

<div>
    <h1 class="text-3xl font-bold mb-8">Notifications Management</h1>

    <?php if ($success): ?>
        <div class="bg-green-900/20 border border-green-500 text-green-500 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <!-- Send Notification Form -->
    <div class="bg-dark-card rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Send Notification to All Users</h2>
        <form method="POST">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Notification Type</label>
                    <select name="type" required
                        class="w-full bg-dark-bg border border-gray-700 rounded-lg px-4 py-2 focus:outline-none focus:border-primary">
                        <option value="announcement">Announcement</option>
                        <option value="update">Update</option>
                        <option value="alert">Alert</option>
                        <option value="info">Info</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Message</label>
                    <textarea name="message" required rows="3"
                        class="w-full bg-dark-bg border border-gray-700 rounded-lg px-4 py-2 focus:outline-none focus:border-primary"
                        placeholder="Enter notification message"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Link (Optional)</label>
                    <input type="text" name="link"
                        class="w-full bg-dark-bg border border-gray-700 rounded-lg px-4 py-2 focus:outline-none focus:border-primary"
                        placeholder="/example-page">
                </div>
                <button type="submit" name="send_notification" class="bg-primary hover:bg-red-700 px-6 py-2 rounded-lg transition">
                    <i class="fas fa-paper-plane mr-2"></i>Send to All Users
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Notifications -->
    <div class="bg-dark-card rounded-lg p-6">
        <h2 class="text-xl font-bold mb-4">Recent Notifications</h2>
        <div class="space-y-3">
            <?php while ($notif = $recentNotifications->fetch_assoc()): ?>
                <div class="bg-dark-bg rounded-lg p-4 border-l-4 <?php echo $notif['is_read'] ? 'border-gray-700' : 'border-primary'; ?>">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-semibold"><?php echo htmlspecialchars($notif['username']); ?></span>
                                <span class="text-xs bg-gray-700 px-2 py-1 rounded"><?php echo htmlspecialchars($notif['type']); ?></span>
                                <?php if (!$notif['is_read']): ?>
                                    <span class="text-xs bg-primary px-2 py-1 rounded">Unread</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-gray-300 text-sm"><?php echo htmlspecialchars($notif['message']); ?></p>
                            <p class="text-xs text-gray-500 mt-2">
                                <?php echo date('M d, Y H:i', strtotime($notif['created_at'])); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/common/footer.php'; ?>
