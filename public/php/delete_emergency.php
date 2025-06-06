<?php
session_start();
include_once "db.php";

// Check if user is authenticated
if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    header("Location: login.html"); // Redirect to login page if not authenticated
    exit();
}

// Check if emergency_id is set
if (isset($_POST['emergency_id'])) {
    $emergency_id = $_POST['emergency_id'];

    // Prepare SQL query to delete the emergency
    $sql = "DELETE FROM emergencies WHERE id = ? AND client_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $emergency_id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        // Redirect back to the client_emergencies.php page after deletion
        header("Location: ../client_emergencies.php");
    } else {
        echo "Error: Could not delete emergency.";
    }

    $stmt->close();
}

$conn->close();
?>
