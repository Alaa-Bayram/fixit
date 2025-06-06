<?php
include_once "../db.php";

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get language from URL parameter or session (default to English)
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'fr', 'ar'])) {
    $_SESSION['lang'] = $_GET['lang'];
    $lang = $_GET['lang'];
} elseif (isset($_SESSION['lang']) && in_array($_SESSION['lang'], ['en', 'fr', 'ar'])) {
    $lang = $_SESSION['lang'];
} else {
    $lang = 'en';
}

// Determine which columns to select based on language
if ($lang === 'en') {
    $title_col = "title";
    $desc_col = "description";
} else {
    $title_col = "title_" . $lang;
    $desc_col = "description_" . $lang;
}

// Prepare and execute the query
try {
    $sql = "SELECT 
                service_id, 
                images, 
                date, 
                $title_col AS title, 
                $desc_col AS description 
            FROM services";
    
    $stmt = $conn->query($sql);
    
    if ($stmt) {
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($services);
    } else {
        echo json_encode(['error' => 'Failed to fetch services']);
    }
} catch (PDOException $e) {
    // Log error for debugging
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error occurred']);
}

$conn = null; // Close PDO connection
?>