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
    header('Location: ../all_tips.php');
    exit();
}

// Check if form was submitted with POST method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate tip_id
    if (!isset($_POST['tip_id']) || empty($_POST['tip_id'])) {
        $_SESSION['error_message'] = "Invalid tip ID provided.";
        header('Location: ../all_tips.php');
        exit();
    }
    
    $tip_id = filter_var($_POST['tip_id'], FILTER_VALIDATE_INT);
    
    if ($tip_id === false || $tip_id <= 0) {
        $_SESSION['error_message'] = "Invalid tip ID format.";
        header('Location: ../all_tips.php');
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    try {
        // First, get the tip details to check if it exists and get image info
        $stmt = $pdo->prepare("SELECT tip_id, title, images, user_id, type FROM tips WHERE tip_id = :tip_id");
        $stmt->bindParam(':tip_id', $tip_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $tip = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tip) {
            $_SESSION['error_message'] = "Tip not found.";
            header('Location: ../all_tips.php');
            exit();
        }
        
        // Optional: Check if user has permission to delete this tip
        // Uncomment the following lines if you want users to only delete their own tips
        /*
        if ($tip['user_id'] != $user_id) {
            $_SESSION['error_message'] = "You don't have permission to delete this tip.";
            header('Location: ../all_tips.php');
            exit();
        }
        */
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Delete the tip from database
        $stmt = $pdo->prepare("DELETE FROM tips WHERE tip_id = :tip_id");
        $stmt->bindParam(':tip_id', $tip_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            // If tip had an image, try to delete the image file
            if (!empty($tip['images'])) {
                $image_path = '../../public/images/tips/' . $tip['images'];
                if (file_exists($image_path)) {
                    if (!unlink($image_path)) {
                        // Log warning but don't fail the deletion
                        error_log("Warning: Could not delete image file: {$image_path}");
                    }
                }
            }
            
            // Commit transaction
            $pdo->commit();
            
            $_SESSION['success_message'] = "Tip '{$tip['title']}' deleted successfully!";
            
            // Log successful deletion
            error_log("Tip deleted successfully - ID: {$tip_id}, Title: {$tip['title']}, User: {$user_id}");
            
        } else {
            $pdo->rollBack();
            $_SESSION['error_message'] = "Failed to delete tip. Please try again.";
            error_log("Failed to delete tip - ID: {$tip_id}, User: {$user_id}");
        }
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        // Log the error
        error_log('Database Error in delete_tip.php: ' . $e->getMessage());
        $_SESSION['error_message'] = "Database error occurred while deleting tip.";
        
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        // Log the error
        error_log('General Error in delete_tip.php: ' . $e->getMessage());
        $_SESSION['error_message'] = "An unexpected error occurred while deleting tip.";
    }
    
} else {
    $_SESSION['error_message'] = "Invalid request method.";
}

// Redirect back to all tips page
header('Location: ../all_tips.php');
exit();
?>