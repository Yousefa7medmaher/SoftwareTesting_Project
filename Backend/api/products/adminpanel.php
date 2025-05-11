<?php
include '../../config/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $featured = isset($_POST['featured']) ? 1 : 0;  

    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = $_FILES['image'];
        $imageName = $image['name'];
        $imageTmpName = $image['tmp_name'];
        $imageSize = $image['size'];
        $imageError = $image['error'];

       
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

       
        if (in_array($imageExt, $allowedExtensions)) {
            
            if ($imageSize < 5000000) {
               
                $newImageName = uniqid('', true) . '.' . $imageExt;
                
                $imageUploadPath = $_SERVER['DOCUMENT_ROOT'] . '/Ecommerce/FrontEnd/IMAGES/' . $newImageName;

                if (move_uploaded_file($imageTmpName, $imageUploadPath)) {
                    $sql = "INSERT INTO products (name, price, category, image_products, featured) 
                            VALUES ('$name', '$price', '$category', '$newImageName', '$featured')";

                    if (mysqli_query($conn, $sql)) {
                        echo "Product added successfully!";
                    } else {
                        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
                    }
                } else {
                    echo "Error uploading image. Please check the directory permissions.";
                }
            } else {
                echo "Image size is too large. Maximum allowed size is 5MB.";
            }
        } else {
            echo "Invalid image format. Allowed formats: jpg, jpeg, png, gif.";
        }
    } else {
        echo "Image is required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin Panel - Add Products">
    <title>Admin Panel - Add Product</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<style>
    * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    background-color: #f4f7fb;
    color: #333;
    font-size: 16px;
}

header {
    background: linear-gradient(90deg, gray, black);
    color: white;
    text-align: center;
    padding: 20px;
}

header h1 {
    margin-bottom: 10px;
}

header a {
    color: #fff;
    text-decoration: none;
    font-weight: 500;
    margin-top: 10px;
    display: inline-block;
}

header a:hover {
    text-decoration: underline;
}

main {
    display: flex;
    justify-content: center;
    padding: 30px;
}

form {
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    padding: 40px;
    width: 100%;
    max-width: 600px;
    margin-top: 20px;
}

form label {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 5px;
    display: block;
    color: #555;
}

form input,
form select,
form button {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
    color: #333;
}

form input[type="checkbox"] {
    width: auto;
    margin-right: 10px;
}

form button {
    background: linear-gradient(90deg, gray, black);
    color: white;
    border: none;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

form button:hover {
    background: linear-gradient(90deg, white, gray);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
    transform: translateY(-3px);
}

form button:focus {
    outline: none;
}

form input[type="text"]:focus,
form input[type="number"]:focus,
form select:focus,
form button:focus {
    border-color: #007bff;
}

.alert {
    background-color: #dff0d8;
    color: #3c763d;
    padding: 10px;
    border-radius: 4px;
    margin-top: 10px;
}

.alert.error {
    background-color: #f2dede;
    color: #a94442;
}

</style>
<body>
    <header>
        <h1>Add New Product</h1> 
    </header>

    <main>
        <form action="adminpanel.php" method="POST" enctype="multipart/form-data">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" required>

            <label for="price">Product Price</label>
            <input type="number" id="price" name="price" step="0.01" required>

            <label for="category">Category</label>
            <select id="category" name="category" required>
                <option value="electronics">Electronics</option>
                <option value="clothing">Clothing</option>
                <option value="home">Home</option>
            </select>

            <label for="image">Product Image</label>
            <input type="file" id="image" name="image" accept="image/*" required>

            <label for="featured">Featured</label>
            <input type="checkbox" id="featured" name="featured">

            <button type="submit">Add Product</button>
        </form>
    </main>
</body>
</html>
