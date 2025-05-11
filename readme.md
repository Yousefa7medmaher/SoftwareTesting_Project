# Ecommerce Project

This is a full-stack Ecommerce application built using PHP (Vanilla) for the backend and HTML, CSS, JavaScript for the frontend. The project includes basic features such as product listings, product search and filtering by category, cart management, and a simple emulated payment process. Additionally, it demonstrates the implementation of automation testing using Selenium with Java.

## Table of Contents

1. [Project Overview](#project-overview)
2. [Frontend Features](#frontend-features)
3. [Backend Features](#backend-features)
4. [Database Schema](#database-schema)
5. [Automation Testing](#automation-testing)
6. [Installation](#installation)
7. [Usage](#usage)
8. [Contributing](#contributing)
9. [License](#license)

## Project Overview

This Ecommerce project is designed to be a fully functional online store where users can view products, filter them by category or search terms, add them to a shopping cart, and simulate a payment process. The backend is built using PHP (Vanilla), and the frontend is developed with HTML, CSS, and JavaScript. The project is connected to a SQL database for product and user management.

### Course Goal

The goal of this project is to:
- Build a full-stack Ecommerce website with both frontend and backend.
- Simulate a payment process.
- Implement automation testing using Selenium with Java for testing the website.

## Frontend Features

The frontend consists of the following pages and features:

### 1. Home Page
- Displays featured products.
- Displays product categories.
- Offers a search bar to search products by name.

### 2. Product Page
- Lists products with details such as name, price, and description.
- Filters products by category.
- Option to add products to the shopping cart.

### 3. Cart Page
- Displays products added to the cart.
- Allows users to update quantities or remove items from the cart.
- Shows total price and checkout button.

### 4. Payment Page
- Simulates a simple payment process (no real payment integration).
- Displays a confirmation message once payment is "processed."

## Backend Features

The backend is built using PHP (Vanilla) and provides the following API endpoints:

### 1. Authentication
- `POST /api/auth/register.php`: Allows users to register.
- `POST /api/auth/login.php`: Allows users to log in.
- `GET /api/auth/check-auth.php`: Verifies if a user is logged in.

### 2. Products Management
- `GET /api/products/getProducts.php`: Fetches all products.
- `GET /api/products/getProduct.php`: Fetches a single product by ID.
- `POST /api/products/addProduct.php`: Adds a new product.
- `PUT /api/products/updateProduct.php`: Updates product details.
- `DELETE /api/products/deleteProduct.php`: Deletes a product.

### 3. Categories Management
- `GET /api/categories/getCategories.php`: Fetches all categories.
- `POST /api/categories/addCategory.php`: Adds a new category.
- `PUT /api/categories/updateCategory.php`: Updates category details.
- `DELETE /api/categories/deleteCategory.php`: Deletes a category.

### 4. Cart Management
- `POST /api/cart/addToCart.php`: Adds an item to the cart.
- `GET /api/cart/getCart.php`: Fetches all items in the cart.
- `POST /api/cart/removeFromCart.php`: Removes an item from the cart.
- `PUT /api/cart/updateCart.php`: Updates cart quantities.

### 5. Order and Payment
- `POST /api/orders/createOrder.php`: Creates a new order from the cart.
- `GET /api/orders/getOrders.php`: Fetches all orders for the user.
- `POST /api/payment/payment.php`: Simulates the payment process.

## Database Schema

The SQL database (`database.sql`) contains tables for the following entities:

- **Users**: Stores user information such as username, email, and password.
- **Products**: Stores product details like name, description, price, and stock.
- **Categories**: Stores product categories for filtering.
- **Orders**: Stores order information for completed purchases.
- **Cart**: Stores cart items for each user.
- **Wishlist**: Stores products users add to their wishlist.
- **Reviews**: Allows users to review products.
- **Payments**: Simulates the payment data.

Here is the SQL script for creating the database schema:

```sql
-- Create the database
CREATE DATABASE IF NOT EXISTS ecommerce_122;
USE ecommerce_122;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category_id INT NOT NULL,
    image_products VARCHAR(255),
    stock INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Create orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create order_items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Create cart table
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Create wishlist table
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Create reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Create admin user
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@example.com', '$2y$10$8K1p/a0dL1LXMIZoIqPK6.1MkzX5UZ5UZ5UZ5UZ5UZ5UZ5UZ5UZ', 'admin'); 

-- Create payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    zip_code VARCHAR(20) NOT NULL,
    card_name VARCHAR(100) NOT NULL,
    card_number VARCHAR(16) NOT NULL,
    exp_month INT NOT NULL,
    exp_year INT NOT NULL,
    cvv VARCHAR(4) NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);
