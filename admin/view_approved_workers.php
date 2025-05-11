<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

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
            <div class="title">Approved Workers</div>
            
            <?php if (has_flash_message('success')): ?>
                <div class="alert alert-success"><?php echo get_flash_message('success'); ?></div>
            <?php endif; ?>
            
            <?php if (has_flash_message('error')): ?>
                <div class="alert alert-danger"><?php echo get_flash_message('error'); ?></div>
            <?php endif; ?>
            
            <div class="sales-details">
                <?php if (empty($approved_workers)): ?>
                    <div class="no-data" style="padding: 20px; text-align: center; font-style: italic; color: #666;">
                        No approved workers available at the moment.
                    </div>
                <?php else: ?>
                    <ul class="worker-requests-list">
                        <?php foreach ($approved_workers as $row): ?>
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
                                    <form action="includes/disable_worker.php" method="POST" onsubmit="return confirm('Do you want to disable this worker?');">
                                        <input type="hidden" name="worker_id" value="<?php echo h($row['user_id']); ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                        <button type="submit" class="btn btn-disable">
                                            <i class='bx bx-user-x'></i> Disable Worker
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
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
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
    width: 350px;
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
</style>

<?php include_once 'includes/footer.php'; ?>