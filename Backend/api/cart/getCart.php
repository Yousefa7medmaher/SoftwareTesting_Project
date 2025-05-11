<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if it's a GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit();
}

include '../../config/db.php';
include '../../config/db_utils.php';

// Start session
session_start();

// Get user ID from session
$user_id = null;

// Debug information
$debug = [
    "session" => isset($_SESSION) ? "Session exists" : "No session",
    "headers" => getallheaders(),
    "get_params" => $_GET
];

// Check if user is logged in via session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $debug["auth_method"] = "session";
} 

// If still no user ID, check for user data in URL parameters
if (!$user_id && isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    $debug["auth_method"] = "url_param";
}

// If still no user ID, check for user data in request body
if (!$user_id) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['user_id'])) {
        $user_id = intval($data['user_id']);
        $debug["auth_method"] = "request_body";
    }
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

// Get cart items with product details
$sql = "SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, p.image_products, p.stock 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?";

$cart_items = executeQuery($conn, $sql, "i", [$user_id]);

if (isset($cart_items['error'])) {
    http_response_code(500);
    echo json_encode([
        "error" => $cart_items['error'],
        "debug" => $debug
    ]);
    $conn->close();
    exit();
}

// Calculate total price
$total_price = 0;
foreach ($cart_items as &$item) {
    $item['subtotal'] = $item['price'] * $item['quantity'];
    $total_price += $item['subtotal'];
}

echo json_encode([
    "success" => true,
    "cart_items" => $cart_items,
    "total_price" => $total_price,
    "item_count" => count($cart_items),
    "debug" => $debug
]);

$conn->close();
?>
