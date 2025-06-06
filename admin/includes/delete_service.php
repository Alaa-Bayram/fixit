<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_once 'translation/trans.php';

// Check if user is logged in with appropriate permissions
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    $_SESSION['error_message'] = "Unauthorized access.";
    header('Location: ../login.php');
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['error_message'] = "Invalid form submission.";
    header('Location: ../all_articles.php');
    exit();
}

// Check if form was submitted with the article ID
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['article_id'])) {
    // Sanitize and validate the article ID
    $article_id = filter_var($_POST['article_id'], FILTER_VALIDATE_INT);
    
    if (!$article_id) {
        $_SESSION['error_message'] = "Invalid article ID.";
        header('Location: ../all_articles.php');
        exit();
    }
    
    try {
        // Using PDO for database operations
        $stmt = $pdo->prepare("SELECT images FROM articles WHERE article_id = :article_id");
        $stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Get the image filename to delete from server
        $article = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($article) {
            // Delete the article from database
            $delete_stmt = $pdo->prepare("DELETE FROM articles WHERE article_id = :article_id");
            $delete_stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
            
            if ($delete_stmt->execute()) {
                // Delete the associated image file if it exists
                if (!empty($article['images'])) {
                    $image_path = '../../public/images/' . $article['images'];
                    if (file_exists($image_path)) {
                        unlink($image_path);
                    }
                }
                
                $_SESSION['success_message'] = "Article deleted successfully.";
            } else {
                $_SESSION['error_message'] = "Failed to delete article.";
            }
        } else {
            $_SESSION['error_message'] = "Article not found.";
        }
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        $_SESSION['error_message'] = "Database error occurred. Please try again.";
    }
    
    // Redirect back to articles list
    $lang = $_GET['lang'] ?? 'en';
    header('Location: ../all_articles.php?lang=' . $lang);
    exit();
} else {
    // If accessed directly without POST data
    $_SESSION['error_message'] = "Invalid request.";
    $lang = $_GET['lang'] ?? 'en';
    header('Location: ../all_articles.php?lang=' . $lang);
    exit();
}


?>