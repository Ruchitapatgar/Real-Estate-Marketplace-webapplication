<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'real_estate_db');

// Create connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to check user role
function isSeller() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'seller';
}

function isBuyer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'buyer';
}

// Helper function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Helper function to sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Currency Configuration
define('CURRENCY_SYMBOL', '₹');  // Change to $, €, £, ¥ as needed
define('CURRENCY_CODE', 'INR');   // Change to USD, EUR, GBP, JPY as needed

// Helper function to format price with currency
function formatPrice($price, $decimals = 2) {
    return CURRENCY_SYMBOL . number_format($price, $decimals);
}
?>