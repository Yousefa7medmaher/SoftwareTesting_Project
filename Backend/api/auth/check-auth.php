<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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
    // User is authenticated
    echo json_encode([
        "authenticated" => true,
        "user" => [
            "id" => $_SESSION['user_id'],
            "name" => $_SESSION['user_name'],
            "email" => $_SESSION['user_email']
        ]
    ]);
} else {
    // User is not authenticated
    http_response_code(401);
    echo json_encode([
        "authenticated" => false,
        "message" => "Not authenticated"
    ]);
}
?> 