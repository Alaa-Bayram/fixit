<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', 'Please log in to access the dashboard');
}

// Fetch worker requests where usertype is 'worker' in descending order of user_id
$query = "SELECT * FROM users WHERE usertype='worker' AND access_status!='approved' ORDER BY user_id DESC";
$stmt = $pdo->query($query);

if (!$stmt) {
    error_log('Database Error: ' . implode(' ', $pdo->errorInfo()));
    $rows = [];
} else {
    $rows = $stmt->fetchAll();
}

// Handle POST requests for approval actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $request_id = sanitize_input($_POST['request_id']);
    $action = sanitize_input($_POST['action']);
    $worker_email = sanitize_input($_POST['worker_email']);
    $worker_fname = sanitize_input($_POST['worker_fname']);
    $worker_lname = sanitize_input($_POST['worker_lname']);
    
    if ($action == 'approve_pending') {
        // Update status to 'in progress'
        $stmt = $pdo->prepare("UPDATE users SET access_status='in progress' WHERE unique_id=:request_id");
        $stmt->bindParam(':request_id', $request_id);
        
        if ($stmt->execute()) {
            // Send payment information email
            $subject = "Payment Instructions for FixItApp Subscription";
            $message = "Please transfer the subscription fees via OMT or Western Union. Use the following details: Full name of the admin: FixItApp Admin. After completing the transaction, send the MTCN to this email.";
            // Include email sending functionality
            require_once 'includes/send_email.php';
            sendEmail($worker_email, $worker_fname, $worker_lname, $subject, $message);
            set_flash_message('success', 'Worker status updated and payment instructions sent');
        } else {
            set_flash_message('error', 'Failed to update worker status');
        }
    } elseif ($action == 'approve_in_progress') {
        // Update status to 'approved'
        $stmt = $pdo->prepare("UPDATE users SET access_status='approved' WHERE unique_id=:request_id");
        $stmt->bindParam(':request_id', $request_id);
        
        if ($stmt->execute()) {
            // Send login details email
            $subject = "Welcome to FixItApp";
            $message = "Your subscription is complete. You can now log in to the app using the following password: fixit@2025";
            require_once 'includes/send_email.php';
            sendEmail($worker_email, $worker_fname, $worker_lname, $subject, $message);
            set_flash_message('success', 'Worker approved and login details sent');
        } else {
            set_flash_message('error', 'Failed to approve worker');
        }
    } elseif ($action == 'approve_disabled') {
        // Update status to 'approved'
        $stmt = $pdo->prepare("UPDATE users SET access_status='approved' WHERE unique_id=:request_id");
        $stmt->bindParam(':request_id', $request_id);
        
        if ($stmt->execute()) {
            // Send notification email
            $subject = "Your account has been enabled";
            $message = "Your account has been enabled. You can now log in to the app.";
            require_once 'includes/send_email.php';
            sendEmail($worker_email, $worker_fname, $worker_lname, $subject, $message);
            set_flash_message('success', 'Worker account enabled');
        } else {
            set_flash_message('error', 'Failed to enable worker account');
        }
    }
    
    // Redirect to refresh the page after action
    header("Location: workers.php");
    exit();
}

// Include header
include_once 'includes/header.php';
?>

