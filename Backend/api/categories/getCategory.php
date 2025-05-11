<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow CORS
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Debug information
$debug = [
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'headers' => getallheaders(),
    'get_params' => $_GET,
    'session' => isset($_SESSION) ? $_SESSION : null
];

// Include database connection
require_once '../../config/db.php';

// Start session
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode([
        'error' => 'Unauthorized. Admin access required.',
        'debug' => $debug
    ]);
    exit();
}

// Check if category ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Category ID is required and must be numeric.',
        'debug' => $debug
    ]);
    exit();
}

$categoryId = intval($_GET['id']);

try {
    // Prepare SQL query to get category with product count
    $query = "
        SELECT 
            c.id,
            c.name,
            c.description,
            COUNT(p.id) as product_count
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id
        WHERE c.id = :id
        GROUP BY c.id
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
    $stmt->execute();
    
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$category) {
        http_response_code(404);
        echo json_encode([
            'error' => 'Category not found.',
            'debug' => $debug
        ]);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'category' => $category
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error occurred.',
        'debug' => array_merge($debug, ['sql_error' => $e->getMessage()])
    ]);
} 