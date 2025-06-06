<?php
session_start();

// Get language from URL parameter, session, or default to English
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'en';

// Validate language code
$available_languages = ['en', 'ar', 'fr'];
if (!in_array($lang, $available_languages)) {
    $lang = 'en';
}

// Store language in session
$_SESSION['lang'] = $lang;

// Load translation file
$translations = [];
$lang_file = __DIR__ . "/../lang/{$lang}.php";

if (file_exists($lang_file)) {
    include $lang_file;
} else {
    // Fallback to English if file doesn't exist
    include __DIR__ . "/../lang/en.php";
}

// Translation helper function
function t($key, $default = '') {
    global $translations;
    return $translations[$key] ?? $default ?: $key;
}

// Get current page URL with parameters (excluding lang)
function get_current_url_without_lang() {
    $url_parts = parse_url($_SERVER['REQUEST_URI']);
    $path = $url_parts['path'];
    
    if (isset($url_parts['query'])) {
        parse_str($url_parts['query'], $query_params);
        unset($query_params['lang']); // Remove lang parameter
        
        if (!empty($query_params)) {
            $path .= '?' . http_build_query($query_params);
        }
    }
    
    return $path;
}
?>