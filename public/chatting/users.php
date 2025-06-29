<?php
session_start();

if (!isset($_SESSION['authenticated'])) {
    header("Location: login.html");
    exit();
}

// Retrieve user information
$unique_id = $_SESSION['unique_id'] ?? '';
$user_id = $_SESSION['user_id'] ?? '';
$fname = $_SESSION['fname'] ?? 'Guest';
$user_name = $_SESSION['fname'] ?? 'Guest';
$sts = $_SESSION['status'] ?? 'offline';
$ut = $_SESSION['usertype'] ?? 'GUEST';
$profile_image = $_SESSION['img'] ?? 'default-profile.jpg';

$profile_url = "../profile.php?user_id=" . urlencode($user_id) . "&fname=" . urlencode($fname);

// Language selection (default to English)
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? $_COOKIE['lang'] ?? 'en';
$_SESSION['lang'] = $lang;
setcookie('lang', $lang, time() + (86400 * 30), "/"); // 30 days
$lang_file = "../lang/$lang.php";

if (file_exists($lang_file)) {
    $translations = include($lang_file);
} else {
    $translations = include("../lang/en.php"); // fallback
}
?>

<!DOCTYPE html>
<html lang="en" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
    <head>
        <style>
            /* For Arabic (RTL) layout */
[dir="rtl"] .chat-placeholder {
    margin-right: 30px;
    margin-left: 0;
    text-align: right;
}

/* Ensure icon stays properly aligned in RTL */
[dir="rtl"] .placeholder-icon {
    margin-left: 0;
    margin-right: auto;
}
        </style>
    </head>
<body>

<?php include_once "header1.php"; ?>

<div class="premium-chat-container">
    <section class="users-sidebar">
        <div class="user-header">
            <img src="../<?php echo htmlspecialchars($profile_image)?>" alt="Profile" class="user-avatar">
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                <div class="user-status">
                    <span class="status-indicator <?php echo strtolower($sts); ?>"></span>
                    <?php echo htmlspecialchars($sts); ?> | <?php echo htmlspecialchars($ut); ?>
                </div>
            </div>
            <a href="<?php echo $profile_url; ?>" class="profile-btn">
                <i class="fas fa-user-edit"></i>
            </a>
        </div>
        
        <div class="search-container">
            <input type="text" class="search-input" placeholder="Search contacts...">
        </div>
        
        <div class="users-list-container">
            <ul class="users-list" id="usersList">
                <!-- Users will be loaded here via JavaScript -->
            </ul>
        </div>
    </section>
    
<section class="chat-area">
    <div class="chat-placeholder" style="margin-left: 30px; margin-top: 30px">
        <div class="placeholder-icon">
            <i class="far fa-comments"></i>
        </div>
        <h3><?= $translations['select_conversation'] ?? 'Select a conversation' ?></h3>
        <p><?= $translations['choose_contact'] ?? 'Choose a contact from the list to start chatting' ?></p>
    </div>
</section>
</div>

<script src="../javascript/users.js"></script>
<script src="logout.js"></script>
<script>
    const CURRENT_USER_ID = "<?php echo $_SESSION['unique_id'] ?? ''; ?>";

</script>
</body>
</html>