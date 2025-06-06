<?php
// Include the language file to access translations
include_once 'lang.php';

$servicePages = ['services.php', 'all_services.php', 'edit_service.php', 'delete_service.php'];
$isServicesActive = in_array($currentPage, $servicePages);

$ArticlesPages = ['articles.php', 'all_articles.php', 'edit_article.php', 'delete_article.php'];
$isArticlesActive = in_array($currentPage, $ArticlesPages);

$DashboardPages = ['index.php','appointments.php'];
$isDashboardActive = in_array($currentPage, $DashboardPages);

$TipsPages = ['tips.php','edit_dailyTip.php','edit_seasonalTip.php','all_tips.php'];
$isTipsActive = in_array($currentPage, $TipsPages);

// Function to build URL with current language
function buildLangUrl($page, $lang) {
    return $page . '?lang=' . $lang;
}

// Get current language
$currentLang = $_SESSION['lang'] ?? 'en';
?>

<div class="sidebar">
    <div class="logo-details">
        <img src="assets/images/logo1.png" class="logo" alt="FixIt Logo">
    </div>
    <ul class="nav-links">
        <li>
            <a href="<?php echo buildLangUrl('index.php', $currentLang); ?>" class="<?php echo $isDashboardActive ? 'active' : ''; ?>">
                <i class="bx bx-grid-alt"></i>
                <span class="links_name"><?php echo $trans['dashboard']; ?></span>
            </a>
        </li>
        <li>
            <a href="<?php echo buildLangUrl('workers.php', $currentLang); ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == 'workers.php' ? 'active' : ''; ?>">
                <i class="bx bx-user-plus"></i>
                <span class="links_name"><?php echo $trans['workers_requests']; ?></span>
            </a>
        </li>
        <li>
            <a href="<?php echo buildLangUrl('view_approved_workers.php', $currentLang); ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == 'view_approved_workers.php' ? 'active' : ''; ?>">
                <i class="bx bx-user-check"></i>
                <span class="links_name"><?php echo $trans['fixit_app_workers']; ?></span>
            </a>
        </li>
        <li>
            <a href="<?php echo buildLangUrl('services.php', $currentLang); ?>" class="<?php echo $isServicesActive ? 'active' : ''; ?>">
                <i class="bx bx-wrench"></i>
                <span class="links_name"><?php echo $trans['services']; ?></span>
            </a>
        </li>
        <li>
            <a href="<?php echo buildLangUrl('articles.php', $currentLang); ?>" class="<?php echo $isArticlesActive ? 'active' : ''; ?>">
                <i class="bx bx-book-content"></i>
                <span class="links_name"><?php echo $trans['articles']; ?></span>
            </a>
        </li>
        <li>
            <a href="<?php echo buildLangUrl('tips.php', $currentLang); ?>" class="<?php echo $isTipsActive ? 'active' : ''; ?>">
                <i class="bx bx-bulb"></i>
                <span class="links_name"><?php echo $trans['tips']; ?></span>
            </a>
        </li>
        <li>
            <a href="<?php echo buildLangUrl('client_reviews.php', $currentLang); ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == 'client_reviews.php' ? 'active' : ''; ?>">
                <i class="bx bx-message"></i>
                <span class="links_name"><?php echo $trans['client_reviews']; ?></span>
            </a>
        </li>
        <!-- <li class="log_out">
            <a href="<?php echo buildLangUrl('logout.php', $currentLang); ?>">
                <i class="bx bx-log-out"></i>
                <span class="links_name"><?php echo $trans['logout']; ?></span>
            </a>
        </li> -->
    </ul>
</div>