<div class="home-content">
    <div class="sales-boxes">
        <div class="recent-sales box">
            <div class="title">Workers Requests</div>
            <?php if (has_flash_message('success')): ?>
                <div class="alert alert-success"><?php echo get_flash_message('success'); ?></div>
            <?php endif; ?>
            <?php if (has_flash_message('error')): ?>
                <div class="alert alert-danger"><?php echo get_flash_message('error'); ?></div>
            <?php endif; ?>
            
            <div class="sales-details">
                <?php if (empty($rows)): ?>
                    <div class="no-data" style="padding: 20px; text-align: center; font-style: italic; color: #666;">
                        No worker requests available at the moment.
                    </div>
                <?php else: ?>
                    <ul class="worker-requests-list">
                        <?php foreach ($rows as $row): ?>
                            <li class="worker-request-item">
                                <div class="worker-profile">
                                    <img src="../public/php/images/<?php echo h($row['img']); ?>" alt="Worker Image">
                                    <div class="worker-info">
                                        <h3 class="worker-name"><?php echo h($row['fname']) . ' ' . h($row['lname']); ?></h3>
                                        <p><strong>Program:</strong> <?php echo h($row['fees']); ?></p>
                                        <p><strong>Email:</strong> <?php echo h($row['email']); ?></p>
                                        <p><strong>Phone:</strong> <?php echo h($row['phone']); ?></p>
                                        <p><strong>Skills:</strong> <?php echo h($row['skills']); ?></p>
                                        <p><strong>Experience:</strong> <?php echo h($row['experience']); ?></p>
                                        <p><strong>Region:</strong> <?php echo h($row['region']); ?></p>
                                        <p><strong>Address:</strong> <?php echo h($row['address']); ?></p>
                                        
                                        <?php if (!empty($row['pdf'])): ?>
                                            <a href="cv/<?php echo h($row['pdf']); ?>" class="btn btn-download" download>
                                                <i class='bx bx-download'></i> Download CV
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="worker-actions">
                                    <form method="POST">
                                        <input type="hidden" name="request_id" value="<?php echo h($row['unique_id']); ?>">
                                        <input type="hidden" name="worker_email" value="<?php echo h($row['email']); ?>">
                                        <input type="hidden" name="worker_fname" value="<?php echo h($row['fname']); ?>">
                                        <input type="hidden" name="worker_lname" value="<?php echo h($row['lname']); ?>">
                                        
                                        <?php if ($row['access_status'] == 'pending'): ?>
                                            <button type="submit" name="action" value="approve_pending" class="btn status-pending">
                                                Approve & Send Payment Info
                                            </button>
                                        <?php elseif ($row['access_status'] == 'in progress'): ?>
                                            <button type="submit" name="action" value="approve_in_progress" class="btn status-in-progress">
                                                Approve & Send Login Details
                                            </button>
                                        <?php elseif ($row['access_status'] == 'disabled'): ?>
                                            <button type="submit" name="action" value="approve_disabled" class="btn status-disabled">
                                                Enable Account
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.worker-requests-list {
    list-style: none;
    padding: 0;
}

.worker-request-item {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: flex-start;
    padding: 20px;
    border-bottom: 1px solid var(--gray-light);
    margin-bottom: 10px;
}

.worker-profile {
    display: flex;
    flex: 1;
    min-width: 300px;
}

.worker-profile img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: var(--radius-md);
    margin-right: 20px;
}

.worker-info {
    flex: 1;
}

.worker-info h3 {
    color: var(--secondary);
    margin-bottom: 10px;
}

.worker-info p {
    margin: 5px 0;
    font-size: 14px;
    color: var(--dark);
}

.worker-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 20px;
}

.btn {
    padding: 8px 16px;
    border-radius: var(--radius-sm);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.btn i {
    margin-right: 5px;
}

.btn-download {
    background-color: var(--dark);
    color: white;
    text-decoration: none;
}

.btn-download:hover {
    background-color: var(--primary-dark);
}

.status-pending {
    background-color: var(--warning);
    color: white;
}

.status-in-progress {
    background-color: var(--secondary);
    color: white;
}

.status-disabled {
    background-color: var(--gray);
    color: white;
}

.status-pending:hover, .status-in-progress:hover, .status-disabled:hover {
    filter: brightness(1.1);
    transform: translateY(-2px);
}

.alert {
    padding: 10px 15px;
    margin-bottom: 20px;
    border-radius: var(--radius-sm);
}

.alert-success {
    background-color: rgba(46, 204, 113, 0.1);
    color: var(--success);
    border-left: 4px solid var(--success);
}

.alert-danger {
    background-color: rgba(231, 76, 60, 0.1);
    color: var(--danger);
    border-left: 4px solid var(--danger);
}

@media (max-width: 768px) {
    .worker-profile {
        flex-direction: column;
    }
    
    .worker-profile img {
        margin-right: 0;
        margin-bottom: 15px;
    }
    
    .worker-actions {
        width: 100%;
    }
}
</style>

<?php include_once 'includes/footer.php'; ?>