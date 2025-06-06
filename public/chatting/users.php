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
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';
$lang_file = "../lang/$lang.php";

if (file_exists($lang_file)) {
    $translations = include($lang_file);
} else {
    $translations = include("../lang/en.php"); // fallback
}
?>

<!DOCTYPE html>
<html lang="en">
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
        <div class="chat-placeholder" style="margin-left: 30px;margin-top:30px">
            <div class="placeholder-icon">
                <i class="far fa-comments"></i>
            </div>
            <h3 >Select a conversation</h3>
            <p>Choose a contact from the list to start chatting</p>
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