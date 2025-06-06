<?php
session_start();
include_once "php/db.php";

if (!isset($_SESSION['authenticated'])) {
    header("Location: login.html");
    exit();
}

$emergency_id = $_GET['emergency_id'];

// Prepare SQL query to fetch offers along with worker's name from users table
$sql = "
    SELECT offers.*, users.fname, users.lname, users.address, users.phone, users.email, users.unique_id
    FROM offers
    JOIN users ON offers.worker_id = users.user_id
    WHERE offers.emergency_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $emergency_id);
$stmt->execute();
$result = $stmt->get_result();

// Get emergency details for the header
$emergency_sql = "SELECT title, description FROM emergencies WHERE id = ?";
$emergency_stmt = $conn->prepare($emergency_sql);
$emergency_stmt->bind_param("i", $emergency_id);
$emergency_stmt->execute();
$emergency_result = $emergency_stmt->get_result();
$emergency = $emergency_result->fetch_assoc();
$emergency_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Offers | Fixit</title>
    <?php include_once "header.html"; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/client_emergencies.css">
    <style>
        .client-copyable {
            cursor: pointer;
            transition: color 0.2s ease;
        }
        .client-copyable:hover {
            color: var(--primary);
        }
    </style>
</head>
<body>
    <main class="client-emergencies-main">
        <!-- Page Header -->
        <div class="client-emergencies-header">
            <div class="client-header-content">
                <h1>Review Offers</h1>
                <p class="client-subtitle">Service Request: <?= htmlspecialchars($emergency['title']) ?></p>
                <div class="client-stats">
                    <div class="client-stat-item">
                        <span class="client-stat-number"><?= $result->num_rows ?></span>
                        <span class="client-stat-label">Total Offers</span>
                    </div>
                </div>
            </div>
            <a href="client_emergencies.php" class="client-new-request-btn">
                <i class="bi bi-arrow-left"></i> Back to Requests
            </a>
    </div>

        <!-- Main Content -->
        <div class="client-content-wrapper">
            <!-- Offers Grid -->
            <?php if ($result->num_rows > 0): ?>
            <div class="client-emergencies-grid">
                <?php while($row = $result->fetch_assoc()): ?>
                <div class="client-offer-card">
                    <div class="client-card-header">
                        <span class="client-request-id">#OFFER-<?= str_pad($row['id'], 5, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    <div class="client-card-body">
                        <h3><?= htmlspecialchars($row['fname']) . ' ' . htmlspecialchars($row['lname']) ?></h3>
                        <p class="client-description">
                            <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($row['address']) ?>
                        </p>
                        <div class="client-meta-info">
                            <span><i class="bi bi-envelope"></i> <?= htmlspecialchars($row['email']) ?></span>
                            <span class="client-copyable" onclick="copyToClipboard(this)">
                                <i class="bi bi-telephone"></i> <?= htmlspecialchars($row['phone']) ?>
                            </span>
                        </div>
                        <div class="client-offer-details">
                            <div class="client-offer-detail">
                                <i class="bi bi-clock"></i>
                                <span>Available: <?= htmlspecialchars($row['available_time']) ?></span>
                            </div>
                            <div class="client-offer-detail">
                                <i class="bi bi-cash"></i>
                                <span>Cost: <?= htmlspecialchars($row['cost']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="client-card-footer">
                        <a href="php/accept_offer.php?offer_id=<?= $row['id'] ?>&emergency_id=<?= $emergency_id ?>" class="client-action-btn primary">
                            <i class="bi bi-check-circle"></i> Accept Offer
                        </a>
                        <a href="chat.php?unique_id=<?= $row['unique_id'] ?>" class="client-action-btn secondary">
                            <i class="bi bi-chat-dots"></i> Chat Now
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="client-empty-state">
                <div class="client-empty-icon">
                    <i class="bi bi-info-circle"></i>
                </div>
                <h3>No Offers Available</h3>
                <p>You haven't received any offers for this request yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function copyToClipboard(element) {
            const text = element.textContent.trim();
            navigator.clipboard.writeText(text).then(() => {
                const originalText = element.innerHTML;
                element.innerHTML = '<i class="bi bi-check"></i> Copied!';
                setTimeout(() => {
                    element.innerHTML = originalText;
                }, 2000);
            });
        }

        // Simple search functionality (if you add a search box later)
        document.querySelector('.client-search-box input')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.client-offer-card').forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(searchTerm) ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>