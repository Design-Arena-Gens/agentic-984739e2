<?php
require_once __DIR__ . '/common/header.php';
requireLogin();

$userId = getCurrentUserId();
$user = getUserById($userId);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $fullName = sanitize($_POST['full_name'] ?? '');
        $bio = sanitize($_POST['bio'] ?? '');

        $profilePic = $user['profile_pic'];
        $coverPic = $user['cover_pic'];

        // Handle profile pic upload
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadFile($_FILES['profile_pic'], ['image/jpeg', 'image/png', 'image/jpg']);
            if ($upload['success']) {
                $profilePic = $upload['filename'];
            }
        }

        // Handle cover pic upload
        if (isset($_FILES['cover_pic']) && $_FILES['cover_pic']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadFile($_FILES['cover_pic'], ['image/jpeg', 'image/png', 'image/jpg']);
            if ($upload['success']) {
                $coverPic = $upload['filename'];
            }
        }

        $query = "UPDATE users SET full_name = '$fullName', bio = '$bio', profile_pic = '$profilePic', cover_pic = '$coverPic' WHERE id = $userId";
        if ($conn->query($query)) {
            $success = 'Profile updated successfully';
            $user = getUserById($userId);
        } else {
            $error = 'Failed to update profile';
        }
    }

    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            $error = 'Please fill in all password fields';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $error = 'Current password is incorrect';
        } elseif (strlen($newPassword) < 6) {
            $error = 'New password must be at least 6 characters';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password = '$hashedPassword' WHERE id = $userId");
            $success = 'Password changed successfully';
        }
    }

    if (isset($_POST['logout'])) {
        session_destroy();
        header('Location: /index.php');
        exit();
    }
}
?>

<div class="container mx-auto px-4 py-6 max-w-4xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-2">Settings</h1>
        <p class="text-gray-400">Manage your account settings</p>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-900/20 border border-red-500 text-red-500 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-green-900/20 border border-green-500 text-green-500 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-check-circle mr-2"></i>
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <!-- Profile Settings -->
    <div class="bg-dark-card rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Profile Settings</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="space-y-5">
                <!-- Profile Picture -->
                <div>
                    <label class="block text-sm font-medium mb-2">Profile Picture</label>
                    <div class="flex items-center gap-4">
                        <?php if ($user['profile_pic']): ?>
                            <img src="/uploads/<?php echo htmlspecialchars($user['profile_pic']); ?>"
                                alt="Profile" class="w-20 h-20 rounded-full object-cover">
                        <?php else: ?>
                            <div class="w-20 h-20 rounded-full bg-gray-700 flex items-center justify-center">
                                <i class="fas fa-user text-2xl"></i>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="profile_pic" accept="image/*"
                            class="text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-gray-700 file:text-white hover:file:bg-gray-600">
                    </div>
                </div>

                <!-- Cover Picture -->
                <div>
                    <label class="block text-sm font-medium mb-2">Cover Picture</label>
                    <input type="file" name="cover_pic" accept="image/*"
                        class="text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-gray-700 file:text-white hover:file:bg-gray-600">
                </div>

                <!-- Full Name -->
                <div>
                    <label class="block text-sm font-medium mb-2">Full Name</label>
                    <input type="text" name="full_name"
                        value="<?php echo htmlspecialchars($user['full_name'] ?: ''); ?>"
                        class="w-full bg-dark-bg border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-primary"
                        placeholder="Enter your full name">
                </div>

                <!-- Username (Read-only) -->
                <div>
                    <label class="block text-sm font-medium mb-2">Username</label>
                    <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-3 text-gray-500 cursor-not-allowed">
                    <p class="text-xs text-gray-500 mt-1">Username cannot be changed</p>
                </div>

                <!-- Email (Read-only) -->
                <div>
                    <label class="block text-sm font-medium mb-2">Email</label>
                    <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-3 text-gray-500 cursor-not-allowed">
                </div>

                <!-- Bio -->
                <div>
                    <label class="block text-sm font-medium mb-2">Bio</label>
                    <textarea name="bio" rows="4"
                        class="w-full bg-dark-bg border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-primary"
                        placeholder="Tell us about yourself"><?php echo htmlspecialchars($user['bio'] ?: ''); ?></textarea>
                </div>

                <button type="submit" name="update_profile" class="bg-primary hover:bg-red-700 px-6 py-3 rounded-lg font-semibold transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Password Settings -->
    <div class="bg-dark-card rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Change Password</h2>
        <form method="POST">
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium mb-2">Current Password</label>
                    <input type="password" name="current_password"
                        class="w-full bg-dark-bg border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-primary"
                        placeholder="Enter current password">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">New Password</label>
                    <input type="password" name="new_password"
                        class="w-full bg-dark-bg border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-primary"
                        placeholder="Enter new password">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Confirm New Password</label>
                    <input type="password" name="confirm_password"
                        class="w-full bg-dark-bg border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-primary"
                        placeholder="Confirm new password">
                </div>

                <button type="submit" name="change_password" class="bg-primary hover:bg-red-700 px-6 py-3 rounded-lg font-semibold transition">
                    Change Password
                </button>
            </div>
        </form>
    </div>

    <!-- Account Actions -->
    <div class="bg-dark-card rounded-lg p-6">
        <h2 class="text-xl font-bold mb-4">Account Actions</h2>
        <form method="POST" onsubmit="return confirm('Are you sure you want to logout?')">
            <button type="submit" name="logout" class="bg-gray-700 hover:bg-gray-600 px-6 py-3 rounded-lg font-semibold transition">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/common/footer.php'; ?>
