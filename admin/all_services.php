<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Include language file
require_once 'lang.php';

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', $trans['please_log_in']);
}

// Include services data (now with translation support)
require_once 'includes/services_data.php';

// Handle service deletion if requested
if (isset($_POST['delete_service']) && isset($_POST['service_id'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        set_flash_message('error', 'Invalid form submission.');
    } else {
        $service_id = sanitize_input($_POST['service_id']);

        try {
            // Get image filename before deletion
            $stmt = $pdo->prepare("SELECT images FROM services WHERE service_id = :service_id");
            $stmt->bindParam(':service_id', $service_id);
            $stmt->execute();
            $service = $stmt->fetch();

            if ($service) {
                // Delete from database
                $stmt = $pdo->prepare("DELETE FROM services WHERE service_id = :service_id");
                $stmt->bindParam(':service_id', $service_id);

                if ($stmt->execute()) {
                    // Delete image file if it exists
                    $image_path = '../public/images/' . $service['images'];
                    if (file_exists($image_path)) {
                        unlink($image_path);
                    }

                    set_flash_message('success', $trans['service_deleted_successfully']);
                } else {
                    set_flash_message('error', $trans['failed_delete_service']);
                }
            } else {
                set_flash_message('error', $trans['service_not_found']);
            }
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            set_flash_message('error', $trans['database_error']);
        }
    }

    // Refresh the page to show updated data
    header('Location: all_services.php?lang=' . $lang);
    exit();
}

// Include header
include_once 'includes/header.php';
?>

<div class="home-content">
    <div class="sales-boxes">
        <div class="recent-sales box">
            <div class="title"><?php echo $trans['all_services']; ?></div>

            <?php if (has_flash_message('success')): ?>
                <div class="alert alert-success"><?php echo get_flash_message('success'); ?></div>
            <?php endif; ?>

            <?php if (has_flash_message('error')): ?>
                <div class="alert alert-danger"><?php echo get_flash_message('error'); ?></div>
            <?php endif; ?>

            <div class="services-grid">
                <?php if (empty($all_services)): ?>
                    <div class="no-data"><?php echo $trans['no_services_available']; ?></div>
                <?php else: ?>
                    <?php foreach ($all_services as $service): ?>
                        <div class="service-card">
                            <div class="service-image">
                                <img src="../public/images/<?php echo h($service['images']); ?>" alt="<?php echo h($service['title']); ?>">
                            </div>
                            <div class="service-details">
                                <h3><?php echo h($service['title']); ?></h3>
                                <p><?php echo h($service['description']); ?></p>
                            </div>
                            <div class="service-actions">
                                <a href="edit_service.php?id=<?php echo h($service['service_id']); ?>&lang=<?php echo $lang; ?>" class="btn btn-edit">
                                    <i class="bx bx-edit"></i> <?php echo $trans['edit']; ?>
                                </a>
                                <form method="POST" onsubmit="return confirm('<?php echo $trans['confirm_delete_service']; ?>');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                    <input type="hidden" name="service_id" value="<?php echo h($service['service_id']); ?>">
                                    <button type="submit" name="delete_service" class="btn btn-delete">
                                        <i class="bx bx-trash"></i> <?php echo $trans['delete']; ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="button">
                <a href="services.php?lang=<?php echo $lang; ?>"><?php echo $trans['back_to_services']; ?></a>
            </div>
        </div>
    </div>
</div>

<style>
    .sales-boxes {
        width: 100%;
        max-width: none;
        margin: 0;
        padding: 0 10px;
    }

    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
        width: 100%;
        padding: 0;
        box-sizing: border-box;
    }

    .recent-sales.box {
        width: 100%;
        padding: 20px;
        box-sizing: border-box;
    }

    /* On larger screens (min-width: 1024px), force exactly 3 columns */
    @media (min-width: 1024px) {
        .services-grid {
            grid-template-columns: repeat(3, minmax(300px, 1fr));
        }
    }

    .service-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    .service-card:hover {
        transform: translateY(-5px);
    }

    .service-image {
        height: 200px;
        overflow: hidden;
    }

    .service-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .service-details {
        padding: 15px;
    }

    .service-details h3 {
        color: #f97f4b;
        margin-bottom: 10px;
    }

    .service-details p {
        color: #3a3a3ae1;
        font-style: italic;
        font-size: 14px;
    }

    .service-actions {
        display: flex;
        padding: 0 15px 15px;
        justify-content: space-between;
    }

    .btn {
        padding: 8px 16px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        border: none;
    }

    .btn i {
        margin-right: 5px;
    }

    .btn-edit {
        background-color: #79bcb1;
        color: white;
        text-decoration: none;
    }

    .btn-delete {
        background-color: #e74c3c;
        color: white;
    }

    .btn-edit:hover,
    .btn-delete:hover {
        opacity: 0.9;
    }

    .alert {
        padding: 10px 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }

    .alert-success {
        background-color: rgba(46, 204, 113, 0.1);
        color: #2ecc71;
        border-left: 4px solid #2ecc71;
    }

    .alert-danger {
        background-color: rgba(231, 76, 60, 0.1);
        color: #e74c3c;
        border-left: 4px solid #e74c3c;
    }

    .no-data {
        text-align: center;
        padding: 30px;
        font-style: italic;
        color: #666;
    }

    /* RTL support for Arabic */
    <?php if ($lang === 'ar'): ?>
    body {
        direction: rtl;
        text-align: right;
    }

    .service-actions {
        flex-direction: row-reverse;
    }

    .btn i {
        margin-right: 0;
        margin-left: 5px;
    }

    .services-grid {
        direction: rtl;
    }

    .service-card {
        direction: rtl;
        text-align: right;
    }

    .service-details h3,
    .service-details p {
        text-align: right;
    }
    <?php endif; ?>

    /* French language adjustments */
    <?php if ($lang === 'fr'): ?>
    .service-details h3,
    .service-details p {
        line-height: 1.4;
    }
    <?php endif; ?>
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