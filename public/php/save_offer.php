<?php
session_start();
include_once "db.php";

if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emergency_id = $_POST['emergency_id'];
    $available_time = $_POST['available_time'];
    $cost = $_POST['cost'];
    $worker_id = $_SESSION['user_id'];

    // Check if worker_id exists in users table
    $checkUserStmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $checkUserStmt->bind_param("i", $worker_id);
    $checkUserStmt->execute();
    $checkUserStmt->store_result();

    if ($checkUserStmt->num_rows === 0) {
        // Worker ID doesn't exist in users table
        echo "Error: Worker ID not found in database.";
        exit();
    }

    // Add this after your worker_id check
        $checkEmergencyStmt = $conn->prepare("SELECT id FROM emergencies WHERE id = ?");
        $checkEmergencyStmt->bind_param("i", $emergency_id);
        $checkEmergencyStmt->execute();
        $checkEmergencyStmt->store_result();

        if ($checkEmergencyStmt->num_rows === 0) {
            echo "Error: Emergency ID not found in database.";
            exit();
        }
        $checkEmergencyStmt->close();

    $stmt = $conn->prepare("INSERT INTO offers (emergency_id, worker_id, available_time, cost) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $emergency_id, $worker_id, $available_time, $cost);

    try {
    if ($stmt->execute()) {
        header("Location: ../worker_emergencies.php");
        exit();
    }
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1452) { // Foreign key constraint error code
        echo "Error: The emergency you're trying to offer help for doesn't exist.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}

    $stmt->close();
    $conn->close();
}
?>
