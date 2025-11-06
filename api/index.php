<?php
// API endpoint for Vercel serverless function compatibility
header('Content-Type: application/json');

echo json_encode([
    'status' => 'success',
    'message' => 'VidZone API is running',
    'version' => '1.0.0'
]);
?>
