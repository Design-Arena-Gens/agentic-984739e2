<?php
session_start();
date_default_timezone_set('UTC');

// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'vidzone_db');

// Site configuration
define('SITE_NAME', 'VidZone Admin');
define('ADMIN_URL', '/admin');

// Database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Get current admin ID
function getAdminId() {
    return $_SESSION['admin_id'] ?? null;
}

// Require admin login
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: /admin/login.php');
        exit();
    }
}

// Sanitize input
function sanitize($data) {
    global $conn;
    return $conn->real_escape_string(trim(strip_tags($data)));
}
?>
