<?php
// payment.php

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize form data
    $full_name = htmlspecialchars($_POST['full_name']);
    $email = htmlspecialchars($_POST['email']);
    $address = htmlspecialchars($_POST['address']);
    $city = htmlspecialchars($_POST['city']);
    $state = htmlspecialchars($_POST['state']);
    $zip_code = htmlspecialchars($_POST['zip_code']);
    $card_name = htmlspecialchars($_POST['card_name']);
    $card_number = htmlspecialchars($_POST['card_number']);
    $exp_month = htmlspecialchars($_POST['exp_month']);
    $exp_year = htmlspecialchars($_POST['exp_year']);
    $cvv = htmlspecialchars($_POST['cvv']);
    $total_amount = htmlspecialchars($_POST['total_amount']);
    
    // Basic validation
    if (empty($full_name) || empty($email) || empty($address) || empty($city) || empty($state) || empty($zip_code) || empty($card_name) || empty($card_number) || empty($exp_month) || empty($exp_year) || empty($cvv) || empty($total_amount)) {
        die('Please fill in all required fields.');
    }

    // Basic payment processing logic (for demonstration purposes)
    // In real life, use a payment gateway like Stripe or PayPal.
    // This is where you would process the payment data securely.

    // For now, we'll just simulate a successful payment
    $payment_success = true;

    if ($payment_success) {
          echo "Payment Success. ";
    } else {
        echo "Payment failed. Please try again.";
    }
} else {
    echo "No data received.";
}
?>