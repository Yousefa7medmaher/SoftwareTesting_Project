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

try {
    // Prepare SQL query to get categories with product count
    $query = "
        SELECT 
            c.id,
            c.name,
            c.description,
            COUNT(p.id) as product_count
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id
        GROUP BY c.id
        ORDER BY c.name ASC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error occurred.',
        'debug' => array_merge($debug, ['sql_error' => $e->getMessage()])
    ]);
}
?> 