<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow CORS
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
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
    'put_data' => file_get_contents('php://input'),
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

// Check if request method is PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        'error' => 'Method not allowed. Use PUT.',
        'debug' => $debug
    ]);
    exit();
}

// Get PUT data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['id']) || !is_numeric($data['id'])) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Category ID is required and must be numeric.',
        'debug' => array_merge($debug, ['received_data' => $data])
    ]);
    exit();
}

if (!isset($data['name']) || empty(trim($data['name']))) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Category name is required.',
        'debug' => array_merge($debug, ['received_data' => $data])
    ]);
    exit();
}

try {
    // Check if category exists
    $checkStmt = $conn->prepare("SELECT id FROM categories WHERE id = :id");
    $checkStmt->bindParam(':id', $data['id']);
    $checkStmt->execute();
    
    if (!$checkStmt->fetch()) {
        http_response_code(404);
        echo json_encode([
            'error' => 'Category not found.',
            'debug' => array_merge($debug, ['received_data' => $data])
        ]);
        exit();
    }
    
    // Check if name is already taken by another category
    $nameCheckStmt = $conn->prepare("SELECT id FROM categories WHERE name = :name AND id != :id");
    $nameCheckStmt->bindParam(':name', $data['name']);
    $nameCheckStmt->bindParam(':id', $data['id']);
    $nameCheckStmt->execute();
    
    if ($nameCheckStmt->fetch()) {
        http_response_code(409);
        echo json_encode([
            'error' => 'Another category with this name already exists.',
            'debug' => array_merge($debug, ['received_data' => $data])
        ]);
        exit();
    }
    
    // Prepare SQL query to update category
    $query = "UPDATE categories SET name = :name, description = :description WHERE id = :id";
    $stmt = $conn->prepare($query);
    
    // Bind parameters
    $stmt->bindParam(':id', $data['id']);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':description', $data['description'] ?? null);
    
    // Execute query
    $stmt->execute();
    
    // Fetch the updated category with product count
    $selectStmt = $conn->prepare("
        SELECT 
            c.id,
            c.name,
            c.description,
            COUNT(p.id) as product_count
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id
        WHERE c.id = :id
        GROUP BY c.id
    ");
    $selectStmt->bindParam(':id', $data['id']);
    $selectStmt->execute();
    
    $updatedCategory = $selectStmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Category updated successfully.',
        'category' => $updatedCategory
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error occurred.',
        'debug' => array_merge($debug, [
            'sql_error' => $e->getMessage(),
            'received_data' => $data
        ])
    ]);
}
?> 