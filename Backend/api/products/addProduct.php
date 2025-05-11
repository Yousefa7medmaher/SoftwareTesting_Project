<?php
include '../../config/db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['name'], $data['price'], $data['category'], $data['image'])) {
    echo json_encode(["error" => "Missing required fields."]);
    exit;
}

$name = $data['name'];
$price = $data['price'];
$category = $data['category'];
$image = $data['image'];
$featured = isset($data['featured']) ? ($data['featured'] ? 1 : 0) : 0; 

$sql = "INSERT INTO products (name, price, category, image, featured) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

$stmt->bind_param("ssdsi", $name, $price, $category, $image, $featured);

if ($stmt->execute()) {
    echo json_encode(["message" => "Product added successfully."]);
} else {
    echo json_encode(["error" => "Failed to add product."]);
}
?>
