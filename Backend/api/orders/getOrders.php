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

// Start session to get user ID
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. Please login first."]);
    exit();
}

include '../../config/db.php';

$user_id = $_SESSION['user_id'];

// Get user's orders
$sql = "SELECT o.id, o.total_amount, o.status, o.shipping_address, o.payment_method, o.created_at 
        FROM orders o 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];

while ($order = $result->fetch_assoc()) {
    // Get order items for each order
    $items_sql = "SELECT oi.product_id, oi.quantity, oi.price, p.name, p.image_products 
                  FROM order_items oi 
                  JOIN products p ON oi.product_id = p.id 
                  WHERE oi.order_id = ?";
    
    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->bind_param("i", $order['id']);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    
    $items = [];
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }
    
    $items_stmt->close();
    
    // Add items to order
    $order['items'] = $items;
    $orders[] = $order;
}

echo json_encode([
    "success" => true,
    "orders" => $orders
]);

$stmt->close();
$conn->close();
?> 