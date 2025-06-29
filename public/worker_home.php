<?php 
include_once "php/fetch_worker_data.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
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
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $translations['dashboard_title'] ?? 'Dashboard - Fixit Worker Portal' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/workerHome.css">
</head>
<body>
    <?php include_once "worker_header2.php"; ?>
    
    <div class="container">
        <div class="welcome-section">
            <h1><?= $translations['welcome'] ?? 'Welcome' ?>, <?= htmlspecialchars($user['fname']); ?>!</h1>
            <p class="subtitle"><?= $translations['dashboard_subtitle'] ?? 'Your professional dashboard for managing jobs, clients, and earnings.' ?></p>
        </div>

        <div class="dashboard-grid">
            
            <!-- Profile Summary Card -->
            <div class="dashboard-card profile-summary">
                <div class="card-header">
                    <h2><?= $translations['your_profile'] ?? 'Your Profile' ?></h2>
                    <button id="toggleDetailsBtn" class="toggle-btn">
                        <i class="bi bi-chevron-down"></i> <?= $translations['details'] ?? 'Details' ?>
                    </button>
                </div>
                <div id="detailsBox" class="card-content hidden">
                    <div class="profile-detail">
                        <span class="detail-label"><?= $translations['id'] ?? 'ID' ?>:</span>
                        <span class="detail-value"><?= htmlspecialchars($user['unique_id']); ?></span>
                    </div>
                    <div class="profile-detail">
                        <span class="detail-label"><?= $translations['skills'] ?? 'Skills' ?>:</span>
                        <span class="detail-value"><?= htmlspecialchars($user['title']); ?></span>
                    </div>
                    <div class="profile-detail">
                        <span class="detail-label"><?= $translations['region'] ?? 'Region' ?>:</span>
                        <span class="detail-value"><?= htmlspecialchars($user['region']); ?></span>
                    </div>
                    <div class="profile-detail">
                        <span class="detail-label"><?= $translations['phone'] ?? 'Phone' ?>:</span>
                        <span class="detail-value"><?= htmlspecialchars($user['phone']); ?></span>
                    </div>
                    <a href="worker_profile.php?lang=<?= $lang ?>" class="btn profile-btn">
                        <i class="bi bi-person-circle"></i> <?= $translations['view_full_profile'] ?? 'View Full Profile' ?>
                    </a>
                </div>
            </div>

            <!-- Activity Summary Card -->
            <div class="dashboard-card activity-summary">
                <div class="card-header">
                    <h2><?= $translations['activity_summary'] ?? 'Activity Summary' ?></h2>
                </div>
                <div class="card-content">
                    <div class="activity-item emergency">
                        <i class="bi bi-exclamation-triangle"></i>
                        <div class="activity-text">
                            <span class="activity-count"><?= $nb_emergencies_pending; ?></span>
                            <span class="activity-label"><?= $translations['emergency_cases'] ?? 'Emergency Cases' ?></span>
                        </div>
                        <a href="worker_emergencies.php?status=pending&lang=<?= $lang ?>" class="activity-link">
                            <?= $translations['view'] ?? 'View' ?>
                        </a>
                    </div>
                    
                    <div class="activity-item appointment">
                        <i class="bi bi-calendar-check"></i>
                        <div class="activity-text">
                            <span class="activity-count"><?= $pendingRequests; ?></span>
                            <span class="activity-label"><?= $translations['pending_requests'] ?? 'Pending Requests' ?></span>
                        </div>
                    </div>
                    
                    <div class="activity-item next-appointment">
                        <i class="bi bi-clock-history"></i>
                        <div class="activity-text">
                            <span class="activity-time"><?= $nextAppointment; ?></span>
                            <span class="activity-label"><?= $translations['next_appointment'] ?? 'Next Appointment' ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Earnings Card -->
            <div class="dashboard-card earnings-summary">
                <div class="card-header">
                    <h2><?= $translations['earnings'] ?? 'Earnings' ?> - <?= date('F Y', mktime(0, 0, 0, $currentMonth, 1, $currentYear)); ?></h2>
                    <div class="month-navigation">
                        <a href="?month=<?= date('m', strtotime('-1 month', mktime(0, 0, 0, $currentMonth, 1, $currentYear))); ?>&year=<?= date('Y', strtotime('-1 month', mktime(0, 0, 0, $currentMonth, 1, $currentYear))); ?>&lang=<?= $lang ?>" class="nav-arrow">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                        <a href="?month=<?= date('m', strtotime('+1 month', mktime(0, 0, 0, $currentMonth, 1, $currentYear))); ?>&year=<?= date('Y', strtotime('+1 month', mktime(0, 0, 0, $currentMonth, 1, $currentYear))); ?>&lang=<?= $lang ?>" class="nav-arrow">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-content">
                    <div class="earning-item">
                        <span class="earning-label"><?= $translations['appointments'] ?? 'Appointments' ?>:</span>
                        <span class="earning-amount">$<?= number_format($user['total_appointment_earnings'], 2); ?></span>
                    </div>
                    <div class="earning-item">
                        <span class="earning-label"><?= $translations['emergencies'] ?? 'Emergencies' ?>:</span>
                        <span class="earning-amount">$<?= number_format($totalEmergencyEarnings, 2); ?></span>
                    </div>
                    <div class="earning-total">
                        <span class="earning-label"><?= $translations['total_earnings'] ?? 'Total Earnings' ?>:</span>
                        <span class="earning-amount">$<?= number_format($totalEarnings, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Button -->
    <button class="chatbot-toggler">
        <a href="worker_users.php?lang=<?= $lang ?>" aria-label="<?= $translations['chat_with_clients'] ?? 'Chat with clients' ?>">
            <i class="bi bi-chat-left-text"></i>
        </a>
    </button>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#toggleDetailsBtn').click(function() {
                $('#detailsBox').toggleClass('hidden');
                $(this).find('i').toggleClass('bi-chevron-down bi-chevron-up');
            });
        });
    </script>
</body>
</html>
