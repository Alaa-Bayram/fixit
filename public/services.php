<?php 
session_start(); // Start session

// Check if user is authenticated
if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    header("Location: login.html");
    exit();
}

// Language selection (default to English)
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';
$lang_file = "lang/$lang.php";

if (file_exists($lang_file)) {
    $translations = include($lang_file);
} else {
    $translations = include("lang/en.php"); // fallback
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <link rel="stylesheet" href="css/search.css">
</head>
<body>
    <?php include_once "header.php"; ?>

<button class="chatbot-toggler">
    <a href="chatting/users.php?<?= http_build_query(array_merge($_GET, ['lang' => $lang])) ?>">
        <i class="bi bi-chat-left" style="color: white; font-size: 24px;"></i>
    </a>
</button>
    <br><button class="emergency-toggler">
        <a href="emergency.php"><i class="bi bi-exclamation-triangle-fill" style="color: white;font-size: 24px;"></i></a>
    </button>

    <section class="portfolio" id="portfolio">
        <h2><?= $translations['our_services'] ?? 'Our Services' ?></h2>
        <p><?= $translations['services_intro'] ?? 'From cozy homes to bustling offices, FixIt\'s expertise in plumbing, electrical, HVAC, gardening, and more transforms every space into a haven of comfort and functionality.' ?></p>
        
        <div class="wrapper">
            <div class="search-box">
                <i class="bx bx-search"></i>
                <input type="text" placeholder="<?= $translations['search_placeholder'] ?? 'Search for a service' ?>" />
                <div class="icon"><i class="fas fa-search"></i></div>
            </div>
        </div>
        
        <ul class="cards" id="services-list"></ul>
    </section>

    

    <?php include_once "footer.html"; ?>

    <script src="javascript/script.js"></script>
</body>
</html>