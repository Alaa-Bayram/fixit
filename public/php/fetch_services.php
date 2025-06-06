<?php
include_once "db.php";

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

// Determine which column to use for title based on language
$title_col = ($lang === 'en') ? 'title' : 'title_' . $lang;

// Query to get services with appropriate language title
$sql = "SELECT service_id, $title_col AS title FROM services";
$result = $conn->query($sql);

// Generate options for select dropdown
$services_options = '';
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Fallback to English if translation is empty
        $display_title = !empty($row['title']) ? $row['title'] : '';
        
        // If we're not in English and title is empty, try to get English version
        if ($lang !== 'en' && empty($display_title)) {
            $fallback_sql = "SELECT title FROM services WHERE service_id = " . $row['service_id'];
            $fallback_result = $conn->query($fallback_sql);
            if ($fallback_row = $fallback_result->fetch_assoc()) {
                $display_title = $fallback_row['title'];
            }
        }
        
        if (!empty($display_title)) {
            $services_options .= '<option value="' . $row['service_id'] . '">' . 
                                htmlspecialchars($display_title) . '</option>';
        }
    }
}

// If no services found or no valid titles
if (empty($services_options)) {
    $services_options = '<option value="">' . 
                       ($lang === 'fr' ? 'Aucun service trouvé' : 
                        ($lang === 'ar' ? 'لم يتم العثور على خدمات' : 
                         'No Services Found')) . 
                       '</option>';
}

$conn->close();
?>