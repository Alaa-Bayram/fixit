<?php
session_start();

if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang = $_SESSION['lang'] ?? 'en'; // Default to English

// Sanitize and restrict to allowed values
$allowed_langs = ['en', 'fr', 'ar'];
if (!in_array($lang, $allowed_langs)) {
    $lang = 'en';
}

// Dynamically include the correct language file
include_once __DIR__ . '\lang\lang_' . $lang . '.php';
