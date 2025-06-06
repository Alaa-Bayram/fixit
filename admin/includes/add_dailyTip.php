<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Check if user is logged in
if (!is_admin_logged_in()) {
    $_SESSION['error_message'] = "Please log in to access this feature";
    header('Location: ../login.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "CSRF token missing";
        header('Location: ../tips.php');
        exit();
    }
    
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Invalid form submission";
        header('Location: ../tips.php');
        exit();
    }

    // Sanitize input
    $title = sanitize_input($_POST['title'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $type = 'daily tips'; // Fixed type as per your form
    $date = date('Y-m-d H:i:s');
    $user_id = $_SESSION['user_id'];

    // Validate input
    if (empty($title) || empty($description)) {
        $_SESSION['error_message'] = "Title and description are required";
        header('Location: ../tips.php');
        exit();
    }

    // Validate lengths
    if (strlen($title) < 3 || strlen($title) > 100) {
        $_SESSION['error_message'] = "Title must be between 3 and 100 characters";
        header('Location: ../tips.php');
        exit();
    }

    if (strlen($description) < 10 || strlen($description) > 500) {
        $_SESSION['error_message'] = "Description must be between 10 and 500 characters";
        header('Location: ../tips.php');
        exit();
    }

    // Check if image was uploaded
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $upload_error = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
        switch ($upload_error) {
            case UPLOAD_ERR_NO_FILE:
                $_SESSION['error_message'] = "Please select an image file";
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $_SESSION['error_message'] = "Image file is too large (max 2MB)";
                break;
            case UPLOAD_ERR_PARTIAL:
                $_SESSION['error_message'] = "Image upload was interrupted";
                break;
            default:
                $_SESSION['error_message'] = "Image upload failed with error code: " . $upload_error;
        }
        header('Location: ../tips.php');
        exit();
    }

    try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user_exists = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user_exists['count'] == 0) {
        $_SESSION['error_message'] = "Invalid user account. Please log out and log in again.";
        header('Location: ../tips.php');
        exit();
    }

    // Check if title already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tips WHERE title = :title AND type = :type");
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':type', $type);
    $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            $_SESSION['error_message'] = "A daily tip with this title already exists";
            header('Location: ../tips.php');
            exit();
        }

        // Handle image upload
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
            $_SESSION['error_message'] = "Only JPG, PNG, and GIF images are allowed";
            header('Location: ../tips.php');
            exit();
        }
        
        if ($img_size > $max_size) {
            $_SESSION['error_message'] = "Image size should be less than 2MB";
            header('Location: ../tips.php');
            exit();
        }
        
        // Generate unique filename
        $new_image = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $img_name);
        $upload_path = '../../public/images/tips/' . $new_image;
        
        // Create directory if it doesn't exist
        $dir = '../../public/images/tips/';
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                $_SESSION['error_message'] = "Failed to create upload directory";
                header('Location: ../tips.php');
                exit();
            }
        }
        
        // Move uploaded file
        if (!move_uploaded_file($tmp_name, $upload_path)) {
            $_SESSION['error_message'] = "Failed to upload image";
            header('Location: ../tips.php');
            exit();
        }


error_log("Attempting to insert tip with user_id: " . $user_id);
        // Start transaction
        $pdo->beginTransaction();
        
        // Insert the new tip
        $stmt = $pdo->prepare("INSERT INTO tips (title, description, images, date, type, user_id) 
                              VALUES (:title, :description, :images, :date, :type, :user_id)");
        
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':images', $new_image);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($stmt->execute()) {
            $pdo->commit();
            $_SESSION['success_message'] = "Daily tip added successfully!";
            
            // Log successful addition
            error_log("Daily tip added - Title: {$title}, User: {$user_id}");
            
        } else {
            $pdo->rollBack();
            // Clean up uploaded file if database insert failed
            if (file_exists($upload_path)) {
                unlink($upload_path);
            }
            $_SESSION['error_message'] = "Failed to add daily tip";
        }
        
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        // Clean up uploaded file if database error occurred
        if (isset($upload_path) && file_exists($upload_path)) {
            unlink($upload_path);
        }
        
        error_log('Database Error in add_dailyTip.php: ' . $e->getMessage());
        $_SESSION['error_message'] = "Database error occurred while adding tip: " . $e->getMessage();
    }
    
    header('Location: ../tips.php');
    exit();
    
} else {
    $_SESSION['error_message'] = "Invalid request method";
    header('Location: ../tips.php');
    exit();
}