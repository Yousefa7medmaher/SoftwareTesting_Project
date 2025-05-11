<?php
 
// secure_payment.php
// A more secure approach to payment processing

// Start session for maintaining user state
session_start();

// Include configuration and dependencies
require_once 'config.php';
require_once 'vendor/autoload.php'; // Using Composer for dependencies

// Load environment variables for sensitive credentials
$stripe_secret_key = getenv('STRIPE_SECRET_KEY');

// Initialize payment gateway client (example using Stripe)
\Stripe\Stripe::setApiKey($stripe_secret_key);

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token to prevent cross-site request forgery
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Invalid security token");
        }
        
        // Sanitize and validate user input
        $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
        $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
        $state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING);
        $zip_code = filter_input(INPUT_POST, 'zip_code', FILTER_SANITIZE_STRING);
        $amount = filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_NUMBER_FLOAT);
        
        // Get token from secure form element (Stripe Elements or similar)
        $token = $_POST['payment_token'];
        
        if (!$token) {
            throw new Exception("Payment information is required");
        }
        
        // Input validation
        if (!$full_name || !$email || !$address || !$city || !$state || !$zip_code) {
            throw new Exception("All fields are required");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }
        
        // Process payment through Stripe
        $charge = \Stripe\Charge::create([
            'amount' => $amount * 100, // Amount in cents
            'currency' => 'usd',
            'description' => 'Order payment',
            'source' => $token,
            'receipt_email' => $email,
            'metadata' => [
                'customer_name' => $full_name,
                'order_id' => uniqid('order_')
            ]
        ]);
        
        // If payment is successful, store order information (NOT card details)
        if ($charge->status === 'succeeded') {
            $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Save order without storing any payment card details
            $stmt = $pdo->prepare("INSERT INTO orders (order_id, full_name, email, address, city, state, zip_code, amount, payment_id) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $order_id = uniqid('order_');
            $stmt->execute([
                $order_id,
                $full_name,
                $email,
                $address,
                $city,
                $state,
                $zip_code,
                $amount,
                $charge->id // Reference to payment in Stripe, not actual card details
            ]);
            
            // Create response for client
            $response = [
                'status' => 'success',
                'message' => 'Payment successful! Your order has been placed.',
                'order_id' => $order_id
            ];
            
            // Set session flag for success page
            $_SESSION['payment_success'] = true;
            $_SESSION['order_id'] = $order_id;
            
            // Redirect to success page
            header('Location: order-confirmation.php');
            exit;
        } else {
            throw new Exception("Payment processing failed");
        }
    } catch (Exception $e) {
        // Log error (to a secure log, not displayed to user)
        error_log('Payment Error: ' . $e->getMessage());
        
        // Return error message
        $response = [
            'status' => 'error',
            'message' => 'Payment failed. Please try again.'
        ];
        
        // Set session flag for error display
        $_SESSION['payment_error'] = $e->getMessage();
        
        // Redirect back to checkout with error
        header('Location: checkout.php?error=1');
        exit;
    }
} else {
    // Not a POST request
    header('Location: checkout.php');
    exit;
}
?>