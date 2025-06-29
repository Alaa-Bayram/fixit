<?php 
session_start();
include_once "../php/db.php";

if(!isset($_SESSION['unique_id'])) {
    header("location: login.php");
}

$unique_id = $_SESSION['unique_id'] ?? '';
$user_id = $_SESSION['user_id'] ?? '';
$fname = $_SESSION['fname'] ?? 'Guest';
$fname = $_SESSION['phone'] ?? 'Unknown';

$profile_url = "../profile.php?user_id=" . urlencode($user_id) . "&fname=" . urlencode($fname);

$unique_id = mysqli_real_escape_string($conn, $_GET['unique_id']);
$sql = "SELECT * FROM users WHERE unique_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $unique_id);
$stmt->execute();
$result = $stmt->get_result();

if(mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
} else {
    header("location: ../php/get-chat.php");
    exit();
}

// Set language (priority: GET > SESSION > COOKIE > default 'en')
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
<body>

<?php include_once "header1.php"; ?>

<div class="premium-chat-container">
    <section class="users-sidebar">
        <div class="user-header">
            <img src="../<?php echo $_SESSION['img'] ?? 'images/default-profile.jpg'; ?>" alt="Profile" class="user-avatar">
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($_SESSION['fname'] ?? 'User'); ?></div>
                <div class="user-status">
                    <span class="status-indicator <?php echo strtolower($_SESSION['status'] ?? 'offline'); ?>"></span>
                    <?php echo htmlspecialchars($_SESSION['status'] ?? 'offline'); ?>
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
        <div class="chat-header">
            <a href="users.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <img src="../php/images/<?php echo $row['img']; ?>" alt="<?php echo $row['fname']; ?>" class="chat-avatar">
            <div class="chat-info">
                <div class="chat-name"><?php echo $row['fname'] . " " . $row['lname']; ?></div>
                <div class="chat-status">
                    <span class="status-indicator <?php echo strtolower($row['status']); ?>"></span>
                    <?php echo $row['status']; ?>
                </div>
            </div>

        </div>
        
        <div class="messages-container" id="messagesContainer">
            <!-- Messages will be loaded here via JavaScript -->
        </div>
        
        <div class="message-input-container">
            <form class="message-form" id="messageForm">
                <input type="text" name="message" class="message-input" placeholder="Type your message here..." autocomplete="off">
                <input type="hidden" name="incoming_id" value="<?php echo $unique_id; ?>">
                <button type="submit" class="send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </section>
</div>

<script src="../javascript/chat.js"></script>
<script src="logout.js"></script>
<script>
    const CURRENT_USER_ID = "<?php echo $_SESSION['unique_id'] ?? ''; ?>";

</script>
</body>
</html>