<?php
require_once __DIR__ . '/common/header.php';

$success = '';
$error = '';

// Handle ad actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_ad'])) {
        $title = sanitize($_POST['title']);
        $url = sanitize($_POST['url']);
        $type = sanitize($_POST['type']);

        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image = uniqid() . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../uploads/' . $image);
        }

        $conn->query("INSERT INTO ads (title, image, url, type) VALUES ('$title', '$image', '$url', '$type')");
        $success = 'Ad created successfully';
    } elseif (isset($_POST['delete_ad'])) {
        $adId = (int)$_POST['ad_id'];
        $conn->query("DELETE FROM ads WHERE id = $adId");
        $success = 'Ad deleted successfully';
    } elseif (isset($_POST['toggle_status'])) {
        $adId = (int)$_POST['ad_id'];
        $conn->query("UPDATE ads SET status = IF(status = 'active', 'inactive', 'active') WHERE id = $adId");
        $success = 'Ad status updated';
    }
}

$ads = $conn->query("SELECT * FROM ads ORDER BY created_at DESC");
?>

<div>
    <h1 class="text-3xl font-bold mb-8">Ads Management</h1>

    <?php if ($success): ?>
        <div class="bg-green-900/20 border border-green-500 text-green-500 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <!-- Create Ad Form -->
    <div class="bg-dark-card rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Create New Ad</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <input type="text" name="title" required placeholder="Ad Title"
                    class="bg-dark-bg border border-gray-700 rounded-lg px-4 py-2 focus:outline-none focus:border-primary">
                <input type="url" name="url" placeholder="Ad URL (optional)"
                    class="bg-dark-bg border border-gray-700 rounded-lg px-4 py-2 focus:outline-none focus:border-primary">
                <select name="type" required
                    class="bg-dark-bg border border-gray-700 rounded-lg px-4 py-2 focus:outline-none focus:border-primary">
                    <option value="banner">Banner</option>
                    <option value="sidebar">Sidebar</option>
                    <option value="video">Video</option>
                </select>
                <input type="file" name="image" accept="image/*"
                    class="text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gray-700 file:text-white hover:file:bg-gray-600">
            </div>
            <button type="submit" name="create_ad" class="bg-primary hover:bg-red-700 px-6 py-2 rounded-lg transition">
                <i class="fas fa-plus mr-2"></i>Create Ad
            </button>
        </form>
    </div>

    <!-- Ads List -->
    <div class="bg-dark-card rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark-hover">
                    <tr>
                        <th class="text-left p-4">Ad</th>
                        <th class="text-left p-4">Type</th>
                        <th class="text-left p-4">Impressions</th>
                        <th class="text-left p-4">Clicks</th>
                        <th class="text-left p-4">CTR</th>
                        <th class="text-left p-4">Status</th>
                        <th class="text-left p-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($ad = $ads->fetch_assoc()): ?>
                        <?php $ctr = $ad['impressions'] > 0 ? ($ad['clicks'] / $ad['impressions'] * 100) : 0; ?>
                        <tr class="border-t border-gray-800">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <?php if ($ad['image']): ?>
                                        <img src="/uploads/<?php echo htmlspecialchars($ad['image']); ?>"
                                            alt="<?php echo htmlspecialchars($ad['title']); ?>"
                                            class="w-16 h-16 object-cover rounded">
                                    <?php else: ?>
                                        <div class="w-16 h-16 bg-gray-700 rounded flex items-center justify-center">
                                            <i class="fas fa-ad"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="font-semibold"><?php echo htmlspecialchars($ad['title']); ?></p>
                                        <?php if ($ad['url']): ?>
                                            <a href="<?php echo htmlspecialchars($ad['url']); ?>" target="_blank" class="text-xs text-primary hover:underline">
                                                <?php echo htmlspecialchars($ad['url']); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="bg-gray-700 px-3 py-1 rounded-full text-sm capitalize">
                                    <?php echo htmlspecialchars($ad['type']); ?>
                                </span>
                            </td>
                            <td class="p-4"><?php echo number_format($ad['impressions']); ?></td>
                            <td class="p-4"><?php echo number_format($ad['clicks']); ?></td>
                            <td class="p-4"><?php echo number_format($ctr, 2); ?>%</td>
                            <td class="p-4">
                                <?php if ($ad['status'] === 'active'): ?>
                                    <span class="bg-green-900/20 text-green-400 px-3 py-1 rounded-full text-sm">Active</span>
                                <?php else: ?>
                                    <span class="bg-gray-700 text-gray-300 px-3 py-1 rounded-full text-sm">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4">
                                <div class="flex gap-2">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="ad_id" value="<?php echo $ad['id']; ?>">
                                        <button type="submit" name="toggle_status" class="text-blue-400 hover:text-blue-300" title="Toggle Status">
                                            <i class="fas fa-toggle-on"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="inline" onsubmit="return confirm('Delete this ad?')">
                                        <input type="hidden" name="ad_id" value="<?php echo $ad['id']; ?>">
                                        <button type="submit" name="delete_ad" class="text-red-400 hover:text-red-300" title="Delete Ad">
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
