<?php
session_start();
include_once "php/db.php";

// Language selection
$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en');
$_SESSION['lang'] = $lang;
$lang_file = "lang/$lang.php";
$translations = file_exists($lang_file) ? include($lang_file) : include("lang/en.php");

// Check authentication
if (!isset($_SESSION['authenticated'])) {
    header("Location: login.html?lang=$lang");
    exit();
}

// Get client emergencies
$client_id = $_SESSION['user_id'];
$sql = "SELECT id, title, description, status, created_at FROM emergencies WHERE client_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$emergencies = $result->fetch_all(MYSQLI_ASSOC);
$hasEmergencies = count($emergencies) > 0;

// Close connections
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $translations['my_emergencies_title'] ?? 'My Emergency Requests' ?> | Fixit</title>
    <?php include_once "header.php"; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/client_emergencies.css">
</head>
<body>
    <main class="client-emergencies-main">
        <!-- Page Header -->
        <div class="client-emergencies-header">
            <div class="client-header-content">
                <h1><?= $translations['your_emergency_requests'] ?? 'Your Emergency Requests' ?></h1>
                <p class="client-subtitle"><?= $translations['manage_requests_subtitle'] ?? 'Manage your submitted service requests and review offers from professionals' ?></p>
                <div class="client-stats">
                    <div class="client-stat-item">
                        <span class="client-stat-number"><?= count($emergencies) ?></span>
                        <span class="client-stat-label"><?= $translations['total_requests'] ?? 'Total Requests' ?></span>
                    </div>
                    <div class="client-stat-item">
                        <span class="client-stat-number"><?= array_reduce($emergencies, fn($carry, $item) => $carry + ($item['status'] === 'pending' ? 1 : 0), 0) ?></span>
                        <span class="client-stat-label"><?= $translations['pending'] ?? 'Pending' ?></span>
                    </div>
                    <div class="client-stat-item">
                        <span class="client-stat-number"><?= array_reduce($emergencies, fn($carry, $item) => $carry + ($item['status'] === 'accepted' ? 1 : 0), 0) ?></span>
                        <span class="client-stat-label"><?= $translations['completed'] ?? 'Completed' ?></span>
                    </div>
                </div>
            </div>
            <a href="emergency.php?lang=<?= $lang ?>" class="client-new-request-btn">
                <i class="bi bi-plus-lg"></i> <?= $translations['new_request'] ?? 'New Request' ?>
            </a>
        </div>

        <!-- Main Content -->
        <div class="client-content-wrapper">
            <!-- Filter/Search Bar -->
            <div class="client-action-bar">
                <div class="client-search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" placeholder="<?= $translations['search_requests'] ?? 'Search requests...' ?>">
                </div>
                <div class="client-filter-group">
                    <button class="client-filter-btn active" data-status="all"><?= $translations['all'] ?? 'All' ?></button>
                    <button class="client-filter-btn" data-status="pending"><?= $translations['pending'] ?? 'Pending' ?></button>
                    <button class="client-filter-btn" data-status="accepted"><?= $translations['completed'] ?? 'Completed' ?></button>
                </div>
            </div>

            <!-- Emergencies Grid -->
            <?php if ($hasEmergencies): ?>
            <div class="client-emergencies-grid">
                <?php foreach ($emergencies as $emergency): ?>
                <div class="client-emergency-card" data-status="<?= $emergency['status'] ?>">
                    <div class="client-card-header">
                        <span class="client-request-id">#REQ-<?= str_pad($emergency['id'], 5, '0', STR_PAD_LEFT) ?></span>
                        <span class="client-status-badge <?= $emergency['status'] === 'pending' ? 'status-pending' : 'status-accepted' ?>">
                            <?= $emergency['status'] === 'pending' ? ($translations['pending'] ?? 'Pending') : ($translations['completed'] ?? 'Completed') ?>
                        </span>
                    </div>
                    <div class="client-card-body">
                        <h3><?= htmlspecialchars($emergency['title']) ?></h3>
                        <p class="client-description"><?= htmlspecialchars($emergency['description']) ?></p>
                        <div class="client-meta-info">
                            <span><i class="bi bi-calendar"></i> <?= date('M j, Y', strtotime($emergency['created_at'])) ?></span>
                            <span><i class="bi bi-clock"></i> <?= date('g:i A', strtotime($emergency['created_at'])) ?></span>
                        </div>
                    </div>
                    <div class="client-card-footer">
                        <a href="review_offers.php?emergency_id=<?= $emergency['id'] ?>&lang=<?= $lang ?>" class="client-action-btn primary">
                            <i class="bi bi-eye"></i> <?= $translations['review_offers'] ?? 'Review Offers' ?>
                        </a>
                        <form method="POST" action="php/delete_emergency.php?lang=<?= $lang ?>" class="client-delete-form">
                            <input type="hidden" name="emergency_id" value="<?= $emergency['id'] ?>">
                            <button type="submit" class="client-action-btn danger" onclick="return confirm('<?= $translations['confirm_delete'] ?? 'Are you sure you want to delete this request?' ?>')">
                                <i class="bi bi-trash"></i> <?= $translations['delete'] ?? 'Delete' ?>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="client-empty-state">
                <div class="client-empty-icon">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
                <h3><?= $translations['no_requests_found'] ?? 'No Emergency Requests Found' ?></h3>
                <p><?= $translations['no_requests_message'] ?? 'You haven\'t submitted any emergency service requests yet.' ?></p>
                <a href="emergency.php?lang=<?= $lang ?>" class="client-new-request-btn">
                    <i class="bi bi-plus-lg"></i> <?= $translations['create_first_request'] ?? 'Create Your First Request' ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Simple search functionality
        document.querySelector('.client-search-box input').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.client-emergency-card').forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(searchTerm) ? 'block' : 'none';
            });
        });

        // Filter functionality
        document.querySelectorAll('.client-filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Update active button
                document.querySelectorAll('.client-filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const statusFilter = this.dataset.status;
                
                // Filter cards
                document.querySelectorAll('.client-emergency-card').forEach(card => {
                    const cardStatus = card.dataset.status;
                    
                    if (statusFilter === 'all') {
                        card.style.display = 'block';
                    } else {
                        card.style.display = cardStatus === statusFilter ? 'block' : 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>