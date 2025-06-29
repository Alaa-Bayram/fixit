<?php
session_start();
include_once "php/db.php";

if (!isset($_SESSION['authenticated'])) {
    header("Location: login.html");
    exit();
}

$emergency_id = $_GET['emergency_id'];

// Prepare SQL query to fetch offers along with worker's details
$sql = "
    SELECT offers.*, users.fname, users.lname, users.address, users.phone, users.email, users.unique_id,
           emergencies.status as emergency_status
    FROM offers
    JOIN users ON offers.worker_id = users.user_id
    JOIN emergencies ON offers.emergency_id = emergencies.id
    WHERE offers.emergency_id = ?
    ORDER BY offers.date DESC
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

$visible_offers = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Offers</title>
    <?php include_once "header.php"; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/client_emergencies.css">
    <style>
        .client-copyable {
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .client-copyable:hover {
            color: var(--primary);
            text-decoration: underline;
        }
        .offer-detail-value {
            font-weight: 600;
            color: #2d3748;
        }
        .professional-badge {
            background: #f0fdf4;
            color: #166534;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
    </style>
</head>
<body>
    <main class="client-emergencies-main">
        <!-- Page Header -->
        <div class="client-emergencies-header">
            <div class="client-header-content">
                <h1><i class="fas fa-handshake"></i> Service Offers Received</h1>
                <p class="client-subtitle">For: <strong><?= htmlspecialchars($emergency['title']) ?></strong></p>
                <div class="client-stats">
                    <div class="client-stat-item">
                        <span class="client-stat-number"><?= $result->num_rows ?></span>
                        <span class="client-stat-label">Professional Offers</span>
                    </div>
                    <div class="client-stat-item">
                        <span class="client-stat-number">
                            <?= date('M j, Y') ?>
                        </span>
                        <span class="client-stat-label">Last Updated</span>
                    </div>
                </div>
            </div>
            <a href="client_emergencies.php" class="client-new-request-btn">
                <i class="fas fa-arrow-left"></i> Back to Requests
            </a>
        </div>

        <!-- Main Content -->
        <div class="client-content-wrapper">
            <!-- Offers Grid -->
<div class="client-emergencies-grid">
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <?php 
            $emergency_status = isset($row['emergency_status']) ? $row['emergency_status'] : '';
            if ($emergency_status !== 'accepted'): 
                $visible_offers++;
            ?>
            <div class="client-offer-card">
            <div class="client-card-header">
                <span class="client-request-id">Offer #<?= str_pad($row['id'], 5, '0', STR_PAD_LEFT) ?></span>
                <span class="professional-badge">
                    <i class="fas fa-check-circle"></i> Verified Professional
                </span>
            </div>
            <div class="client-card-body">
                <div class="professional-info">
                    <h3><?= htmlspecialchars($row['fname']) . ' ' . htmlspecialchars($row['lname']) ?></h3>
                    <p class="client-description">
                        <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($row['address']) ?>
                    </p>
                </div>
                
                <div class="contact-info">
                    <div class="client-meta-info">
                        <span><i class="fas fa-envelope"></i> <?= htmlspecialchars($row['email']) ?></span>
                        <span class="client-copyable" onclick="copyToClipboard(this)">
                            <i class="fas fa-phone"></i> <?= htmlspecialchars($row['phone']) ?>
                        </span>
                    </div>
                </div>
                
                <div class="offer-details">
                    <div class="client-offer-detail">
                        <i class="fas fa-clock"></i>
                        <span>Available: <span class="offer-detail-value"><?= htmlspecialchars($row['available_time']) ?></span></span>
                    </div>
                    <div class="client-offer-detail">
                        <i class="fas fa-dollar-sign"></i>
                        <span>Estimated Cost: <span class="offer-detail-value">$<?= number_format(htmlspecialchars($row['cost']), 2) ?></span></span>
                    </div>
                </div>
            </div>
            <div class="client-card-footer">
                <a href="php/accept_offer.php?offer_id=<?= $row['id'] ?>&emergency_id=<?= $emergency_id ?>" class="client-action-btn primary">
                    <i class="fas fa-check-circle"></i> Accept Offer
                </a>
                <a href="chatting/chat.php?unique_id=<?= $row['unique_id'] ?>" class="client-action-btn secondary">
                    <i class="fas fa-comments"></i> Message
                </a>
            </div>
        </div>
            <?php endif; ?>
        <?php endwhile; ?>
        
        <?php if ($visible_offers === 0): ?>
            <div class="client-empty-state">
                <div class="client-empty-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3>Request Already Accepted</h3>
                <p>This emergency request has already been accepted and is no longer available for new offers.</p>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="client-empty-state">
            <div class="client-empty-icon">
                <i class="fas fa-inbox"></i>
            </div>
            <h3>No Offers Received Yet</h3>
            <p>Your service request hasn't received any offers yet. Check back later or consider adjusting your request details.</p>
        </div>
    <?php endif; ?>
</div>
    </main>

    <script>
        function copyToClipboard(element) {
            const text = element.textContent.trim();
            navigator.clipboard.writeText(text).then(() => {
                const originalHTML = element.innerHTML;
                element.innerHTML = '<i class="fas fa-check"></i> Copied!';
                setTimeout(() => {
                    element.innerHTML = originalHTML;
                }, 2000);
            });
        }
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>