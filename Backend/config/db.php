<?php
$host = "localhost";
$user = "root";
$password = "1234";
$database = "ecommerce_22";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
