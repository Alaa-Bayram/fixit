<?php
require_once 'includes/config.php';

// Update user status before logging out
if (isset($_SESSION['unique_id'])) {
    try {
        $status = "Offline now";
        $stmt = $pdo->prepare("UPDATE users SET status = :status WHERE unique_id = :unique_id");
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':unique_id', $_SESSION['unique_id'], PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        error_log('Logout Error: ' . $e->getMessage());
    }
}

// Clear session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>