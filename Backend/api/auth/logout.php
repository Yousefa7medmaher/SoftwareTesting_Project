<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start the session
session_start();

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy the session
    session_destroy();
    
    echo json_encode([
        "success" => true,
        "message" => "Logged out successfully"
    ]);
} else {
    http_response_code(401);
    echo json_encode([
        "error" => "Not logged in"
    ]);
}
?> 