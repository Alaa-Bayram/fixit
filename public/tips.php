<?php 
session_start();
include_once "php/db.php";
if(!isset($_SESSION['authenticated'])){
  header("location: login.html");
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

$query = "SELECT * FROM articles ORDER BY date DESC LIMIT 3";
$result = mysqli_query($conn, $query);

// Query for the latest daily tip
$query1 = "SELECT * FROM tips WHERE type='daily tips' ORDER BY date DESC LIMIT 1";
$result1 = mysqli_query($conn, $query1);

// Query for the latest seasonal tip
$query2 = "SELECT * FROM tips WHERE type='seasonal tips' ORDER BY date DESC LIMIT 1";
$result2 = mysqli_query($conn, $query2);

// Debugging: Check if query was successful
if (!$result || !$result1 || !$result2) {
  die("Query Failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
  <meta charset="UTF-8">
  <title><?= $translations['tips'] ?? 'Tips' ?></title>
  <link rel="stylesheet" href="css/tips.css">
  <link rel="stylesheet" href="css/stylebtn.css">
  <link rel="stylesheet" href="css/ar.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<button class="chatbot-toggler">
  <a href="chatting/users.php?<?= http_build_query(array_merge($_GET, ['lang' => $lang])) ?>"><i class="bi bi-chat-left" style="color: white; font-size: 24px;"></i></a>  
</button>
<br><button class="emergency-toggler">
    <a href="emergency.php?<?= http_build_query(array_merge($_GET, ['lang' => $lang])) ?>"><i class="bi bi-exclamation-triangle-fill" style="color: white;font-size: 24px;"></i></a>
</button>

     <header1>
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
    </header1>

    
<div class="homepage <?= $lang === 'ar' ? 'rtl' : 'ltr' ?>" id="home">
  <img src="app_images/pixelcut.jpeg" alt="Background Image">
</div>

<header class="<?= $lang === 'ar' ? 'rtl-header' : 'ltr-header' ?>">
  <div class="content">
    <div class="text-content">
      <div class="text"><?= $translations['good_morning'] ?? 'Good morning, home hero!' ?></div>
      <div class="name"><?= $translations['ready_challenge'] ?? 'Ready to conquer today\'s maintenance challenges?' ?></div>
      <div class="job">
        <div class="typing-text">
          <span class="one"><?= $translations['today_tip'] ?? 'Today\'s tip, tomorrow\'s ease.' ?></span><br>
          <span class="two"><?= $translations['empower_daily'] ?? 'Empower yourself daily.' ?></span>
        </div>
      </div>
      <div class="buttons">
        <a href="#daily"><button><?= $translations['daily_tips'] ?? 'Daily Tips' ?></button></a>
        <a href="#seasonal"><button><?= $translations['seasonal_tips'] ?? 'Seasonal Tips' ?></button></a>
        <a href="#hints"><button><?= $translations['hints'] ?? 'Hints' ?></button></a>
      </div>
    </div>
  </div>
</header>
  <div id="daily"></div>
<section>
  <h2><?= $translations['tip_of_day'] ?? 'Tip of the Day!' ?></h2>
  <p><?= $translations['home_best_friend'] ?? 'Your Home\'s Best Friend in Maintenance' ?></p>
  <ul class="cards1" >
  <?php
    while ($row = mysqli_fetch_assoc($result1)) {
    ?>
    <li class="card1">
      <div class="card-content">
        <div class="text">
          <h3><?php echo htmlspecialchars($row['title']); ?></h3>
          <p><?php echo htmlspecialchars($row['description']); ?></p>
        </div>
        <img src="images/tips/<?php echo htmlspecialchars($row['images']); ?>" class="card-image" alt="<?php echo htmlspecialchars($row['title']); ?>">
      </div>
    </li>
    <?php } ?>
  </ul>
</section>


<div id="hints"></div>
<div class="tips">
  <h2><?= $translations['helpful_hints'] ?? 'Delve into Our Helpful Hints' ?></h2>
  <ul class="cards">
    <?php
    while ($row = mysqli_fetch_assoc($result)) {
    ?>
      <li class="card">
        <img src="images/<?php echo htmlspecialchars($row['images']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
        <p><?php echo htmlspecialchars($row['description']); ?></p>
        <a href="article.php?article_id=<?php echo htmlspecialchars($row['article_id']); ?>"><button class="btn btn2"><?= $translations['read_now'] ?? 'Read Now' ?></button></a>
      </li>
    <?php } ?>
  </ul>
</div>

<div class="tips">
  <h2><?= $translations['eco_friendly'] ?? 'Eco-Friendly Practices' ?></h2>
  <ul class="cards">
  <li class="card">
          <h3><?= $translations['energy_conservation'] ?? 'Energy Conservation' ?></h3>
          <p><?= $translations['energy_tips'] ?? '1- Switch to energy-efficient LED light bulbs.<br>
2- Turn off lights, appliances, and electronics when not in use.<br>
4- Install solar panels or utilize solar-powered outdoor lighting to reduce reliance on fossil fuels' ?></p>
            </li>
            <li class="card">
          <h3><?= $translations['sustainable_living'] ?? 'Sustainable Living' ?></h3>
          <p><?= $translations['sustainable_tips'] ?? '1- Choose eco-friendly cleaning products that are non-toxic and biodegradable.<br>
2- Plant trees and native vegetation to enhance biodiversity and sequester carbon.<br>
3- Use reusable alternatives to disposable items such as paper towels, napkins, and utensils.' ?></p>
            </li>
            <li class="card">
          <h3><?= $translations['water_conservation'] ?? 'Water Conservation' ?></h3>
          <p><?= $translations['water_tips'] ?? '1- Fix leaks promptly to prevent water wastage.<br>
2- Turn off the tap while brushing your teeth or shaving to conserve water.<br>
3- Adjust sprinklers to avoid watering sidewalks, driveways, or other non-landscaped areas.' ?></p>
            </li>
  </ul>
</div>

<div id="seasonal"></div>
<div class="container2">
<?php
    while ($row = mysqli_fetch_assoc($result2)) {
    ?>
    <div class="left">
      <p><?php echo htmlspecialchars($row['description']); ?></p>
      <h1><?php echo htmlspecialchars($row['title']); ?></h1>
    </div>
    <div class="right">
        <p class="first"><?php echo htmlspecialchars($row['f_tip']); ?></p><br>
        <p class="second"><?php echo htmlspecialchars($row['s_tip']); ?></p>
    </div>
    <?php } ?>
    </div>


      <div class="footer">
        <span class="link">
            <a href="#top"><?= $translations['top'] ?? 'Top' ?></a>
            <a href="contact.php?<?= http_build_query(array_merge($_GET, ['lang' => $lang])) ?>"><?= $translations['contact'] ?? 'Contact' ?></a>
        </span>
      </div>
      
<script src="javascript/logout.js"></script>
</body>
</html>