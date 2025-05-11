<?php
// Start or resume a session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is authenticated
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

// Redirect to login page if not authenticated
function requireAuth() {
    if (!isAuthenticated()) {
        header("Location: /frontend/Login.html");
        exit();
    }
}
?>