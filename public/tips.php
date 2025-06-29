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

// Function to get the appropriate field based on language
function getTranslatedField($row, $field, $lang) {
    $langField = $field . '_' . $lang;
    return (!empty($row[$langField])) ? $row[$langField] : $row[$field];
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
  <meta charset="UTF-8">
  <title><?= $translations['tips'] ?? 'Maintenance Tips' ?></title>
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


<?php include_once "header.php"; ?>

<div class="hero-banner">
      <img src="app_images/pixelcut.jpeg" alt="Maintenance Tips Banner">
  <div class="hero-content">
    <div class="hero-text">
      <h1><?= $translations['maintenance_tips'] ?? 'Professional Maintenance Tips' ?></h1>
      <p><?= $translations['expert_advice'] ?? 'Expert advice to keep your home in perfect condition' ?></p>
    </div>
  </div>
</div>

   <div class="section"> 
<div class="daily-tips-section">
  <div class="section-header">
    <h2><i class="fas fa-calendar-day"></i> <?= $translations['daily_tip'] ?? 'Daily Maintenance Tip' ?></h2>
    <p><?= $translations['daily_advice'] ?? 'Your daily dose of maintenance wisdom' ?></p>
  </div>
  
  <div class="daily-tip-card">
    <?php while ($row = mysqli_fetch_assoc($result1)): ?>
    <div class="tip-content">
      <div class="tip-text">
        <h3><?= htmlspecialchars(getTranslatedField($row, 'title', $lang)) ?></h3>
        <p><?= htmlspecialchars(getTranslatedField($row, 'description', $lang)) ?></p>
      </div>
      <div class="tip-image">
        <img src="images/tips/<?= htmlspecialchars($row['images']) ?>" alt="<?= htmlspecialchars(getTranslatedField($row, 'title', $lang)) ?>">
      </div>
    </div>
    <?php endwhile; ?>
  </div>
    </div></div>

   <div class="section"> 
<div class="helpful-hints-section">
  <div class="section-header">
    <h2><i class="fas fa-lightbulb"></i> <?= $translations['helpful_hints'] ?? 'Helpful Maintenance Hints' ?></h2>
    <p><?= $translations['practical_solutions'] ?? 'Practical solutions for common household issues' ?></p>
  </div>
  
  <div class="hints-grid">
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <div class="hint-card">
      <div class="hint-image">
        <img src="images/<?= htmlspecialchars($row['images']) ?>" alt="<?= htmlspecialchars(getTranslatedField($row, 'title', $lang)) ?>">
      </div>
      <div class="hint-content">
        <h3><?= htmlspecialchars(getTranslatedField($row, 'title', $lang)) ?></h3>
        <p><?= htmlspecialchars(getTranslatedField($row, 'description', $lang)) ?></p>
        <a href="article.php?article_id=<?= htmlspecialchars($row['article_id']) ?>&lang=<?= $lang ?>" class="read-more-btn">
          <?= $translations['read_more'] ?? 'Read More' ?> <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
    </div></div>

   <div class="section"> 
<div class="eco-tips-section">
  <div class="section-header">
    <h2><i class="fas fa-leaf"></i> <?= $translations['eco_friendly'] ?? 'Eco-Friendly Maintenance' ?></h2>
    <p><?= $translations['sustainable_practices'] ?? 'Sustainable practices for a greener home' ?></p>
  </div>
  
  <div class="eco-tips-grid">
    <div class="eco-tip-card">
      <div class="eco-icon">
        <i class="fas fa-bolt"></i>
      </div>
      <h3><?= $translations['energy_conservation'] ?? 'Energy Conservation' ?></h3>
      <ul>
        <li><?= $translations['energy_tip1'] ?? 'Switch to energy-efficient LED light bulbs' ?></li>
        <li><?= $translations['energy_tip2'] ?? 'Turn off unused appliances and electronics' ?></li>
        <li><?= $translations['energy_tip3'] ?? 'Consider solar-powered outdoor lighting' ?></li>
      </ul>
    </div>
    
    <div class="eco-tip-card">
      <div class="eco-icon">
        <i class="fas fa-recycle"></i>
      </div>
      <h3><?= $translations['sustainable_living'] ?? 'Sustainable Living' ?></h3>
      <ul>
        <li><?= $translations['sustainable_tip1'] ?? 'Use eco-friendly cleaning products' ?></li>
        <li><?= $translations['sustainable_tip2'] ?? 'Plant native vegetation for biodiversity' ?></li>
        <li><?= $translations['sustainable_tip3'] ?? 'Choose reusable alternatives' ?></li>
      </ul>
    </div>
    <div class="eco-tip-card">
      <div class="eco-icon">
        <i class="fas fa-tint"></i>
      </div>
      <h3><?= $translations['water_conservation'] ?? 'Water Conservation' ?></h3>
      <ul>
        <li><?= $translations['water_tip1'] ?? 'Fix leaks promptly' ?></li>
        <li><?= $translations['water_tip2'] ?? 'Turn off taps when not in use' ?></li>
        <li><?= $translations['water_tip3'] ?? 'Adjust sprinklers to avoid waste' ?></li>
      </ul>
    </div></div>
  </div>
    </div>
<div class="section">
<div class="seasonal-tips-section">
  <div class="section-header">
    <h2><i class="fas fa-calendar-alt"></i> <?= $translations['seasonal_tips'] ?? 'Seasonal Maintenance Tips' ?></h2>
    <p><?= $translations['seasonal_advice'] ?? 'Timely advice for every season' ?></p>
  </div>
  
  <div class="seasonal-tip-container">
    <?php while ($row = mysqli_fetch_assoc($result2)): ?>
    <div class="seasonal-tip-content">
      <h3><?= htmlspecialchars(getTranslatedField($row, 'title', $lang)) ?></h3>
      <p><?= htmlspecialchars(getTranslatedField($row, 'description', $lang)) ?></p>
      
      <div class="seasonal-tip-details">
        <div class="seasonal-tip">
          <i class="fas fa-check-circle"></i>
          <p><?= htmlspecialchars(getTranslatedField($row, 'f_tip', $lang)) ?></p>
        </div>
        <div class="seasonal-tip">
          <i class="fas fa-check-circle"></i>
          <p><?= htmlspecialchars(getTranslatedField($row, 's_tip', $lang)) ?></p>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
    </div>
</div>
<footer class="main-footer">
  <div class="footer-content">
    <div class="footer-links">
      <a href="#top"><i class="fas fa-arrow-up"></i> <?= $translations['back_to_top'] ?? 'Back to Top' ?></a>
      <a href="contact.php?<?= http_build_query(array_merge($_GET, ['lang' => $lang])) ?>"><i class="fas fa-envelope"></i> <?= $translations['contact'] ?? 'Contact' ?></a>
    </div>  </div>
</footer>

<script src="javascript/logout.js"></script>
</body>
</html>