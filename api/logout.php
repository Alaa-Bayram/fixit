<?php
session_start();

// Check if the session exists
if (isset($_SESSION['authenticated'])) {
    include('../db.php'); // Include your database connection
    
    // Update user status to 'Offline Now'
    $updateSql = "UPDATE users SET status = 'Offline Now' WHERE unique_id = :unique_id";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bindParam(':unique_id', $_SESSION['unique_id']);
    $updateStmt->execute();

    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Send a JSON response
    echo json_encode(array("success" => true, "message" => "Logged out successfully."));
} else {
    echo json_encode(array("success" => false, "message" => "No session found."));
}
?>
