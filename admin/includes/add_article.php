<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_once 'translation/trans.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "User not logged in!";
    header('Location: ../login.php');
    exit();
}

// Get language parameter
$lang = $_GET['lang'] ?? $_POST['lang'] ?? 'en';

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['error_message'] = "Invalid form submission.";
    header('Location: ../articles.php?lang=' . $lang);
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Sanitize input
    $title = sanitize_input($_POST['title'] ?? '');
    $description = sanitize_input($_POST['desc'] ?? '');
    $sec_title = sanitize_input($_POST['sec_title'] ?? '');
    $content1 = sanitize_input($_POST['content1'] ?? '');
    $tert_title = sanitize_input($_POST['tert_title'] ?? '');
    $content2 = sanitize_input($_POST['content2'] ?? '');
    $date = date('Y-m-d H:i:s');
    
    // Validate input
    if (empty($title) || empty($description)) {
        $_SESSION['error_message'] = "Title and description are required.";
        header('Location: ../articles.php?lang=' . $lang);
        exit();
    }
    
    try {
        // Check if the article already exists
        $stmt = $pdo->prepare("SELECT article_id FROM articles WHERE title = :title");
        $stmt->bindParam(':title', $title);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['error_message'] = "Article already exists!";
            header("Location: ../articles.php?lang=" . $lang);
            exit();
        }
        
        // Check if an image was uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            $allowed_extensions = ['jpeg', 'jpg', 'png', 'webp'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            $img_name = $_FILES['image']['name'];
            $img_type = $_FILES['image']['type'];
            $img_size = $_FILES['image']['size'];
            $tmp_name = $_FILES['image']['tmp_name'];
            
            $img_explode = explode('.', $img_name);
            $img_ext = strtolower(end($img_explode));
            
            // Validate file type, extension and size
            if (!in_array($img_type, $allowed_types) || !in_array($img_ext, $allowed_extensions)) {
                $_SESSION['error_message'] = "Only JPG, PNG, and WEBP images are allowed.";
                header('Location: ../articles.php?lang=' . $lang);
                exit();
            }
            
            if ($img_size > $max_size) {
                $_SESSION['error_message'] = "Image size should be less than 2MB.";
                header('Location: ../articles.php?lang=' . $lang);
                exit();
            }
            
            // Generate a unique filename
            $new_img_name = time() . '_' . $img_name;
            $upload_path = '../../public/images/' . $new_img_name;
            
            // Create directory if it doesn't exist
            $dir = '../../public/images/';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Move the uploaded file
            if (move_uploaded_file($tmp_name, $upload_path)) {
                // Translate content to French and Arabic
                $title_fr = translateText($title, 'en|fr');
                $description_fr = translateText($description, 'en|fr');
                $sec_title_fr = !empty($sec_title) ? translateText($sec_title, 'en|fr') : '';
                $content1_fr = !empty($content1) ? translateText($content1, 'en|fr') : '';
                $tert_title_fr = !empty($tert_title) ? translateText($tert_title, 'en|fr') : '';
                $content2_fr = !empty($content2) ? translateText($content2, 'en|fr') : '';
                
                $title_ar = translateText($title, 'en|ar');
                $description_ar = translateText($description, 'en|ar');
                $sec_title_ar = !empty($sec_title) ? translateText($sec_title, 'en|ar') : '';
                $content1_ar = !empty($content1) ? translateText($content1, 'en|ar') : '';
                $tert_title_ar = !empty($tert_title) ? translateText($tert_title, 'en|ar') : '';
                $content2_ar = !empty($content2) ? translateText($content2, 'en|ar') : '';
                
                // Insert data into the database with translations
                $stmt = $pdo->prepare("INSERT INTO articles (title, description, sec_title, content1, tert_title, content2, images, date, user_id, title_fr, description_fr, sec_title_fr, content1_fr, tert_title_fr, content2_fr, title_ar, description_ar, sec_title_ar, content1_ar, tert_title_ar, content2_ar) VALUES (:title, :description, :sec_title, :content1, :tert_title, :content2, :images, :date, :user_id, :title_fr, :description_fr, :sec_title_fr, :content1_fr, :tert_title_fr, :content2_fr, :title_ar, :description_ar, :sec_title_ar, :content1_ar, :tert_title_ar, :content2_ar)");
                
                // Bind original parameters
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':sec_title', $sec_title);
                $stmt->bindParam(':content1', $content1);
                $stmt->bindParam(':tert_title', $tert_title);
                $stmt->bindParam(':content2', $content2);
                $stmt->bindParam(':images', $new_img_name);
                $stmt->bindParam(':date', $date);
                $stmt->bindParam(':user_id', $user_id);
                
                // Bind French translation parameters
                $stmt->bindParam(':title_fr', $title_fr);
                $stmt->bindParam(':description_fr', $description_fr);
                $stmt->bindParam(':sec_title_fr', $sec_title_fr);
                $stmt->bindParam(':content1_fr', $content1_fr);
                $stmt->bindParam(':tert_title_fr', $tert_title_fr);
                $stmt->bindParam(':content2_fr', $content2_fr);
                
                // Bind Arabic translation parameters
                $stmt->bindParam(':title_ar', $title_ar);
                $stmt->bindParam(':description_ar', $description_ar);
                $stmt->bindParam(':sec_title_ar', $sec_title_ar);
                $stmt->bindParam(':content1_ar', $content1_ar);
                $stmt->bindParam(':tert_title_ar', $tert_title_ar);
                $stmt->bindParam(':content2_ar', $content2_ar);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Article added successfully!";
                } else {
                    $_SESSION['error_message'] = "Failed to add article. Please try again.";
                }
            } else {
                $_SESSION['error_message'] = "Failed to upload image. Please try again.";
            }
        } else {
            $_SESSION['error_message'] = "Please select an image.";
        }
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        $_SESSION['error_message'] = "Database error occurred. Please try again.";
    }
    
    // Redirect back to articles page
    header('Location: ../articles.php?lang=' . $lang);
    exit();
}

// If not POST request, redirect
header('Location: ../articles.php?lang=' . $lang);
exit();
?>