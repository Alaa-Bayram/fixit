<?php
session_start();
include_once "php/db.php";

if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    header("Location: login.html");
    exit();
}

$lang = 'en'; // Default

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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $emergency_id = $_GET['emergency_id'];
    
    // Get emergency details for better context
    $stmt = $conn->prepare("SELECT title FROM emergencies WHERE id = ?");
    $stmt->bind_param("i", $emergency_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $emergency = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Service Offer | Fixit Professionals</title>
    <link rel="stylesheet" href="css/submit_offer.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php include_once "worker_header2.php"; ?>
</head>
<body>
    <div class="offer-container">
        <div class="offer-card">
            <div class="offer-header">
                <h1><i class="fas fa-handshake"></i> Submit Service Offer</h1>
                <?php if(isset($emergency['title'])): ?>
                    <p class="service-context">For: <span><?php echo htmlspecialchars($emergency['title']); ?></span></p>
                <?php endif; ?>
            </div>
            
            <form action="php/save_offer.php" method="POST" class="offer-form">
                <input type="hidden" name="emergency_id" value="<?php echo $emergency_id; ?>">
                
                <div class="form-group">
                    <label for="available_time">
                        <i class="far fa-clock"></i> When can you arrive?
                    </label>
                    <div class="input-with-icon">
                        <input type="time" name="available_time" id="available_time" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="cost">
                        <i class="fas fa-dollar-sign"></i> Estimated Cost (USD)
                    </label>
                    <div class="input-with-icon">
                        <span class="currency">$</span>
                        <input type="number" name="cost" id="cost" min="0" step="0.01" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="notes">
                        <i class="far fa-edit"></i> Additional Notes (Optional)
                    </label>
                    <textarea name="notes" id="notes" rows="3" placeholder="Any special instructions or details about your service..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-paper-plane"></i> Submit Offer
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>