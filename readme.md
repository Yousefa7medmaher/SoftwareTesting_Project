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

The SQL database (`BackEnd\database.sql`) contains tables for the following entities:

- **Users**: Stores user information such as username, email, and password.
- **Products**: Stores product details like name, description, price, and stock.
- **Categories**: Stores product categories for filtering.
- **Orders**: Stores order information for completed purchases.
- **Cart**: Stores cart items for each user.
- **Wishlist**: Stores products users add to their wishlist.
- **Reviews**: Allows users to review products.
- **Payments**: Simulates the payment data.

## Automation Testing

The project includes automation testing implemented using Selenium with Java. The test cases cover:
- User registration and login functionality
- Product search and filtering
- Cart management (adding/removing items)
- Checkout process simulation
- Basic UI element verification

## Installation

1. Clone the repository
2. Import the database schema from `database.sql`
3. Configure the database connection in `config.php`
4. Set up a local PHP server (e.g., XAMPP, WAMP, or MAMP)
5. For testing: Set up Java environment and configure Selenium

## Usage

1. Access the application through your local server
2. Register as a new user or use existing credentials
3. Browse products, add them to cart, and proceed to checkout
4. For admin features, access the admin panel (if implemented)

## Contributing

Contributions to this project include:
- Implementation of test cases for various features
- Work on sprints and project milestones
- Integration with Zephyr tools in Jira for test case management
- Bug fixes and feature enhancements

To contribute:
1. Fork the repository
2. Create a new branch for your feature/bugfix
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.