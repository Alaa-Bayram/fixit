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
            $stmt = $pdo->prepare("SELECT * FROM tips WHERE tip_id = :tip_id AND type = 'seasonal tips'");
            $stmt->bindParam(':tip_id', $tip_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $tip = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tip) {
                $_SESSION['error_message'] = "Seasonal tip not found.";
                header('Location: ../all_tips.php');
                exit();
            }
            
            // Optional: Check if user owns this tip (uncomment if needed)
            /*
            if ($tip['user_id'] != $user_id) {
                $_SESSION['error_message'] = "You don't have permission to edit this tip.";
                header('Location: ../all_tips.php');
                exit();
            }
            */
            
        } catch (PDOException $e) {
            error_log('Database Error in edit_seasonalTip.php (fetch): ' . $e->getMessage());
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
        $tip1 = sanitize_input($_POST['tip1'] ?? '');
        $tip2 = sanitize_input($_POST['tip2'] ?? '');
        
        // Validate required fields
        if (empty($title) || empty($description) || empty($tip1) || empty($tip2)) {
            $_SESSION['error_message'] = "All fields are required.";
            header('Location: ../all_tips.php');
            exit();
        }
        
        // Validate field lengths
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
        
        if (strlen($tip1) < 5 || strlen($tip1) > 255) {
            $_SESSION['error_message'] = "First tip must be between 5 and 255 characters.";
            header('Location: ../all_tips.php');
            exit();
        }
        
        if (strlen($tip2) < 5 || strlen($tip2) > 255) {
            $_SESSION['error_message'] = "Second tip must be between 5 and 255 characters.";
            header('Location: ../all_tips.php');
            exit();
        }
        
        try {
            // Check if tip exists and get current data
            $stmt = $pdo->prepare("SELECT * FROM tips WHERE tip_id = :tip_id AND type = 'seasonal tips'");
            $stmt->bindParam(':tip_id', $tip_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $current_tip = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$current_tip) {
                $_SESSION['error_message'] = "Seasonal tip not found.";
                header('Location: ../all_tips.php');
                exit();
            }
            
            // Check if title already exists (excluding current tip)
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tips WHERE title = :title AND type = 'seasonal tips' AND tip_id != :tip_id");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':tip_id', $tip_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                $_SESSION['error_message'] = "A seasonal tip with this title already exists.";
                header('Location: ../all_tips.php');
                exit();
            }
            
            // Handle optional image upload
            $new_image = null;
            $old_image = $current_tip['image'] ?? null;
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $allowed_extensions = ['jpeg', 'jpg', 'png', 'gif'];
                $max_size = 2 * 1024 * 1024; // 2MB
                
                $img_name = $_FILES['image']['name'];
                $img_type = $_FILES['image']['type'];
                $img_size = $_FILES['image']['size'];
                $tmp_name = $_FILES['image']['tmp_name'];
                
                // Validate file name
                if (empty($img_name)) {
                    $_SESSION['error_message'] = "Invalid image file.";
                    header('Location: ../all_tips.php');
                    exit();
                }
                
                $img_explode = explode('.', $img_name);
                $img_ext = strtolower(end($img_explode));
                
                // Validate file type, extension and size
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
                    if (!mkdir($dir, 0755, true)) {
                        $_SESSION['error_message'] = "Failed to create upload directory.";
                        header('Location: ../all_tips.php');
                        exit();
                    }
                }
                
                // Move uploaded file
                if (!move_uploaded_file($tmp_name, $upload_path)) {
                    $_SESSION['error_message'] = "Failed to upload image.";
                    header('Location: ../all_tips.php');
                    exit();
                }
            } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
                // Handle other upload errors
                $upload_error = $_FILES['image']['error'];
                switch ($upload_error) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $_SESSION['error_message'] = "Image file is too large.";
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $_SESSION['error_message'] = "Image upload was interrupted. Please try again.";
                        break;
                    default:
                        $_SESSION['error_message'] = "Image upload failed. Please try again.";
                }
                header('Location: ../all_tips.php');
                exit();
            }
            
            // Start transaction
            $pdo->beginTransaction();
            
            // Update the tip
            if ($new_image) {
                $stmt = $pdo->prepare("UPDATE tips SET title = :title, description = :description, f_tip = :f_tip, s_tip = :s_tip, image = :image WHERE tip_id = :tip_id");
                $stmt->bindParam(':image', $new_image);
            } else {
                $stmt = $pdo->prepare("UPDATE tips SET title = :title, description = :description, f_tip = :f_tip, s_tip = :s_tip WHERE tip_id = :tip_id");
            }
            
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':f_tip', $tip1);
            $stmt->bindParam(':s_tip', $tip2);
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
                $_SESSION['success_message'] = "Seasonal tip updated successfully!";
                
                // Log successful update
                error_log("Seasonal tip updated - ID: {$tip_id}, Title: {$title}, User: {$user_id}");
                
            } else {
                $pdo->rollBack();
                $_SESSION['error_message'] = "Failed to update seasonal tip.";
                error_log("Failed to update seasonal tip - ID: {$tip_id}, User: {$user_id}");
            }
            
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            // Clean up uploaded file if database update failed
            if ($new_image && isset($upload_path) && file_exists($upload_path)) {
                unlink($upload_path);
            }
            
            error_log('Database Error in edit_seasonalTip.php (update): ' . $e->getMessage());
            $_SESSION['error_message'] = "Database error occurred while updating tip.";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            // Clean up uploaded file if update failed
            if ($new_image && isset($upload_path) && file_exists($upload_path)) {
                unlink($upload_path);
            }
            
            error_log('General Error in edit_seasonalTip.php (update): ' . $e->getMessage());
            $_SESSION['error_message'] = "An unexpected error occurred while updating tip.";
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