<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';
require_once '../../middleware/auth.php';

// Check if user is admin
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['name']) || empty($data['name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Category name is required']);
    exit();
}

try {
    $query = "INSERT INTO categories (name, description) VALUES (:name, :description)";
    $stmt = $conn->prepare($query);
    
    $stmt->execute([
        ':name' => $data['name'],
        ':description' => $data['description'] ?? null
    ]);
    
    $categoryId = $conn->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Category added successfully',
        'category' => [
            'id' => $categoryId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'products_count' => 0
        ]
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 