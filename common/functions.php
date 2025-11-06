<?php
require_once __DIR__ . '/config.php';

// Sanitize input
function sanitize($data) {
    global $conn;
    return $conn->real_escape_string(trim(strip_tags($data)));
}

// Format time ago
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;

    if ($difference < 60) return 'Just now';
    if ($difference < 3600) return floor($difference / 60) . ' minutes ago';
    if ($difference < 86400) return floor($difference / 3600) . ' hours ago';
    if ($difference < 604800) return floor($difference / 86400) . ' days ago';
    if ($difference < 2592000) return floor($difference / 604800) . ' weeks ago';
    if ($difference < 31536000) return floor($difference / 2592000) . ' months ago';
    return floor($difference / 31536000) . ' years ago';
}

// Format number
function formatNumber($num) {
    if ($num >= 1000000) return round($num / 1000000, 1) . 'M';
    if ($num >= 1000) return round($num / 1000, 1) . 'K';
    return $num;
}

// Get user by ID
function getUserById($userId) {
    global $conn;
    $userId = (int)$userId;
    $result = $conn->query("SELECT * FROM users WHERE id = $userId");
    return $result->fetch_assoc();
}

// Get video by ID
function getVideoById($videoId) {
    global $conn;
    $videoId = (int)$videoId;
    $result = $conn->query("SELECT v.*, u.username, u.profile_pic,
        (SELECT COUNT(*) FROM likes WHERE video_id = v.id AND type = 1) as likes,
        (SELECT COUNT(*) FROM likes WHERE video_id = v.id AND type = 0) as dislikes,
        (SELECT COUNT(*) FROM comments WHERE video_id = v.id) as comment_count
        FROM videos v
        JOIN users u ON v.user_id = u.id
        WHERE v.id = $videoId");
    return $result->fetch_assoc();
}

// Check if user is subscribed
function isSubscribed($userId, $channelId) {
    global $conn;
    $userId = (int)$userId;
    $channelId = (int)$channelId;
    $result = $conn->query("SELECT id FROM subscriptions WHERE user_id = $userId AND channel_id = $channelId");
    return $result->num_rows > 0;
}

// Get subscriber count
function getSubscriberCount($channelId) {
    global $conn;
    $channelId = (int)$channelId;
    $result = $conn->query("SELECT COUNT(*) as count FROM subscriptions WHERE channel_id = $channelId");
    $row = $result->fetch_assoc();
    return $row['count'];
}

// Check if user liked video
function hasUserLikedVideo($userId, $videoId) {
    global $conn;
    $userId = (int)$userId;
    $videoId = (int)$videoId;
    $result = $conn->query("SELECT type FROM likes WHERE user_id = $userId AND video_id = $videoId");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['type'];
    }
    return null;
}

// Get notification count
function getUnreadNotificationCount($userId) {
    global $conn;
    $userId = (int)$userId;
    $result = $conn->query("SELECT COUNT(*) as count FROM notifications WHERE user_id = $userId AND is_read = 0");
    $row = $result->fetch_assoc();
    return $row['count'];
}

// Create notification
function createNotification($userId, $type, $message, $link = '') {
    global $conn;
    $userId = (int)$userId;
    $type = sanitize($type);
    $message = sanitize($message);
    $link = sanitize($link);
    $conn->query("INSERT INTO notifications (user_id, type, message, link) VALUES ($userId, '$type', '$message', '$link')");
}

// Generate thumbnail
function generateThumbnail($videoPath, $thumbnailPath) {
    // Placeholder - would need FFmpeg in production
    return true;
}

// Upload file
function uploadFile($file, $allowedTypes = ['video/mp4', 'video/webm', 'image/jpeg', 'image/png', 'image/jpg']) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload failed'];
    }

    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = UPLOAD_DIR . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $filename];
    }

    return ['success' => false, 'error' => 'Failed to move file'];
}
?>
