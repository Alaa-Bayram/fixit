<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    header("Location: login.html");
    exit();
}

// Language selection (default to English)
$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en');
$_SESSION['lang'] = $lang;
$lang_file = "lang/$lang.php";

if (file_exists($lang_file)) {
    $translations = include($lang_file);
} else {
    $translations = include("lang/en.php"); // fallback
}

// Include PHPMailer libraries
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Function to send email
function sendEmail($from, $to, $fname, $lname, $subject, $message, $translations) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'fixitapp.team@gmail.com';
        $mail->Password   = 'eybh rmjq nvbm mmub';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $email = isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : '';

        $mail->setFrom($from, $email);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $translations['email_greeting'] . ",<br><br>$message<br><br>" . 
                         $translations['email_signature'] . ",<br>$fname $lname.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $name = isset($_SESSION['fname']) ? htmlspecialchars($_SESSION['fname'] . ' ' . $_SESSION['lname']) : '';
    $email = isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : '';
    $message = isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '';

    // Validate and send email
    if (!empty($name) && !empty($email) && !empty($message)) {
        $to = 'fixitapp.team@gmail.com';
        $subject = $translations['email_subject'] ?? 'Message from FixIt Contact Form';
        $fname = $_SESSION['fname'];
        $lname = $_SESSION['lname'];

        if (sendEmail($email, $to, $fname, $lname, $subject, $message, $translations)) {
            // Display success message
            echo '<script>window.onload = function() { document.getElementById("success-message").style.display = "block"; }</script>';
        } else {
            // Display error message
            echo '<script>window.onload = function() { document.getElementById("error-message").style.display = "block"; }</script>';
        }
    } else {
        // Display error message if required fields are empty
        echo '<script>window.onload = function() { document.getElementById("empty-fields-message").style.display = "block"; }</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8" />
    <title><?= $translations['contact_page_title'] ?? 'Contact Us' ?> | Fixit</title>
    <link rel="stylesheet" href="css/contact.css" />
    <!-- Fontawesome CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo"><a href="home.php?lang=<?= $lang ?>"><img src="app_images/logo1.png"></a></div>
            <input type="checkbox" id="menu-toggler">
            <label for="menu-toggler" id="hamburger-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="24px" height="24px">
                    <path d="M0 0h24v24H0z" fill="none"/>
                    <path d="M3 18h18v-2H3v2zm0-5h18V11H3v2zm0-7v2h18V6H3z"/>
                </svg>
            </label>
            <ul class="all-links">
                <?php
                // Function to generate navigation links with preserved parameters
                function navLink($page, $icon, $text) {
                    global $lang;
                    $currentParams = $_GET;
                    $url = "$page.php?" . http_build_query(array_merge($currentParams, ['lang' => $lang]));
                    echo "<li><a href=\"$url\"><i class=\"$icon\"></i> $text</a></li>";
                }
                
                // Navigation items with translations
                navLink('home', 'fas fa-home', $translations['home'] ?? 'Home');
                navLink('services', 'fas fa-tools', $translations['services'] ?? 'Services');
                navLink('tips', 'fas fa-lightbulb', $translations['tips'] ?? 'Tips');
                navLink('contact', 'fas fa-envelope', $translations['contact'] ?? 'Contact');
                navLink('profile', 'fas fa-user', $translations['profile'] ?? 'Profile');
                ?>
                
                <li class="language-selector">
                    <div class="dropdown">
                        <button class="dropbtn">
                            <span class="current-language">
                                <?= match($lang) {
                                    'en' => '🌐 EN',
                                    'fr' => '🌐 FR',
                                    'ar' => '🌐 عربي',
                                    default => '🌐 EN'
                                } ?>
                            </span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="dropdown-content">
                            <?php
                            $languages = [
                                'en' => $translations['english'] ?? 'English',
                                'ar' => $translations['arabic'] ?? 'العربية', 
                                'fr' => $translations['french'] ?? 'Français'
                            ];
                            
                            foreach ($languages as $code => $name) {
                                $urlParams = array_merge($_GET, ['lang' => $code]);
                                $activeClass = ($lang === $code) ? 'active' : '';
                                echo "<a href='?".http_build_query($urlParams)."' class='$activeClass'>$name</a>";
                            }
                            ?>
                        </div>
                    </div>
                </li>
                
                <li><a href="#" class="logout" onclick="logout()"><?= $translations['logout'] ?? 'Logout' ?></a></li>
            </ul>
        </nav>
    </header>

<!-- Success Message Popup -->
<div id="success-message" class="popup-container" style="display: none;">
    <div class="popup">
        <span class="close-btn">&times;</span>
        <p><?= $translations['success_message'] ?? 'Thank you for your message! We will try our best to help you.' ?></p>
    </div>
</div>

<!-- Error Message Popup -->
<div id="error-message" class="popup-container" style="display: none;">
    <div class="popup">
        <span class="close-btn">&times;</span>
        <p><?= $translations['error_message'] ?? 'Message could not be sent. Please try again later.' ?></p>
    </div>
</div>

<!-- Empty Fields Message Popup -->
<div id="empty-fields-message" class="popup-container" style="display: none;">
    <div class="popup">
        <span class="close-btn">&times;</span>
        <p><?= $translations['empty_fields_message'] ?? 'Please fill out all required fields.' ?></p>
    </div>
</div>

<div class="container">
    <div class="content">
        <div class="left-side">
            <div class="phone details">
                <i class="fas fa-phone-alt"></i>
                <div class="topic"><?= $translations['phone_label'] ?? 'Phone' ?></div>
                <div class="text-one">+961 76666777</div>
                <div class="text-two">+961 70666777</div>
            </div>
            <div class="email details">
                <i class="fas fa-envelope"></i>
                <div class="topic"><?= $translations['email_label'] ?? 'Email' ?></div>
                <div class="text-one">fixitapp.team@gmail.com</div>
            </div>
        </div>
        
        <div class="right-side">
            <div class="topic-text"><?= $translations['contact_heading'] ?? 'Need Help? Reach Fixit Team' ?></div>
            <p><?= $translations['contact_description'] ?? 'Contact Fixit for any assistance or questions you may have. We\'re here to help with all your maintenance needs.' ?></p>
            <form action="#" method="post">
                <div class="input-box">
                    <input type="text" placeholder="<?= $translations['name_placeholder'] ?? 'Enter your name' ?>" autocomplete="off" value="<?php echo isset($_SESSION['fname']) ? htmlspecialchars($_SESSION['fname'] . ' ' . $_SESSION['lname']) : ''; ?>" readonly />
                </div>
                <div class="input-box">
                    <input type="text" placeholder="<?= $translations['email_placeholder'] ?? 'Enter your email' ?>" autocomplete="off" value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>" readonly />
                </div>
                <div class="input-box message-box">
                    <textarea name="message" placeholder="<?= $translations['message_placeholder'] ?? 'Enter your message' ?>"></textarea>
                </div>
                <div class="button">
                    <input type="submit" value="<?= $translations['send_button'] ?? 'Send Now' ?>" />
                </div>
            </form>
        </div>
    </div>
</div>
<script src="javascript/logout.js"></script>
<script>
    // JavaScript for closing popup messages
    document.addEventListener('DOMContentLoaded', function() {
        var closeBtns = document.querySelectorAll('.close-btn');
        closeBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var popupContainer = this.closest('.popup-container');
                if (popupContainer) {
                    popupContainer.style.display = 'none';
                }
            });
        });
    });
</script>
</body>
</html>