<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'lang.php'; // Include language file

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
            set_flash_message('success', $trans['worker_status_updated_payment_sent'] ?? 'Worker status updated and payment instructions sent');
        } else {
            set_flash_message('error', $trans['failed_update_worker_status'] ?? 'Failed to update worker status');
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
            set_flash_message('success', $trans['worker_approved_login_sent'] ?? 'Worker approved and login details sent');
        } else {
            set_flash_message('error', $trans['failed_approve_worker'] ?? 'Failed to approve worker');
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
            set_flash_message('success', $trans['worker_account_enabled'] ?? 'Worker account enabled');
        } else {
            set_flash_message('error', $trans['failed_enable_worker'] ?? 'Failed to enable worker account');
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
            <div class="title"><?php echo $trans['workers_requests']; ?></div>
            <?php if (has_flash_message('success')): ?>
                <div class="alert alert-success"><?php echo get_flash_message('success'); ?></div>
            <?php endif; ?>
            <?php if (has_flash_message('error')): ?>
                <div class="alert alert-danger"><?php echo get_flash_message('error'); ?></div>
            <?php endif; ?>
            
            <div class="sales-details">
                <?php if (empty($rows)): ?>
                    <div class="no-data" style="padding: 20px; text-align: center; font-style: italic; color: #666;">
                        <?php echo $trans['no_worker_requests'] ?? 'No worker requests available at the moment.'; ?>
                    </div>
                <?php else: ?>
                    <ul class="worker-requests-list">
                        <?php foreach ($rows as $row): ?>
                            <li class="worker-request-item">
                                <div class="worker-profile">
                                    <img src="../public/php/images/<?php echo h($row['img']); ?>" alt="<?php echo $trans['worker_image'] ?? 'Worker Image'; ?>">
                                    <div class="worker-info">
                                        <h3 class="worker-name"><?php echo h($row['fname']) . ' ' . h($row['lname']); ?></h3>
                                        <p><strong><?php echo $trans['program'] ?? 'Program'; ?>:</strong> <?php echo h($row['fees']); ?></p>
                                        <p><strong><?php echo $trans['email'] ?? 'Email'; ?>:</strong> <?php echo h($row['email']); ?></p>
                                        <p><strong><?php echo $trans['phone'] ?? 'Phone'; ?>:</strong> <?php echo h($row['phone']); ?></p>
                                        <p><strong><?php echo $trans['skills'] ?? 'Skills'; ?>:</strong> <?php echo h($row['skills']); ?></p>
                                        <p><strong><?php echo $trans['experience'] ?? 'Experience'; ?>:</strong> <?php echo h($row['experience']); ?></p>
                                        <p><strong><?php echo $trans['region'] ?? 'Region'; ?>:</strong> <?php echo h($row['region']); ?></p>
                                        <p><strong><?php echo $trans['address'] ?? 'Address'; ?>:</strong> <?php echo h($row['address']); ?></p>
                                        
                                        <?php if (!empty($row['pdf'])): ?>
                                            <a href="cv/<?php echo h($row['pdf']); ?>" class="btn btn-download" download>
                                                <i class='bx bx-download'></i> <?php echo $trans['download_cv'] ?? 'Download CV'; ?>
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
                                                <?php echo $trans['approve_send_payment'] ?? 'Approve & Send Payment Info'; ?>
                                            </button>
                                        <?php elseif ($row['access_status'] == 'in progress'): ?>
                                            <button type="submit" name="action" value="approve_in_progress" class="btn status-in-progress">
                                                <?php echo $trans['approve_send_login'] ?? 'Approve & Send Login Details'; ?>
                                            </button>
                                        <?php elseif ($row['access_status'] == 'disabled'): ?>
                                            <button type="submit" name="action" value="approve_disabled" class="btn status-disabled">
                                                <?php echo $trans['enable_account'] ?? 'Enable Account'; ?>
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
.sales-boxes {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 0;
}

.worker-requests-list {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 40px;
    list-style: none;
    padding: 0;
}

.worker-request-item {
    display: flex;
    flex-direction: column;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    height: 100%;
    width: 380px;
}

.worker-profile {
    display: flex;
    flex: 1;
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
    margin-top: 15px;
    border-top: 1px solid #eee;
    padding-top: 15px;
    align-items: center;
}

.action-buttons {
    display: flex;
    gap: 10px;
    margin-top: 10px;
    justify-content: center;
    width: 100%;
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
    width: auto;
    display: inline-block;
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
    grid-column: 1 / -1;
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

@media (max-width: 1200px) {
    .worker-requests-list {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .worker-requests-list {
        grid-template-columns: 1fr;
    }
    
    .worker-profile {
        flex-direction: column;
    }
    
    .worker-profile img {
        margin-right: 0;
        margin-bottom: 15px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
<?php if ($lang === 'ar'): ?>
/* Arabic RTL Styling - Properly Fixed */
body {
    direction: rtl;
    text-align: right;
    font-family: 'Tajawal', 'Arial', sans-serif;
}

/* Sidebar Positioning - ON THE RIGHT SIDE */
.sidebar {
    left: auto;
    right: 0;
    transition: all 0.5s ease;
}

/* Main Content Area - positioned from the right */
.home-section {
    position: relative;
    left: auto;
    right: 260px;
    width: calc(100% - 260px);
    transition: all 0.5s ease;
}

/* When Sidebar is Closed/Minimized */
.sidebar.close {
    width: 78px;
}

.sidebar.close ~ .home-section {
    right: 78px;
    left: auto;
    width: calc(100% - 78px);
}

/* Mobile view when sidebar is toggled */
@media (max-width: 1090px) {
    .sidebar {
        right: -260px;
        left: auto;
    }
    
    .sidebar.active {
        right: 0;
    }
    
    .home-section {
        right: 0;
        left: auto;
        width: 100%;
    }
    
    .sidebar.active ~ .home-section {
        right: 260px;
        left: auto;
        width: calc(100% - 260px);
    }
    
    .sidebar.close.active ~ .home-section {
        right: 78px;
        left: auto;
        width: calc(100% - 78px);
    }
}

/* Fix service items layout */
.top-sales-details li {
    padding: 10px 0 10px 20px;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    flex-direction: row-reverse;
}

/* Service info alignment */
.service-info {
    margin-left: 15px;
    margin-right: 0;
    text-align: right;
    flex: 1;
}

/* Form field alignment */
.service-form .field {
    text-align: right;
}

/* Input fields styling */
.service-form input[type="text"] {
    text-align: right;
    padding: 12px 15px;
}

/* Fix file upload label alignment */
.file-upload-wrapper {
    direction: rtl;
}

.file-upload-label {
    justify-content: center;
    flex-direction: row-reverse;
}

.file-upload-label i {
    margin-right: 0;
    margin-left: 8px;
}

/* Box layout adjustments */
.box {
    text-align: right;
}

.box-topic, .number, .indicator {
    text-align: right;
}

/* Icon positioning in boxes - move to left side */
.box i {
    left: 15px;
    right: auto;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .top-sales-details li {
        flex-direction: column;
        align-items: flex-end;
    }
    
    .service-info {
        margin-left: 0;
        margin-top: 10px;
        text-align: right;
    }
}

@media (max-width: 480px) {
    .sidebar {
        right: -100%;
        left: auto;
    }
    
    .sidebar.active {
        right: 0;
        width: 260px;
    }
    
    .sidebar.close.active {
        width: 78px;
    }
    
    .home-section {
        right: 0;
        left: auto;
        width: 100%;
    }
    
    .sidebar.active ~ .home-section {
        right: 260px;
        left: auto;
        width: calc(100% - 260px);
    }
    
    .sidebar.close.active ~ .home-section {
        right: 78px;
        left: auto;
        width: calc(100% - 78px);
    }
}

/* Additional RTL layout fixes */
.sales-boxes, .overview-boxes {
    direction: rtl;
}

/* File upload specific fixes */
.file-upload-name, .form-text, .error-message {
    text-align: right;
}

/* Dropdown menus */
.dropdown-menu {
    left: auto;
    right: 0;
}

/* Service thumbnail container */
.top-sales-details li a {
    order: 2;
}

.service-info {
    order: 1;
}
    .home-section {
    position: relative;
    width: calc(100% - 240px);
    left: 240px;
    min-height: 100vh;
    transition: all 0.5s ease;
}

[dir="rtl"] .home-section {
    left: 0;
    right: 240px;
}
<?php endif; ?>
</style>

<?php include_once 'includes/footer.php'; ?>