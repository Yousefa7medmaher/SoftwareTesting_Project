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

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

// Check if required fields are provided
if (!isset($data['shipping_address']) || !isset($data['payment_method'])) {
    http_response_code(400);
    echo json_encode(["error" => "Shipping address and payment method are required"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$shipping_address = $data['shipping_address'];
$payment_method = $data['payment_method'];

// Start transaction
$conn->begin_transaction();

try {
    // Get cart items
    $cart_sql = "SELECT c.id as cart_id, c.product_id, c.quantity, p.price, p.stock, p.name 
                 FROM cart c 
                 JOIN products p ON c.product_id = p.id 
                 WHERE c.user_id = ?";
    
    $cart_stmt = $conn->prepare($cart_sql);
    $cart_stmt->bind_param("i", $user_id);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();
    
    if ($cart_result->num_rows === 0) {
        throw new Exception("Your cart is empty");
    }
    
    $cart_items = [];
    $total_amount = 0;
    
    while ($row = $cart_result->fetch_assoc()) {
        // Check if enough stock
        if ($row['quantity'] > $row['stock']) {
            throw new Exception("Not enough stock for product: " . $row['name']);
        }
        
        $subtotal = $row['price'] * $row['quantity'];
        $total_amount += $subtotal;
        
        $cart_items[] = $row;
    }
    
    $cart_stmt->close();
    
    // Create order
    $order_sql = "INSERT INTO orders (user_id, total_amount, shipping_address, payment_method) 
                  VALUES (?, ?, ?, ?)";
    
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param("idss", $user_id, $total_amount, $shipping_address, $payment_method);
    $order_stmt->execute();
    
    $order_id = $conn->insert_id;
    $order_stmt->close();
    
    // Create order items and update stock
    foreach ($cart_items as $item) {
        // Insert order item
        $order_item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                          VALUES (?, ?, ?, ?)";
        
        $order_item_stmt = $conn->prepare($order_item_sql);
        $order_item_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
        $order_item_stmt->execute();
        $order_item_stmt->close();
        
        // Update product stock
        $update_stock_sql = "UPDATE products SET stock = stock - ? WHERE id = ?";
        
        $update_stock_stmt = $conn->prepare($update_stock_sql);
        $update_stock_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
        $update_stock_stmt->execute();
        $update_stock_stmt->close();
    }
    
    // Clear user's cart
    $clear_cart_sql = "DELETE FROM cart WHERE user_id = ?";
    
    $clear_cart_stmt = $conn->prepare($clear_cart_sql);
    $clear_cart_stmt->bind_param("i", $user_id);
    $clear_cart_stmt->execute();
    $clear_cart_stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        "success" => true,
        "message" => "Order created successfully",
        "order_id" => $order_id,
        "total_amount" => $total_amount
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

$conn->close();
?> 