<?php 
session_start();
include_once "php/db.php";

if(!isset($_SESSION['unique_id'])) {
    header("location: login.php");
}

$unique_id = $_SESSION['unique_id'] ?? '';
$user_id = $_SESSION['user_id'] ?? '';
$fname = $_SESSION['fname'] ?? 'Guest';
$phone = $_SESSION['phone'] ?? 'Unknown';

$profile_url = "worker_profile.php?user_id=" . urlencode($user_id) . "&fname=" . urlencode($fname);

$unique_id = mysqli_real_escape_string($conn, $_GET['unique_id']);
$sql = "SELECT * FROM users WHERE unique_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $unique_id);
$stmt->execute();
$result = $stmt->get_result();

if(mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
} else {
    header("location: php/get-chat.php");
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
<html lang="en">
    <head>
            <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap');
*{
  margin: 0px;
  padding: 0px;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
}
/* Language Switcher Styles */
.lang-switcher {
    position: relative;
    margin-left: 15px;
}

.language-selector {
    position: relative;
    display: flex;
    align-items: center;
}

.language-trigger {
    background: none;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    padding: 8px 12px;
    border-radius: 4px;
    transition: all 0.3s ease;
    color: #fff;
    font-size: 1rem;
}

.language-trigger:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.language-trigger i {
    margin-right: 8px;
    font-size: 1.2rem;
}

.current-language {
    font-weight: 500;
    font-size: 0.9rem;
}

.language-dropdown {
    position: relative;
}

.language-select {
    position: absolute;
    top: 100%;
    right: 0;
    min-width: 120px;
    padding: 8px 12px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: all 0.3s ease;
    cursor: pointer;
    z-index: 1000;
}

.language-selector:hover .language-select,
.language-select:focus {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

/* For mobile responsiveness */
@media (max-width: 768px) {
    .language-select {
        right: auto;
        left: 0;
    }
    
    .language-trigger {
        padding: 8px;
    }
    
    .current-language {
        display: none;
    }
}
    </style>
    </head>
<body>

<?php include_once "header1.html"; ?>
<div class="header1">
<nav class="navbar">
            <div class="logo"><a href="#"><img src="app_images/logo1.png"></a></div>
            <input type="checkbox" id="menu-toggler">
            <label for="menu-toggler" id="hamburger-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="24px" height="24px">
                    <path d="M0 0h24v24H0z" fill="none"/>
                    <path d="M3 18h18v-2H3v2zm0-5h18V11H3v2zm0-7v2h18V6H3z"/>
                </svg>
            </label>
            <ul class="all-links">
                <li><a href="worker_home.php">Home</a></li>
                <li><a href="worker_emergencies.php">Emergency</a></li>
                <li><a href="worker_dash.php">Appointments</a></li>
                <li><a href="worker_schedule.php" target="_blank">My Schedule</a></li>
                                             <li class="lang-switcher">
                    <div class="language-selector">
                        <button class="language-trigger" aria-label="Language selector" title="<?= $translations['change_language'] ?? 'Change Language' ?>">
                            <i class="fas fa-globe"></i>
                            <span class="current-language"><?= strtoupper($lang) ?></span>
                        </button>
                        <div class="language-dropdown">
                            <select class="language-select" onchange="changeLang(this.value)">
                                <option value="en" <?= $lang == 'en' ? 'selected' : '' ?>>English</option>
                                <option value="ar" <?= $lang == 'ar' ? 'selected' : '' ?>>عربي</option>
                                <option value="fr" <?= $lang == 'fr' ? 'selected' : '' ?>>Français</option>
                            </select>
                        </div>
                    </div>
                </li>
                <!-- Logout button -->
                <li><a href="#" class="logout" onclick="logout()">Logout</a></li>
            </ul>
        </nav>
  </div>

<div class="premium-chat-container">
    <section class="users-sidebar">
        <div class="user-header">
            <img src="php/<?php echo $_SESSION['img'] ?? 'images/default-profile.jpg'; ?>" alt="Profile" class="user-avatar">
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
            <a href="worker_users.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <img src="images/<?php echo $row['img']; ?>" alt="<?php echo $row['fname']; ?>" class="chat-avatar">
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

<script src="javascript/worker-chat.js"></script>
<script src="javascript/logout.js"></script>
<script>
    const CURRENT_USER_ID = "<?php echo $_SESSION['unique_id'] ?? ''; ?>";
</script>
</body>
</html>