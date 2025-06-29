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

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Handle fetching tip for editing
    if (isset($_POST['tip_id']) && !isset($_POST['update_tip'])) {
        $tip_id = filter_var($_POST['tip_id'], FILTER_VALIDATE_INT);
        
        if ($tip_id === false || $tip_id <= 0) {
            $_SESSION['error_message'] = "Invalid tip ID.";
            header('Location: ../all_tips.php');
            exit();
        }
        
        try {
            // Fetch the existing tip details
            $stmt = $pdo->prepare("SELECT * FROM tips WHERE tip_id = :tip_id AND type = 'daily tips'");
            $stmt->bindParam(':tip_id', $tip_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $tip = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tip) {
                $_SESSION['error_message'] = "Daily tip not found.";
                header('Location: ../all_tips.php');
                exit();
            }
            
            // Optional: Check if user owns this tip
            /*
            if ($tip['user_id'] != $user_id) {
                $_SESSION['error_message'] = "You don't have permission to edit this tip.";
                header('Location: ../all_tips.php');
                exit();
            }
            */
            
        } catch (PDOException $e) {
            error_log('Database Error in edit_dailyTip.php (fetch): ' . $e->getMessage());
            $_SESSION['error_message'] = "Database error occurred.";
            header('Location: ../all_tips.php');
            exit();
        }
        
    } 
    // Handle updating the tip
    elseif (isset($_POST['update_tip'])) {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['error_message'] = "Invalid form submission.";
            header('Location: ../all_tips.php');
            exit();
        }
        
        $tip_id = filter_var($_POST['tip_id'], FILTER_VALIDATE_INT);
        
        if ($tip_id === false || $tip_id <= 0) {
            $_SESSION['error_message'] = "Invalid tip ID.";
            header('Location: ../all_tips.php');
            exit();
        }
        
        // Sanitize input
        $title = sanitize_input($_POST['title'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        
        // Validate input
        if (empty($title) || empty($description)) {
            $_SESSION['error_message'] = "Title and description are required.";
            header('Location: ../all_tips.php');
            exit();
        }
        
        // Validate lengths
        if (strlen($title) < 3 || strlen($title) > 100) {
            $_SESSION['error_message'] = "Title must be between 3 and 100 characters.";
            header('Location: ../all_tips.php');
            exit();
        }
        
        if (strlen($description) < 10 || strlen($description) > 500) {
            $_SESSION['error_message'] = "Description must be between 10 and 500 characters.";
            header('Location: ../all_tips.php');
            exit();
        }
        
        try {
            // Check if tip exists and get current data
            $stmt = $pdo->prepare("SELECT * FROM tips WHERE tip_id = :tip_id AND type = 'daily tips'");
            $stmt->bindParam(':tip_id', $tip_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $current_tip = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$current_tip) {
                $_SESSION['error_message'] = "Daily tip not found.";
                header('Location: ../all_tips.php');
                exit();
            }
            
            // Check if title already exists (excluding current tip)
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tips WHERE title = :title AND type = 'daily tips' AND tip_id != :tip_id");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':tip_id', $tip_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                $_SESSION['error_message'] = "A daily tip with this title already exists.";
                header('Location: ../all_tips.php');
                exit();
            }
            
            // Handle image upload
            $new_image = null;
            $old_image = $current_tip['images'];
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $allowed_extensions = ['jpeg', 'jpg', 'png', 'gif'];
                $max_size = 2 * 1024 * 1024; // 2MB
                
                $img_name = $_FILES['image']['name'];
                $img_type = $_FILES['image']['type'];
                $img_size = $_FILES['image']['size'];
                $tmp_name = $_FILES['image']['tmp_name'];
                
                $img_explode = explode('.', $img_name);
                $img_ext = strtolower(end($img_explode));
                
                // Validate file
                if (!in_array($img_type, $allowed_types) || !in_array($img_ext, $allowed_extensions)) {
                    $_SESSION['error_message'] = "Only JPG, PNG, and GIF images are allowed.";
                    header('Location: ../all_tips.php');
                    exit();
                }
                
                if ($img_size > $max_size) {
                    $_SESSION['error_message'] = "Image size should be less than 2MB.";
                    header('Location: ../all_tips.php');
                    exit();
                }
                
                // Generate unique filename
                $new_image = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $img_name);
                $upload_path = '../../public/images/tips/' . $new_image;
                
                // Create directory if it doesn't exist
                $dir = '../../public/images/tips/';
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                
                // Move uploaded file
                if (!move_uploaded_file($tmp_name, $upload_path)) {
                    $_SESSION['error_message'] = "Failed to upload image.";
                    header('Location: ../all_tips.php');
                    exit();
                }
            }
            
            // Start transaction
            $pdo->beginTransaction();
            
            // Update the tip
            if ($new_image) {
                $stmt = $pdo->prepare("UPDATE tips SET title = :title, description = :description, images = :images WHERE tip_id = :tip_id");
                $stmt->bindParam(':images', $new_image);
            } else {
                $stmt = $pdo->prepare("UPDATE tips SET title = :title, description = :description WHERE tip_id = :tip_id");
            }
            
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':tip_id', $tip_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // If new image uploaded, delete old image
                if ($new_image && !empty($old_image)) {
                    $old_image_path = '../../public/images/tips/' . $old_image;
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                
                $pdo->commit();
                $_SESSION['success_message'] = "Daily tip updated successfully!";
                
                // Log successful update
                error_log("Daily tip updated - ID: {$tip_id}, Title: {$title}, User: {$user_id}");
                
            } else {
                $pdo->rollBack();
                $_SESSION['error_message'] = "Failed to update daily tip.";
            }
            
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            // Clean up uploaded file if database update failed
            if ($new_image && file_exists($upload_path)) {
                unlink($upload_path);
            }
            
            error_log('Database Error in edit_dailyTip.php (update): ' . $e->getMessage());
            $_SESSION['error_message'] = "Database error occurred while updating tip.";
        }
        
        header('Location: ../all_tips.php');
        exit();
        
    } else {
        $_SESSION['error_message'] = "Invalid request.";
        header('Location: ../all_tips.php');
        exit();
    }
    
} else {
    $_SESSION['error_message'] = "Invalid request method.";
    header('Location: ../all_tips.php');
    exit();
}
?>