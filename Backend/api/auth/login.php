<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Debug information
$debug = [
    "request_method" => $_SERVER['REQUEST_METHOD'],
    "headers" => getallheaders(),
    "post_data" => file_get_contents("php://input")
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

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);
$debug["decoded_data"] = $data;

// Check if email and password are provided
if (!isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode([
        "error" => "Email and password are required",
        "debug" => $debug
    ]);
    exit();
}

$email = $data['email'];
$password = $data['password'];

try {
    // Prepare SQL statement to prevent SQL injection
    // Using the exact column names from the users table
    $stmt = $conn->prepare("SELECT id, name, email, password, role, created_at FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // User not found
        http_response_code(401);
        echo json_encode([
            "error" => "Invalid email or password",
            "debug" => $debug
        ]);
        exit();
    }

    $user = $result->fetch_assoc();
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        // Password is incorrect
        http_response_code(401);
        echo json_encode([
            "error" => "Invalid email or password",
            "debug" => $debug
        ]);
        exit();
    }

    // Update last login (if you have a last_login column)
    // If you don't have this column, you can remove this section
    if (isset($user['last_login'])) {
        $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $updateStmt->bind_param("i", $user['id']);
        $updateStmt->execute();
        $updateStmt->close();
    }

    // Start session and store user info
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    
    // For admin users, set additional permissions
    if ($user['role'] === 'admin') {
        $_SESSION['is_admin'] = true;
        $_SESSION['permissions'] = [
            'products' => true,
            'categories' => true,
            'orders' => true,
            'users' => true,
            'dashboard' => true
        ];
    }
    
    $debug["session_id"] = session_id();
    $debug["session_data"] = $_SESSION;
    
    // Return user data (excluding password) and redirect information
    unset($user['password']);
    
    // Determine redirect URL based on user role
    $redirectUrl = $user['role'] === 'admin' ? '../admin/dashboard.html' : '../index.html';
    
    echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "user" => $user,
        "redirect" => $redirectUrl,
        "isAdmin" => $user['role'] === 'admin',
        "permissions" => $user['role'] === 'admin' ? [
            'products' => true,
            'categories' => true,
            'orders' => true,
            'users' => true,
            'dashboard' => true
        ] : null,
        "debug" => $debug
    ]);

} catch (Exception $e) {
    // Log the error (you should implement proper error logging)
    error_log("Login error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        "error" => "An error occurred. Please try again later.",
        "debug" => $debug
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?> 