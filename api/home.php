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

// For English, use the base columns (title, description)
// For other languages, use the suffixed columns (title_fr, description_fr, etc.)
if ($lang === 'en') {
    $title_col = "title";
    $desc_col = "description";
} else {
    $title_col = "title_" . $lang;
    $desc_col = "description_" . $lang;
}

$sql = "SELECT service_id, images, date, $title_col AS title, $desc_col AS description FROM services ORDER BY date DESC LIMIT 3";
$stmt = $conn->query($sql);

if ($stmt) {
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($services);
} else {
    echo json_encode(array('error' => 'Failed to fetch services'));
}

$conn = null; // Close PDO connection
?>