<?php
require_once __DIR__ . '/common/header.php';

$success = '';
$error = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)($_POST['user_id'] ?? 0);

    if (isset($_POST['delete_user']) && $userId) {
        $conn->query("DELETE FROM users WHERE id = $userId");
        $success = 'User deleted successfully';
    } elseif (isset($_POST['toggle_admin']) && $userId) {
        $conn->query("UPDATE users SET is_admin = NOT is_admin WHERE id = $userId");
        $success = 'Admin status updated';
    } elseif (isset($_POST['toggle_verified']) && $userId) {
        $conn->query("UPDATE users SET is_verified = NOT is_verified WHERE id = $userId");
        $success = 'Verification status updated';
    }
}

$search = sanitize($_GET['search'] ?? '');
$query = "SELECT * FROM users WHERE 1=1";
if ($search) {
    $query .= " AND (username LIKE '%$search%' OR email LIKE '%$search%' OR full_name LIKE '%$search%')";
}
$query .= " ORDER BY created_at DESC";

$users = $conn->query($query);
?>

<div>
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold">Users Management</h1>
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Search users..."
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

    <?php if ($error): ?>
        <div class="bg-red-900/20 border border-red-500 text-red-500 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="bg-dark-card rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark-hover">
                    <tr>
                        <th class="text-left p-4">User</th>
                        <th class="text-left p-4">Email</th>
                        <th class="text-left p-4">Status</th>
                        <th class="text-left p-4">Joined</th>
                        <th class="text-left p-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <tr class="border-t border-gray-800">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <?php if ($user['profile_pic']): ?>
                                        <img src="/uploads/<?php echo htmlspecialchars($user['profile_pic']); ?>"
                                            alt="<?php echo htmlspecialchars($user['username']); ?>"
                                            class="w-10 h-10 rounded-full object-cover">
                                    <?php else: ?>
                                        <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="font-semibold">
                                            <?php echo htmlspecialchars($user['username']); ?>
                                            <?php if ($user['is_verified']): ?>
                                                <i class="fas fa-check-circle text-primary ml-1"></i>
                                            <?php endif; ?>
                                        </p>
                                        <p class="text-sm text-gray-400"><?php echo htmlspecialchars($user['full_name'] ?: 'N/A'); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-gray-400"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="p-4">
                                <?php if ($user['is_admin']): ?>
                                    <span class="bg-primary/20 text-primary px-3 py-1 rounded-full text-sm">Admin</span>
                                <?php else: ?>
                                    <span class="bg-gray-700 text-gray-300 px-3 py-1 rounded-full text-sm">User</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-gray-400 text-sm"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td class="p-4">
                                <div class="flex gap-2">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="toggle_verified" class="text-blue-400 hover:text-blue-300" title="Toggle Verified">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="toggle_admin" class="text-yellow-400 hover:text-yellow-300" title="Toggle Admin">
                                            <i class="fas fa-shield-alt"></i>
                                        </button>
                                    </form>
                                    <a href="/channel.php?id=<?php echo $user['id']; ?>" class="text-green-400 hover:text-green-300" title="View Profile">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form method="POST" class="inline" onsubmit="return confirm('Delete this user?')">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" class="text-red-400 hover:text-red-300" title="Delete User">
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
