<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit();
}

include '../../config/db.php';

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

// Check if required fields are provided
if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(["error" => "Name, email, and password are required"]);
    exit();
}

$name = $data['name'];
$email = $data['email'];
$password = $data['password'];

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid email format"]);
    exit();
}

// Check if email already exists
$check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$check_result = $check_stmt->get_result();


if ($check_result->num_rows > 0) {
    http_response_code(409);
    echo json_encode(["error" => "Email already registered"]);
    $check_stmt->close();
    $conn->close();
    exit();
}

$check_stmt->close();

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Default role for new users
$role = 'user'; // Role can be 'user' or 'admin'

// Prepare SQL statement to prevent SQL injection
$stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Registration successful"
    ]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Registration failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>