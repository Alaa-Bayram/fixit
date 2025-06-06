<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "User not logged in!";
    header('Location: ../login.php');
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['error_message'] = "Invalid form submission.";
    header('Location: ../tips.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Sanitize input
    $title = sanitize_input($_POST['title'] ?? '');
    $description = sanitize_input($_POST['desc'] ?? '');
    $tip1 = sanitize_input($_POST['tip1'] ?? '');
    $tip2 = sanitize_input($_POST['tip2'] ?? '');
    $type = sanitize_input($_POST['type'] ?? 'seasonal tips');
    $date = date('Y-m-d H:i:s');
    
    // Validate required fields
    if (empty($title) || empty($description) || empty($tip1) || empty($tip2)) {
        $_SESSION['error_message'] = "All fields are required.";
        header('Location: ../tips.php');
        exit();
    }
    
    // Validate field lengths
    if (strlen($title) < 3) {
        $_SESSION['error_message'] = "Title must be at least 3 characters long.";
        header('Location: ../tips.php');
        exit();
    }
    
    if (strlen($description) < 10) {
        $_SESSION['error_message'] = "Description must be at least 10 characters long.";
        header('Location: ../tips.php');
        exit();
    }
    
    if (strlen($tip1) < 5) {
        $_SESSION['error_message'] = "First tip must be at least 5 characters long.";
        header('Location: ../tips.php');
        exit();
    }
    
    if (strlen($tip2) < 5) {
        $_SESSION['error_message'] = "Second tip must be at least 5 characters long.";
        header('Location: ../tips.php');
        exit();
    }
    
    // Validate maximum lengths
    if (strlen($title) > 100) {
        $_SESSION['error_message'] = "Title must be less than 100 characters.";
        header('Location: ../tips.php');
        exit();
    }
    
    if (strlen($description) > 500) {
        $_SESSION['error_message'] = "Description must be less than 500 characters.";
        header('Location: ../tips.php');
        exit();
    }
    
    if (strlen($tip1) > 255) {
        $_SESSION['error_message'] = "First tip must be less than 255 characters.";
        header('Location: ../tips.php');
        exit();
    }
    
    if (strlen($tip2) > 255) {
        $_SESSION['error_message'] = "Second tip must be less than 255 characters.";
        header('Location: ../tips.php');
        exit();
    }
    
    try {
        // Check if the seasonal tip already exists
        $stmt = $pdo->prepare("SELECT tip_id FROM tips WHERE title = :title AND type = :type");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':type', $type);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['error_message'] = "Seasonal tip with this title already exists!";
            header("Location: ../tips.php");
            exit();
        }
        
        // Insert seasonal tip data into the database
        $stmt = $pdo->prepare("INSERT INTO tips (title, description, f_tip, s_tip, date, type, user_id) VALUES (:title, :description, :f_tip, :s_tip, :date, :type, :user_id)");
        
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':f_tip', $tip1);
        $stmt->bindParam(':s_tip', $tip2);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Seasonal tip added successfully!";
            
            // Log the successful addition
            error_log("Seasonal tip added successfully - Title: {$title}, User ID: {$user_id}");
        } else {
            $_SESSION['error_message'] = "Failed to add seasonal tip. Please try again.";
            error_log("Failed to insert seasonal tip - Title: {$title}, User ID: {$user_id}");
        }
        
    } catch (PDOException $e) {
        // Log the actual error for debugging
        error_log('Database Error in add_seasonalTip.php: ' . $e->getMessage());
        $_SESSION['error_message'] = "Database error occurred. Please try again.";
    } catch (Exception $e) {
        // Log any other errors
        error_log('General Error in add_seasonalTip.php: ' . $e->getMessage());
        $_SESSION['error_message'] = "An unexpected error occurred. Please try again.";
    }
    
    // Redirect back to tips page
    header('Location: ../tips.php');
    exit();
}

// If not POST request, redirect
header('Location: ../tips.php');
exit();
?>