<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit();
}

include '../../config/db.php';
include '../../config/db_utils.php';

// Get user ID from Authorization header
$headers = getallheaders();
$user_id = null;

// Debug information
$debug = [
    "session" => isset($_SESSION) ? "Session exists" : "No session",
    "headers" => $headers,
    "post_data" => $_POST
];

// Check if user is logged in via session
session_start();
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $debug["auth_method"] = "session";
} 
// If not in session, check Authorization header
else if (isset($headers['Authorization'])) {
    $token = $headers['Authorization'];
    $debug["auth_method"] = "token";
    
    // Verify token and get user ID
    $token_result = executeQuery($conn, "SELECT user_id FROM user_tokens WHERE token = ? AND expires_at > NOW()", "s", [$token]);
    
    if (!empty($token_result)) {
        $user_id = $token_result[0]['user_id'];
        $debug["token_valid"] = true;
    } else {
        $debug["token_valid"] = false;
    }
}

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);
$debug["json_data"] = $data;

// If still no user ID, check for user data in request body
if (!$user_id && isset($data['user_id'])) {
    $user_id = intval($data['user_id']);
    $debug["auth_method"] = "request_body";
}

// Check if user is logged in
if (!$user_id) {
    http_response_code(401);
    echo json_encode([
        "error" => "Unauthorized. Please login first.",
        "debug" => $debug
    ]);
    exit();
}

// Check if cart_id is provided
if (!isset($data['cart_id'])) {
    http_response_code(400);
    echo json_encode([
        "error" => "Cart ID is required",
        "debug" => $debug
    ]);
    exit();
}

$cart_id = intval($data['cart_id']);

// Verify that the cart item belongs to the user
$check_result = executeQuery($conn, "SELECT id FROM cart WHERE id = ? AND user_id = ?", "ii", [$cart_id, $user_id]);

if (isset($check_result['error'])) {
    http_response_code(500);
    echo json_encode([
        "error" => $check_result['error'],
        "debug" => $debug
    ]);
    $conn->close();
    exit();
}

if (empty($check_result)) {
    http_response_code(404);
    echo json_encode([
        "error" => "Cart item not found or does not belong to you",
        "debug" => $debug
    ]);
    $conn->close();
    exit();
}

// Remove the item from cart
$remove_result = executeNonQuery($conn, "DELETE FROM cart WHERE id = ? AND user_id = ?", "ii", [$cart_id, $user_id]);

if ($remove_result['success']) {
    echo json_encode([
        "success" => true,
        "message" => "Item removed from cart successfully",
        "debug" => $debug
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "error" => "Failed to remove item from cart: " . $remove_result['error'],
        "debug" => $debug
    ]);
}

$conn->close();
?>
