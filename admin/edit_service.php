<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', 'Please log in to access the dashboard');
}

// Check if service ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('all_services.php', 'Invalid service ID');
}

$service_id = sanitize_input($_GET['id']);
$service = null;

// Fetch service details
try {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE service_id = :service_id");
    $stmt->bindParam(':service_id', $service_id);
    $stmt->execute();
    $service = $stmt->fetch();
    
    if (!$service) {
        redirect('all_services.php', 'Service not found');
    }
} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    redirect('all_services.php', 'Failed to fetch service details');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        set_flash_message('error', 'Invalid form submission.');
        header('Location: edit_service.php?id=' . $service_id);
        exit();
    }
    
    // Sanitize input
    $title = sanitize_input($_POST['title'] ?? '');
    $description = sanitize_input($_POST['desc'] ?? '');
    
    // Validate input
    if (empty($title) || empty($description)) {
        set_flash_message('error', 'All fields are required.');
        header('Location: edit_service.php?id=' . $service_id);
        exit();
    }
    
    try {
        // Check if a new image was uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            // Validate file type and size
            if (!in_array($_FILES['image']['type'], $allowed_types)) {
                set_flash_message('error', 'Only JPG, PNG and GIF images are allowed.');
                header('Location: edit_service.php?id=' . $service_id);
                exit();
            }
            
            if ($_FILES['image']['size'] > $max_size) {
                set_flash_message('error', 'Image size should be less than 2MB.');
                header('Location: edit_service.php?id=' . $service_id);
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
                $old_image_path = '../public/images' . $service['images'];
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
                
                // Update database with new image
                $stmt = $pdo->prepare("UPDATE services SET title = :title, description = :description, images = :images WHERE service_id = :service_id");
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':images', $filename);
                $stmt->bindParam(':service_id', $service_id);
            } else {
                set_flash_message('error', 'Failed to upload image. Please try again.');
                header('Location: edit_service.php?id=' . $service_id);
                exit();
            }
        } else {
            // Update only title and description
            $stmt = $pdo->prepare("UPDATE services SET title = :title, description = :description WHERE service_id = :service_id");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':service_id', $service_id);
        }
        
        if ($stmt->execute()) {
            set_flash_message('success', 'Service updated successfully!');
            header('Location: all_services.php');
            exit();
        } else {
            set_flash_message('error', 'Failed to update service. Please try again.');
        }
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        set_flash_message('error', 'Database error occurred. Please try again.');
    }
    
    // Redirect back to edit page
    header('Location: edit_service.php?id=' . $service_id);
    exit();
}

// Include header
include_once 'includes/header.php';
?>

<div class="home-content">
    <div class="sales-boxes">
        <div class="recent-sales box">
            <div class="title">Edit Service</div>
            
            <?php if (has_flash_message('error')): ?>
                <div class="alert alert-danger"><?php echo get_flash_message('error'); ?></div>
            <?php endif; ?>
            
            <div class="sales-details">
                <form method="POST" action="edit_service.php?id=<?php echo h($service_id); ?>" enctype="multipart/form-data" class="service-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    <div class="field">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" value="<?php echo h($service['title']); ?>" required>
                    </div>
                    <div class="field">
                        <label for="desc">Description</label>
                        <input type="text" id="desc" name="desc" value="<?php echo h($service['description']); ?>" required>
                    </div>
                    <div class="field">
                        <label for="image">Image</label>
                        <div class="current-image">
                            <img src="../public/images/<?php echo h($service['images']); ?>" alt="Current Image" style="max-height: 100px; margin-bottom: 10px;">
                            <p>Current image: <?php echo h($service['images']); ?></p>
                        </div>
                        <input type="file" id="image" name="image" accept="image/x-png,image/gif,image/jpeg,image/jpg" class="upload">
                        <small>Leave empty to keep current image</small>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="btn btn-update">Update Service</button>
                        <a href="all_services.php" class="btn btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .service-form {
        margin-top: 30px;
        display: flex;
        flex-direction: column;
    }

    .service-form .field {
        margin-bottom: 15px;
    }

    .service-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #333;
    }

    .service-form input[type="text"],
    .service-form input[type="file"] {
        width: 750px;
        padding: 10px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
    }

    .service-form .upload {
        padding: 3px;
    }

    .current-image {
        margin-bottom: 10px;
    }

    .button-group {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 500;
        border: none;
        transition: all 0.3s ease;
    }

    .btn-update {
        background-color: #ff6c40e4;
        color: white;
    }

    .btn-cancel {
        background-color: #95a5a6;
        color: white;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-update:hover, .btn-cancel:hover {
        opacity: 0.9;
    }

    .alert {
        padding: 10px 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }

    .alert-danger {
        background-color: rgba(231, 76, 60, 0.1);
        color: #e74c3c;
        border-left: 4px solid #e74c3c;
    }

    .no-data {
        text-align: center;
        padding: 30px;
        font-style: italic;
        color: #666;
    }
</style>

<?php include_once 'includes/footer.php'; ?>