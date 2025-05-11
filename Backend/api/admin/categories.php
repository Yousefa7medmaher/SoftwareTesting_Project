<?php
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

require_once '../../config/database.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get database connection
$conn = getConnection();

// Handle different HTTP methods
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Fetch all categories with product count
        try {
            $stmt = $conn->prepare("
                SELECT c.*, COUNT(p.id) as product_count 
                FROM categories c 
                LEFT JOIN products p ON c.id = p.category_id 
                GROUP BY c.id 
                ORDER BY c.name ASC
            ");
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['categories' => $categories]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
    case 'POST':
        // Add new category
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Category name is required']);
            exit;
        }
        
        try {
            $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (:name, :description)");
            $stmt->execute([
                ':name' => $data['name'],
                ':description' => $data['description'] ?? ''
            ]);
            
            $categoryId = $conn->lastInsertId();
            
            echo json_encode([
                'message' => 'Category added successfully',
                'category_id' => $categoryId
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
    case 'PUT':
        // Update existing category
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id']) || !isset($data['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Category ID and name are required']);
            exit;
        }
        
        try {
            $stmt = $conn->prepare("UPDATE categories SET name = :name, description = :description WHERE id = :id");
            $stmt->execute([
                ':id' => $data['id'],
                ':name' => $data['name'],
                ':description' => $data['description'] ?? ''
            ]);
            
            echo json_encode(['message' => 'Category updated successfully']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
    case 'DELETE':
        // Delete category
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Category ID is required']);
            exit;
        }
        
        try {
            // First check if category has products
            $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = :id");
            $stmt->execute([':id' => $data['id']]);
            $hasProducts = $stmt->fetchColumn() > 0;
            
            if ($hasProducts) {
                http_response_code(400);
                echo json_encode(['error' => 'Cannot delete category that has products']);
                exit;
            }
            
            // Delete the category
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = :id");
            $stmt->execute([':id' => $data['id']]);
            
            echo json_encode(['message' => 'Category deleted successfully']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
} 