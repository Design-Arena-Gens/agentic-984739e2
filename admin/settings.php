<?php
require_once __DIR__ . '/common/header.php';

$success = '';
$error = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $siteName = sanitize($_POST['site_name']);
    $maintenanceMode = isset($_POST['maintenance_mode']) ? 1 : 0;
    $registrationEnabled = isset($_POST['registration_enabled']) ? 1 : 0;

    $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('site_name', '$siteName')
        ON DUPLICATE KEY UPDATE setting_value = '$siteName'");
    $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('maintenance_mode', '$maintenanceMode')
        ON DUPLICATE KEY UPDATE setting_value = '$maintenanceMode'");
    $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('registration_enabled', '$registrationEnabled')
        ON DUPLICATE KEY UPDATE setting_value = '$registrationEnabled'");

    $success = 'Settings updated successfully';
}

// Get current settings
$settings = [];
$result = $conn->query("SELECT * FROM settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Get system info
$phpVersion = phpversion();
$mysqlVersion = $conn->server_info;
$uploadMaxSize = ini_get('upload_max_filesize');
$postMaxSize = ini_get('post_max_size');
?>

<div>
    <h1 class="text-3xl font-bold mb-8">System Settings</h1>

    <?php if ($success): ?>
        <div class="bg-green-900/20 border border-green-500 text-green-500 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- General Settings -->
        <div class="bg-dark-card rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">General Settings</h2>
            <form method="POST">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Site Name</label>
                        <input type="text" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'VidZone'); ?>"
                            class="w-full bg-dark-bg border border-gray-700 rounded-lg px-4 py-2 focus:outline-none focus:border-primary">
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="maintenance_mode" id="maintenance_mode"
                            <?php echo isset($settings['maintenance_mode']) && $settings['maintenance_mode'] ? 'checked' : ''; ?>
                            class="w-4 h-4 text-primary bg-dark-bg border-gray-700 rounded focus:ring-primary">
                        <label for="maintenance_mode" class="text-sm font-medium">Maintenance Mode</label>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="registration_enabled" id="registration_enabled"
                            <?php echo !isset($settings['registration_enabled']) || $settings['registration_enabled'] ? 'checked' : ''; ?>
                            class="w-4 h-4 text-primary bg-dark-bg border-gray-700 rounded focus:ring-primary">
                        <label for="registration_enabled" class="text-sm font-medium">Enable User Registration</label>
                    </div>

                    <button type="submit" name="update_settings" class="bg-primary hover:bg-red-700 px-6 py-2 rounded-lg transition">
                        <i class="fas fa-save mr-2"></i>Save Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- System Information -->
        <div class="bg-dark-card rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">System Information</h2>
            <div class="space-y-4">
                <div class="flex justify-between items-center py-2 border-b border-gray-800">
                    <span class="text-gray-400">PHP Version</span>
                    <span class="font-semibold"><?php echo $phpVersion; ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-800">
                    <span class="text-gray-400">MySQL Version</span>
                    <span class="font-semibold"><?php echo $mysqlVersion; ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-800">
                    <span class="text-gray-400">Upload Max Size</span>
                    <span class="font-semibold"><?php echo $uploadMaxSize; ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-800">
                    <span class="text-gray-400">Post Max Size</span>
                    <span class="font-semibold"><?php echo $postMaxSize; ?></span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-gray-400">Server Time</span>
                    <span class="font-semibold"><?php echo date('Y-m-d H:i:s'); ?></span>
                </div>
            </div>
        </div>

        <!-- Database Stats -->
        <div class="bg-dark-card rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Database Statistics</h2>
            <div class="space-y-4">
                <?php
                $tables = ['users', 'videos', 'comments', 'likes', 'subscriptions', 'notifications'];
                foreach ($tables as $table):
                    $count = $conn->query("SELECT COUNT(*) as count FROM $table")->fetch_assoc()['count'];
                ?>
                    <div class="flex justify-between items-center py-2 border-b border-gray-800">
                        <span class="text-gray-400 capitalize"><?php echo $table; ?></span>
                        <span class="font-semibold"><?php echo number_format($count); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-dark-card rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Quick Actions</h2>
            <div class="space-y-3">
                <a href="/admin/users.php" class="block bg-dark-bg hover:bg-dark-hover p-4 rounded-lg transition">
                    <i class="fas fa-users text-primary mr-3"></i>
                    <span class="font-semibold">Manage Users</span>
                </a>
                <a href="/admin/videos.php" class="block bg-dark-bg hover:bg-dark-hover p-4 rounded-lg transition">
                    <i class="fas fa-video text-primary mr-3"></i>
                    <span class="font-semibold">Manage Videos</span>
                </a>
                <a href="/admin/comments.php" class="block bg-dark-bg hover:bg-dark-hover p-4 rounded-lg transition">
                    <i class="fas fa-comments text-primary mr-3"></i>
                    <span class="font-semibold">Moderate Comments</span>
                </a>
                <a href="/install.php" class="block bg-dark-bg hover:bg-dark-hover p-4 rounded-lg transition" onclick="return confirm('This will reset the database. Continue?')">
                    <i class="fas fa-database text-yellow-500 mr-3"></i>
                    <span class="font-semibold">Reinstall Database</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/common/footer.php'; ?>
