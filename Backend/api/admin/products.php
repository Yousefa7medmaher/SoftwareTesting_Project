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
        // Fetch all products with category names
        try {
            $stmt = $conn->prepare("
                SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY p.id DESC
            ");
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['products' => $products]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
    case 'POST':
        // Add new product
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['name']) || !isset($data['category_id']) || 
            !isset($data['price']) || !isset($data['stock'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }
        
        try {
            $stmt = $conn->prepare("
                INSERT INTO products (name, category_id, price, stock, description, image_products) 
                VALUES (:name, :category_id, :price, :stock, :description, :image_products)
            ");
            
            $stmt->execute([
                ':name' => $data['name'],
                ':category_id' => $data['category_id'],
                ':price' => $data['price'],
                ':stock' => $data['stock'],
                ':description' => $data['description'] ?? '',
                ':image_products' => $data['image_products'] ?? ''
            ]);
            
            $productId = $conn->lastInsertId();
            
            echo json_encode([
                'message' => 'Product added successfully',
                'product_id' => $productId
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
    case 'PUT':
        // Update existing product
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID is required']);
            exit;
        }
        
        try {
            $stmt = $conn->prepare("
                UPDATE products 
                SET name = :name,
                    category_id = :category_id,
                    price = :price,
                    stock = :stock,
                    description = :description,
                    image_products = :image_products
                WHERE id = :id
            ");
            
            $stmt->execute([
                ':id' => $data['id'],
                ':name' => $data['name'],
                ':category_id' => $data['category_id'],
                ':price' => $data['price'],
                ':stock' => $data['stock'],
                ':description' => $data['description'] ?? '',
                ':image_products' => $data['image_products'] ?? ''
            ]);
            
            echo json_encode(['message' => 'Product updated successfully']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
    case 'DELETE':
        // Delete product
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID is required']);
            exit;
        }
        
        try {
            // First check if product exists in cart
            $stmt = $conn->prepare("SELECT COUNT(*) FROM cart WHERE product_id = :id");
            $stmt->execute([':id' => $data['id']]);
            $inCart = $stmt->fetchColumn() > 0;
            
            if ($inCart) {
                http_response_code(400);
                echo json_encode(['error' => 'Cannot delete product that is in users\' carts']);
                exit;
            }
            
            // Delete the product
            $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
            $stmt->execute([':id' => $data['id']]);
            
            echo json_encode(['message' => 'Product deleted successfully']);
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