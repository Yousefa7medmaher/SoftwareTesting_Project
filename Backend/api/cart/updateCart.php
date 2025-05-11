<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
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

// Start session to get user ID
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. Please login first."]);
    exit();
}

include '../../config/db.php';
include '../../config/db_utils.php';

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

// Check if cart_id and quantity are provided
if (!isset($data['cart_id']) || !isset($data['quantity'])) {
    http_response_code(400);
    echo json_encode(["error" => "Cart ID and quantity are required"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_id = intval($data['cart_id']);
$quantity = intval($data['quantity']);

// Validate quantity
if ($quantity <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Quantity must be greater than 0"]);
    exit();
}

// Get cart item and product details
$sql = "SELECT c.id, c.quantity, p.id as product_id, p.stock 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.id = ? AND c.user_id = ?";

$cart_result = executeQuery($conn, $sql, "ii", [$cart_id, $user_id]);

if (isset($cart_result['error'])) {
    http_response_code(500);
    echo json_encode(["error" => $cart_result['error']]);
    $conn->close();
    exit();
}

if (empty($cart_result)) {
    http_response_code(404);
    echo json_encode(["error" => "Cart item not found or does not belong to you"]);
    $conn->close();
    exit();
}

$cart_item = $cart_result[0];

// Check if new quantity exceeds stock
if ($quantity > $cart_item['stock']) {
    http_response_code(400);
    echo json_encode(["error" => "Requested quantity exceeds available stock"]);
    $conn->close();
    exit();
}

// Update cart item quantity
$update_result = executeNonQuery($conn, "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?", "iii", [$quantity, $cart_id, $user_id]);

if ($update_result['success']) {
    echo json_encode([
        "success" => true,
        "message" => "Cart updated successfully",
        "cart_id" => $cart_id,
        "quantity" => $quantity
    ]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to update cart: " . $update_result['error']]);
}

$conn->close();
?> 