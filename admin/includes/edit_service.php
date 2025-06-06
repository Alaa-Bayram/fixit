<?php 
include_once "config.php";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token'])) {
        set_flash_message('error', $trans['invalid_form_submission']);
        header('Location: edit_service.php?id=' . $service_id . '&lang=' . $lang);
        exit();
    }

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        set_flash_message('error', $trans['invalid_form_submission']);
        header('Location: edit_service.php?id=' . $service_id . '&lang=' . $lang);
        exit();
    }
    
    // Sanitize input for all languages - DON'T HTML encode here
    $title = trim(stripslashes($_POST['title'] ?? ''));
    $description = trim(stripslashes($_POST['desc'] ?? ''));
    $title_fr = trim(stripslashes($_POST['title_fr'] ?? ''));
    $description_fr = trim(stripslashes($_POST['desc_fr'] ?? ''));
    $title_ar = trim(stripslashes($_POST['title_ar'] ?? ''));
    $description_ar = trim(stripslashes($_POST['desc_ar'] ?? ''));
    
    // Validate input - at least English fields are required
    if (empty($title) || empty($description)) {
        set_flash_message('error', $trans['all_fields_required']);
        header('Location: edit_service.php?id=' . $service_id . '&lang=' . $lang);
        exit();
    }
    
    try {
        // Check if a new image was uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            // Validate file type and size
            if (!in_array($_FILES['image']['type'], $allowed_types)) {
                set_flash_message('error', $trans['only_jpg_png_gif_allowed']);
                header('Location: edit_service.php?id=' . $service_id . '&lang=' . $lang);
                exit();
            }
            
            if ($_FILES['image']['size'] > $max_size) {
                set_flash_message('error', $trans['image_size_less_than_2mb']);
                header('Location: edit_service.php?id=' . $service_id . '&lang=' . $lang);
                exit();
            }
            
            // Generate a unique filename
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('service_') . '.' . $file_extension;
            $upload_path = '../public/images/' . $filename;
            
            // Create directory if it doesn't exist
            $dir = '../public/images';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Move the uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if it exists
                if (!empty($service['images'])) {
                    $old_image_path = '../public/images/' . $service['images'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                
                // Update database with new image and all language fields
                $stmt = $pdo->prepare("UPDATE services SET title = :title, description = :description, title_fr = :title_fr, description_fr = :description_fr, title_ar = :title_ar, description_ar = :description_ar, images = :images WHERE service_id = :service_id");
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':title_fr', $title_fr);
                $stmt->bindParam(':description_fr', $description_fr);
                $stmt->bindParam(':title_ar', $title_ar);
                $stmt->bindParam(':description_ar', $description_ar);
                $stmt->bindParam(':images', $filename);
                $stmt->bindParam(':service_id', $service_id);
            } else {
                set_flash_message('error', $trans['failed_upload_image']);
                header('Location: edit_service.php?id=' . $service_id . '&lang=' . $lang);
                exit();
            }
        } else {
            // Update only title, description and language fields
            $stmt = $pdo->prepare("UPDATE services SET title = :title, description = :description, title_fr = :title_fr, description_fr = :description_fr, title_ar = :title_ar, description_ar = :description_ar WHERE service_id = :service_id");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':title_fr', $title_fr);
            $stmt->bindParam(':description_fr', $description_fr);
            $stmt->bindParam(':title_ar', $title_ar);
            $stmt->bindParam(':description_ar', $description_ar);
            $stmt->bindParam(':service_id', $service_id);
        }
        
        if ($stmt->execute()) {
            set_flash_message('success', $trans['service_updated_successfully']);
            header('Location: all_services.php?lang=' . $lang);
            exit();
        } else {
            set_flash_message('error', $trans['failed_update_service']);
        }
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        set_flash_message('error', $trans['database_error']);
    }
    
    // Redirect back to edit page
    header('Location: edit_service.php?id=' . $service_id . '&lang=' . $lang);
    exit();
}
?>