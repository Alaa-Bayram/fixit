<?php
session_start();
include_once "php/db.php";

// Check if user is authenticated
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

// Retrieve worker's skills from session
$worker_skills = isset($_SESSION['service_id']) ? $_SESSION['service_id'] : '';

// Check if worker's skills are properly set
if (!$worker_skills) {
    die("Worker's skills not found in session.");
}

// Get the status filter, default to all
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Prepare SQL query to fetch emergency requests
$sql = "SELECT * FROM emergencies WHERE service_id = ?";

if ($status !== 'all') {
    $sql .= " AND status = ?";
}

$sql .= " ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);

// Bind parameters
if ($status !== 'all') {
    $stmt->bind_param("is", $worker_skills, $status);
} else {
    $stmt->bind_param("i", $worker_skills);
}

// Execute query
$stmt->execute();

// Get result
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Requests Dashboard | Fixit Professionals</title>
    <link rel="stylesheet" href="css/workerEmerg.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/stylebtn.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<?php include_once "worker_header2.php"; ?>

<button class="chatbot-toggler">
    <a href="worker_users.php"><i class="bi bi-chat-left" style="color: white;font-size: 24px;"></i></a>
</button>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1><i class="fas fa-exclamation-triangle"></i> <?= $translations['emergency_requests'] ?? 'Emergency Service Requests' ?></h1>
        <p class="subtitle"><?= $translations['emergency_subtitle'] ?? 'Respond promptly to urgent service requests in your specialty area.' ?></p>

        <div class="filter-controls">
            <form method="get" action="worker_emergencies.php">
                <div class="filter-group">
                    <input type="hidden" name="status" value="pending">
                     <input type="hidden" name="lang" value="<?= $lang ?>">
                    <button type="submit" class="filter-btn pending">
                        <i class="fas fa-clock"></i> <?= $translations['pending_requests'] ?? 'Pending Requests' ?>
            </button>
                </div>
            </form>
            
            <form method="get" action="worker_emergencies.php">
                <div class="filter-group">
                    <input type="hidden" name="status" value="all">
                    <button type="submit" class="filter-btn all">
                <i class="fas fa-list"></i> <?= $translations['all_requests'] ?? 'All Requests' ?>
            </button>
                </div>
            </form>
        </div>
    </div>

    <div class="requests-container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="request-card">
                    <div class="request-content">
                        <div class="request-header">
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <?php
                            $status = htmlspecialchars($row['status']);
                            $class = '';
                            if ($status === 'accepted') {
                                $class = 'status-accepted';
                            } elseif ($status === 'pending') {
                                $class = 'status-pending';
                            }
                            ?>
                            <span class="status-badge <?php echo $class; ?>">
                                <?php echo ucfirst($status); ?>
                            </span>
                        </div>
                        
                        <div class="request-details">
                        <p><i class="fas fa-align-left"></i> <strong><?= $translations['description'] ?? 'Description' ?>:</strong> <?= htmlspecialchars($row['description']); ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <strong><?= $translations['location'] ?? 'Location' ?>:</strong> <?= htmlspecialchars($row['address']); ?></p>
                        <p><i class="fas fa-phone"></i> <strong><?= $translations['contact'] ?? 'Contact' ?>:</strong> <?= htmlspecialchars($row['phone']); ?></p>
                        <p><i class="fas fa-calendar-alt"></i> <strong><?= $translations['submitted'] ?? 'Submitted' ?>:</strong> <?= date('M j, Y g:i A', strtotime($row['created_at'])); ?></p>
                    </div>
                        
                    <div class="request-actions">
                        <?php if ($row['status'] !== 'accepted'): ?>
                        <a href="worker_submit_offer.php?emergency_id=<?= $row['id']; ?>" class="action-btn offer-help">
                            <i class="fas fa-handshake"></i> <?= $translations['submit_offer'] ?? 'Submit Offer' ?>
                        </a>
                    <?php endif; ?>
                    </div>
                    </div>
                    
                    <div class="request-image">
                        <?php
                        $image_path = 'php/' . $row['image'];
                        $default_image = 'php/images/noimage.jpg';
                        
                        if (file_exists($image_path) && !empty($row['image'])) {
                            echo '<img src="' . $image_path . '" alt="Emergency Image">';
                        } else {
                            echo '<img src="' . $default_image . '" alt="No image available">';
                        }
                        ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-requests">
            <i class="fas fa-check-circle"></i>
            <h3><?= $translations['no_requests'] ?? 'No Emergency Requests Available' ?></h3>
            <p><?= $translations['no_requests_desc1'] ?? 'There are currently no emergency service requests matching your specialty.' ?></p>
            <p><?= $translations['no_requests_desc2'] ?? 'Check back later or update your availability settings.' ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
<script>
    function changeLang(lang) {
        const url = new URL(window.location.href);
        url.searchParams.set('lang', lang);
        window.location.href = url.toString();
    }
</script>

</html>