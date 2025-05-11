<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include '../../config/db.php';
include '../../config/db_utils.php';

$category_id = isset($_GET['category']) ? intval($_GET['category']) : null;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Base query
$sql = "SELECT p.id, p.name, p.description, p.price, p.category_id, p.image_products, p.stock, c.name as category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id";
$params = [];
$types = "";

// Add conditions if needed
if ($category_id !== null) {
    $sql .= " WHERE p.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

if (!empty($search)) {
    $sql .= ($category_id !== null) ? " AND" : " WHERE";
    $sql .= " (p.name LIKE ? OR p.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

// Execute the query using our utility function
$products = executeQuery($conn, $sql, $types, $params);

// Check for errors
if (isset($products['error'])) {
    http_response_code(500);
    echo json_encode(["error" => $products['error']]);
    $conn->close();
    exit();
}

echo json_encode($products);
$conn->close();
?>
