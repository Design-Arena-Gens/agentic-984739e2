<?php
// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'vidzone_db');

$success = true;
$messages = [];

try {
    // Connect to MySQL
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Create database
    $conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $conn->select_db(DB_NAME);
    $messages[] = "Database created successfully";

    // Users table
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100),
        profile_pic VARCHAR(255),
        cover_pic VARCHAR(255),
        bio TEXT,
        is_verified BOOLEAN DEFAULT FALSE,
        is_admin BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $messages[] = "Users table created";

    // Videos table
    $conn->query("CREATE TABLE IF NOT EXISTS videos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        video_file VARCHAR(255) NOT NULL,
        thumbnail VARCHAR(255),
        category VARCHAR(50),
        tags VARCHAR(255),
        views INT DEFAULT 0,
        status VARCHAR(20) DEFAULT 'published',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    $messages[] = "Videos table created";

    // Comments table
    $conn->query("CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        video_id INT NOT NULL,
        user_id INT NOT NULL,
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    $messages[] = "Comments table created";

    // Likes table
    $conn->query("CREATE TABLE IF NOT EXISTS likes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        video_id INT NOT NULL,
        user_id INT NOT NULL,
        type TINYINT NOT NULL COMMENT '1=like, 0=dislike',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_like (video_id, user_id),
        FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    $messages[] = "Likes table created";

    // Subscriptions table
    $conn->query("CREATE TABLE IF NOT EXISTS subscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        channel_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_subscription (user_id, channel_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (channel_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    $messages[] = "Subscriptions table created";

    // Notifications table
    $conn->query("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type VARCHAR(50) NOT NULL,
        message TEXT NOT NULL,
        link VARCHAR(255),
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    $messages[] = "Notifications table created";

    // Ads table
    $conn->query("CREATE TABLE IF NOT EXISTS ads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        image VARCHAR(255),
        url VARCHAR(255),
        type VARCHAR(50) COMMENT 'banner, video, sidebar',
        status VARCHAR(20) DEFAULT 'active',
        impressions INT DEFAULT 0,
        clicks INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $messages[] = "Ads table created";

    // Settings table
    $conn->query("CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    $messages[] = "Settings table created";

    // Create default admin user
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT IGNORE INTO users (username, email, password, full_name, is_admin)
                  VALUES ('admin', 'admin@vidzone.com', '$adminPassword', 'Administrator', TRUE)");
    $messages[] = "Default admin user created (username: admin, password: admin123)";

    // Create uploads directory
    $uploadDir = __DIR__ . '/uploads';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
        $messages[] = "Uploads directory created";
    }

    $messages[] = "Installation completed successfully!";

} catch (Exception $e) {
    $success = false;
    $messages[] = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VidZone - Installation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#ff0000',
                        dark: { bg: '#0f0f0f', card: '#1f1f1f', hover: '#2f2f2f' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-dark-bg text-white min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full bg-dark-card rounded-lg shadow-xl p-8">
        <div class="text-center mb-8">
            <i class="fas fa-play-circle text-primary text-6xl mb-4"></i>
            <h1 class="text-3xl font-bold">VidZone Installation</h1>
        </div>

        <div class="space-y-3">
            <?php foreach ($messages as $message): ?>
                <div class="flex items-start gap-3 p-4 rounded-lg <?php echo $success ? 'bg-green-900/20 text-green-400' : 'bg-red-900/20 text-red-400'; ?>">
                    <i class="fas <?php echo $success ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mt-1"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($success): ?>
            <div class="mt-8 text-center space-y-4">
                <p class="text-gray-400">Database and tables have been created successfully!</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="/index.php" class="bg-primary hover:bg-red-700 px-6 py-3 rounded-full transition">
                        Go to Homepage
                    </a>
                    <a href="/admin/login.php" class="bg-gray-700 hover:bg-gray-600 px-6 py-3 rounded-full transition">
                        Admin Login
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="mt-8 text-center">
                <button onclick="location.reload()" class="bg-primary hover:bg-red-700 px-6 py-3 rounded-full transition">
                    <i class="fas fa-redo mr-2"></i>Retry Installation
                </button>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
