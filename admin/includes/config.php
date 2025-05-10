<?php
// Database connection details
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); 
define('DB_NAME', 'fixit');

// Error reporting
ini_set('display_errors', 0);
error_reporting(E_ALL);

// PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Create PDO instance
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        $options
    );
} catch (PDOException $e) {
    // Log error instead of displaying it
    error_log('Database Connection Error: ' . $e->getMessage());
    die('Database connection failed. Please try again later.');
}

// Session configuration
session_start();
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // Only use in HTTPS environments

// Security function to prevent XSS
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Function to validate and sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check if user is logged in as admin
function is_admin_logged_in() {
    return isset($_SESSION['user_id']) && 
           isset($_SESSION['usertype']) && 
           $_SESSION['usertype'] === 'admin';
}

// Function to redirect with message
function redirect($location, $message = null, $type = 'error') {
    if ($message) {
        $_SESSION[$type . '_message'] = $message;
    }
    header("Location: $location");
    exit();
}
?>
