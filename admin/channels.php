<?php
require_once __DIR__ . '/common/header.php';

// Get channels with stats
$channels = $conn->query("SELECT u.*,
    (SELECT COUNT(*) FROM subscriptions WHERE channel_id = u.id) as subscriber_count,
    (SELECT COUNT(*) FROM videos WHERE user_id = u.id) as video_count,
    (SELECT SUM(views) FROM videos WHERE user_id = u.id) as total_views
    FROM users u
    ORDER BY subscriber_count DESC");
?>

<div>
    <h1 class="text-3xl font-bold mb-8">Channels</h1>

    <div class="bg-dark-card rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark-hover">
                    <tr>
                        <th class="text-left p-4">Channel</th>
                        <th class="text-left p-4">Subscribers</th>
                        <th class="text-left p-4">Videos</th>
                        <th class="text-left p-4">Total Views</th>
                        <th class="text-left p-4">Status</th>
                        <th class="text-left p-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($channel = $channels->fetch_assoc()): ?>
                        <tr class="border-t border-gray-800">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <?php if ($channel['profile_pic']): ?>
                                        <img src="/uploads/<?php echo htmlspecialchars($channel['profile_pic']); ?>"
                                            alt="<?php echo htmlspecialchars($channel['username']); ?>"
                                            class="w-12 h-12 rounded-full object-cover">
                                    <?php else: ?>
                                        <div class="w-12 h-12 rounded-full bg-gray-700 flex items-center justify-center">
                                            <i class="fas fa-user text-xl"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="font-semibold">
                                            <?php echo htmlspecialchars($channel['username']); ?>
                                            <?php if ($channel['is_verified']): ?>
                                                <i class="fas fa-check-circle text-primary ml-1"></i>
                                            <?php endif; ?>
                                        </p>
                                        <p class="text-sm text-gray-400">@<?php echo htmlspecialchars($channel['username']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="text-lg font-semibold"><?php echo number_format($channel['subscriber_count']); ?></span>
                            </td>
                            <td class="p-4"><?php echo number_format($channel['video_count']); ?></td>
                            <td class="p-4"><?php echo number_format($channel['total_views'] ?: 0); ?></td>
                            <td class="p-4">
                                <?php if ($channel['is_admin']): ?>
                                    <span class="bg-primary/20 text-primary px-3 py-1 rounded-full text-sm">Admin</span>
                                <?php else: ?>
                                    <span class="bg-green-900/20 text-green-400 px-3 py-1 rounded-full text-sm">Active</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4">
                                <a href="/channel.php?id=<?php echo $channel['id']; ?>" class="text-primary hover:underline text-sm">
                                    View Channel
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/common/footer.php'; ?>
