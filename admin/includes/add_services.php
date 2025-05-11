<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Check if user is logged in as admin
if (!is_admin_logged_in()) {
    header('Location: ../login.php');
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['error_message'] = "Invalid form submission.";
    header('Location: ../services.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input
    $title = sanitize_input($_POST['title'] ?? '');
    $description = sanitize_input($_POST['desc'] ?? '');
    
    // Validate input
    if (empty($title) || empty($description)) {
        $_SESSION['error_message'] = "All fields are required.";
        header('Location: ../services.php');
        exit();
    }
    
    // Check if an image was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        // Validate file type and size
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $_SESSION['error_message'] = "Only JPG, PNG and GIF images are allowed.";
            header('Location: ../services.php');
            exit();
        }
        
        if ($_FILES['image']['size'] > $max_size) {
            $_SESSION['error_message'] = "Image size should be less than 2MB.";
            header('Location: ../services.php');
            exit();
        }
        
        // Generate a unique filename
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('service_') . '.' . $file_extension;
        $upload_path = '../../public/images/' . $filename;
        
        // Create directory if it doesn't exist
        $dir = '../../public/images/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Move the uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            try {
                // Insert data into the database
                $stmt = $pdo->prepare("INSERT INTO services (title, description, images) VALUES (:title, :description, :images)");
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':images', $filename);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Service added successfully!";
                } else {
                    $_SESSION['error_message'] = "Failed to add service. Please try again.";
                }
            } catch (PDOException $e) {
                error_log('Database Error: ' . $e->getMessage());
                $_SESSION['error_message'] = "Database error occurred. Please try again.";
            }
        } else {
            $_SESSION['error_message'] = "Failed to upload image. Please try again.";
        }
    } else {
        $_SESSION['error_message'] = "Please select an image.";
    }
    
    // Redirect back to services page
    header('Location: ../services.php');
    exit();
}

// If not POST request, redirect
header('Location: ../services.php');
exit();
?>