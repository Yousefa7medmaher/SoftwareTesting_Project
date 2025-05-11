<?php
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session before any output
session_start();

// Debug information
$debug = [
    "request_method" => $_SERVER['REQUEST_METHOD'],
    "headers" => getallheaders(),
    "post_data" => file_get_contents("php://input"),
    "session" => $_SESSION
];

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "error" => "Method not allowed",
        "debug" => $debug
    ]);
    exit();
}

// Include database connection
include '../../config/db.php';
include '../../config/db_utils.php';

// Get user ID from session
$user_id = null;

// Check if user is logged in via session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $debug["auth_method"] = "session";
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

// Check if product_id and quantity are provided
if (!isset($data['product_id']) || !isset($data['quantity'])) {
    http_response_code(400);
    echo json_encode([
        "error" => "Product ID and quantity are required",
        "debug" => $debug
    ]);
    exit();
}

$product_id = intval($data['product_id']);
$quantity = intval($data['quantity']);

// Validate quantity
if ($quantity <= 0) {
    http_response_code(400);
    echo json_encode([
        "error" => "Quantity must be greater than 0",
        "debug" => $debug
    ]);
    exit();
}

try {
    // Check if product exists and has enough stock
    $product_result = executeQuery($conn, "SELECT stock FROM products WHERE id = ?", "i", [$product_id]);

    if (isset($product_result['error'])) {
        http_response_code(500);
        echo json_encode([
            "error" => "Database error: " . $product_result['error'],
            "debug" => $debug
        ]);
        exit();
    }

    if (empty($product_result)) {
        http_response_code(404);
        echo json_encode([
            "error" => "Product not found",
            "debug" => $debug
        ]);
        exit();
    }

    $product = $product_result[0];
    if ($product['stock'] < $quantity) {
        http_response_code(400);
        echo json_encode([
            "error" => "Not enough stock available",
            "debug" => $debug
        ]);
        exit();
    }

    // Check if product is already in cart
    $cart_result = executeQuery($conn, "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?", "ii", [$user_id, $product_id]);

    if (isset($cart_result['error'])) {
        http_response_code(500);
        echo json_encode([
            "error" => "Database error: " . $cart_result['error'],
            "debug" => $debug
        ]);
        exit();
    }

    if (!empty($cart_result)) {
        // Update existing cart item
        $cart_item = $cart_result[0];
        $new_quantity = $cart_item['quantity'] + $quantity;
        
        // Check if new quantity exceeds stock
        if ($new_quantity > $product['stock']) {
            http_response_code(400);
            echo json_encode([
                "error" => "Adding this quantity would exceed available stock",
                "debug" => $debug
            ]);
            exit();
        }
        
        $update_result = executeNonQuery($conn, "UPDATE cart SET quantity = ? WHERE id = ?", "ii", [$new_quantity, $cart_item['id']]);
        
        if ($update_result['success']) {
            echo json_encode([
                "success" => true,
                "message" => "Cart updated successfully",
                "cart_id" => $cart_item['id'],
                "quantity" => $new_quantity,
                "debug" => $debug
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "error" => "Failed to update cart: " . $update_result['error'],
                "debug" => $debug
            ]);
        }
    } else {
        // Add new cart item
        $add_result = executeNonQuery($conn, "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)", "iii", [$user_id, $product_id, $quantity]);
        
        if ($add_result['success']) {
            echo json_encode([
                "success" => true,
                "message" => "Product added to cart successfully",
                "cart_id" => $add_result['insert_id'],
                "debug" => $debug
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "error" => "Failed to add product to cart: " . $add_result['error'],
                "debug" => $debug
            ]);
        }
    }
} catch (Exception $e) {
    // Log the error
    error_log("Cart error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        "error" => "An error occurred: " . $e->getMessage(),
        "debug" => $debug
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
