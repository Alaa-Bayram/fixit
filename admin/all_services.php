<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', 'Please log in to access the dashboard');
}

// Include services data
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

                    set_flash_message('success', 'Service deleted successfully!');
                } else {
                    set_flash_message('error', 'Failed to delete service.');
                }
            } else {
                set_flash_message('error', 'Service not found.');
            }
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            set_flash_message('error', 'Database error occurred.');
        }
    }

    // Refresh the page to show updated data
    header('Location: all_services.php');
    exit();
}

// Include header
include_once 'includes/header.php';
?>

<div class="home-content">
    <div class="sales-boxes">
        <div class="recent-sales box">
            <div class="title">All Services</div>

            <?php if (has_flash_message('success')): ?>
                <div class="alert alert-success"><?php echo get_flash_message('success'); ?></div>
            <?php endif; ?>

            <?php if (has_flash_message('error')): ?>
                <div class="alert alert-danger"><?php echo get_flash_message('error'); ?></div>
            <?php endif; ?>

            <div class="services-grid">
                <?php if (empty($all_services)): ?>
                    <div class="no-data">No services available</div>
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
                                <a href="edit_service.php?id=<?php echo h($service['service_id']); ?>" class="btn btn-edit">
                                    <i class="bx bx-edit"></i> Edit
                                </a>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this service?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                    <input type="hidden" name="service_id" value="<?php echo h($service['service_id']); ?>">
                                    <button type="submit" name="delete_service" class="btn btn-delete">
                                        <i class="bx bx-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="button">
                <a href="services.php">Back to Services</a>
            </div>
        </div>
    </div>
</div>

<style>
    .sales-boxes {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 0 10px; /* Added small side padding */
}
    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
        width: 100%;
        /* Add this line */
        padding: 0;
        /* Add this line to remove any padding */
        box-sizing: border-box;
        /* Add this line to include padding in width calculation */
    }

    .recent-sales.box {
        width: 100%;
        /* Add this line to ensure the container takes full width */
        padding: 20px;
        /* Adjust padding as needed */
        box-sizing: border-box;
        /* Add this line */
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
        background-color: #3498db;
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
</style>

<?php include_once 'includes/footer.php'; ?>