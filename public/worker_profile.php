<?php 
  session_start();
  include_once "php/db.php";
  if(!isset($_SESSION['unique_id'])){
    header("location: login.html");
    exit(); // Important to stop script execution after redirection
  }

  $lang = 'en'; 

if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
}

$lang_file = "lang/{$lang}.php";
if (file_exists($lang_file)) {
    $translations = include $lang_file;
} else {
    $translations = include "lang/en.php";
}

  $unique_id = $_SESSION['unique_id'];

  // Fetch user data
  $query = "SELECT * FROM users WHERE unique_id = '$unique_id'";
  $result = mysqli_query($conn, $query);

  // Debugging: Check if query was successful
  if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
  }

  $user = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
<?php include_once "worker_header.php"; ?>

<div class="profile-container">
    <div class="profile-header">
        <h1><?= $translations['profile'] ?? 'Profile' ?></h1>
        <p>Welcome, <?php echo htmlspecialchars($user['fname']); ?>!</p>
    </div>

    <div class="profile-card">
        <div class="profile-sidebar">
            <div class="profile-picture">
                <img src="php/images/<?php echo htmlspecialchars($user['img']); ?>" alt="Profile Picture">
            </div>
            <div class="profile-actions">
                <a href="worker_edit_prof.php" class="edit-btn"><?= $translations['edit_profile'] ?? 'Edit Profile' ?></a>
            </div>
        </div>

        <div class="profile-content">
            <div class="profile-section">
                <h2><?= $translations['personal_info'] ?? 'Personal Information' ?></h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label"><?= $translations['full_name'] ?? 'Full Name' ?>:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['fname']) . ' ' . htmlspecialchars($user['lname']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><?= $translations['email'] ?? 'Email' ?>:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><?= $translations['address'] ?? 'Address' ?>:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['address']); ?></span>
                    </div>
                    <div class="info-item">
                       <span class="info-label"><?= $translations['region'] ?? 'Region' ?>:</span>
                       <span class="info-value"><?= htmlspecialchars($user['region']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><?= $translations['phone'] ?? 'Phone' ?>:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['phone']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    
</body>
</html>