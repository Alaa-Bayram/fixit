<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'lang.php'; // Include language file

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', 'Please log in to access the dashboard');
}

// Fetch approved workers
$search = null;
$query = "SELECT * FROM users WHERE usertype='worker' AND access_status='approved'";
$params = [];

// Check if there's a search query
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = sanitize_input($_GET['search']);
    // Modify query to search by first or last name or skills
    $query .= " AND (fname LIKE :search OR lname LIKE :search OR skills LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

try {
    $stmt = $pdo->prepare($query);
    
    // Bind parameters if searching
    if (!empty($params)) {
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
    }
    
    $stmt->execute();
    $approved_workers = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    $approved_workers = [];
}

// Include header
include_once 'includes/header.php';
?>

<div class="home-content">
    <div class="sales-boxes">
        <div class="recent-sales box">
            <div class="title"><?php echo $trans['approved_workers'] ?? 'Approved Workers'; ?></div>
            
            <?php if (has_flash_message('success')): ?>
                <div class="alert alert-success"><?php echo get_flash_message('success'); ?></div>
            <?php endif; ?>
            
            <?php if (has_flash_message('error')): ?>
                <div class="alert alert-danger"><?php echo get_flash_message('error'); ?></div>
            <?php endif; ?>
            
            <div class="sales-details">
                <?php if (empty($approved_workers)): ?>
                    <div class="no-data" style="padding: 20px; text-align: center; font-style: italic; color: #666;">
                        <?php echo $trans['no_approved_workers'] ?? 'No approved workers available at the moment.'; ?>
                    </div>
                <?php else: ?>
                    <ul class="worker-requests-list">
                        <?php foreach ($approved_workers as $row): ?>
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
                                    <form action="includes/disable_worker.php" method="POST" onsubmit="return confirm('<?php echo addslashes($trans['confirm_disable_worker'] ?? 'Do you want to disable this worker?'); ?>');">
                                        <input type="hidden" name="worker_id" value="<?php echo h($row['user_id']); ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                        <button type="submit" class="btn btn-disable">
                                            <i class='bx bx-user-x'></i> <?php echo $trans['disable_worker'] ?? 'Disable Worker'; ?>
                                        </button>
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

.btn-disable {
    background-color: var(--danger);
    color: white;
}

.btn-disable:hover {
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
<?php endif; ?>
</style>

<?php include_once 'includes/footer.php'; ?>