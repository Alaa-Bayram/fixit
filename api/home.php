<?php
include_once "../db.php";

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if language is set in URL and update session
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'fr', 'ar'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Default language is English
$lang = isset($_SESSION['lang']) && in_array($_SESSION['lang'], ['en', 'fr', 'ar']) ? $_SESSION['lang'] : 'en';

// Prepare the SQL query with language-specific columns
$sql = "SELECT 
            service_id, 
            images, 
            date, 
            CASE 
                WHEN :lang = 'ar' THEN COALESCE(title_ar, title) 
                WHEN :lang = 'fr' THEN COALESCE(title_fr, title) 
                ELSE title 
            END AS title,
            CASE 
                WHEN :lang = 'ar' THEN COALESCE(description_ar, description) 
                WHEN :lang = 'fr' THEN COALESCE(description_fr, description) 
                ELSE description 
            END AS description
        FROM services 
        ORDER BY date DESC 
        LIMIT 3";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':lang', $lang, PDO::PARAM_STR);
    $stmt->execute();
    
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no results found, return empty array
    if (empty($services)) {
        echo json_encode([]);
    } else {
        echo json_encode($services);
    }
    
} catch (PDOException $e) {
    // Log error (in production, you'd want to log this properly)
    error_log("Database error: " . $e->getMessage());
    
    // Return error response
    echo json_encode(array(
        'error' => 'Failed to fetch services',
        'message' => $e->getMessage() // Only for debugging, remove in production
    ));
}

$conn = null; // Close PDO connection
?>