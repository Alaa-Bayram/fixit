<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Include language file
require_once 'lang.php';

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', $trans['please_log_in']);
}

// Include tips data (now with translation support)
require_once 'includes/tips_data.php';

// Handle tip deletion if requested
if (isset($_POST['delete_tip']) && isset($_POST['tip_id'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        set_flash_message('error', 'Invalid form submission.');
    } else {
        $tip_id = sanitize_input($_POST['tip_id']);

        try {
            // Get image filename before deletion
            $stmt = $pdo->prepare("SELECT images FROM tips WHERE tip_id = :tip_id");
            $stmt->bindParam(':tip_id', $tip_id);
            $stmt->execute();
            $tip = $stmt->fetch();

            if ($tip) {
                // Delete from database
                $stmt = $pdo->prepare("DELETE FROM tips WHERE tip_id = :tip_id");
                $stmt->bindParam(':tip_id', $tip_id);

                if ($stmt->execute()) {
                    // Delete image file if it exists
                    if (!empty($tip['images'])) {
                        $image_path = '../public/images/tips/' . $tip['images'];
                        if (file_exists($image_path)) {
                            unlink($image_path);
                        }
                    }

                    set_flash_message('success', $trans['tip_deleted_successfully']);
                } else {
                    set_flash_message('error', $trans['failed_delete_tip']);
                }
            } else {
                set_flash_message('error', $trans['tip_not_found']);
            }
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            set_flash_message('error', $trans['database_error']);
        }
    }

    // Refresh the page to show updated data
    header('Location: all_tips.php?lang=' . $lang);
    exit();
}

// Include header
include_once 'includes/header.php';
?>

<div class="home-content">
    <div class="sales-boxes">
        <div class="recent-sales box">
            <div class="title"><?php echo $trans['all_tips']; ?></div>

            <?php if (has_flash_message('success')): ?>
                <div class="alert alert-success"><?php echo get_flash_message('success'); ?></div>
            <?php endif; ?>

            <?php if (has_flash_message('error')): ?>
                <div class="alert alert-danger"><?php echo get_flash_message('error'); ?></div>
            <?php endif; ?>

            <!-- Daily Tips Section -->
            <div class="tips-section">
                <h2 class="section-title"><?php echo $trans['daily_tips']; ?></h2>
                <div class="tips-grid">
                    <?php if (empty($daily_tips)): ?>
                        <div class="no-data"><?php echo $trans['no_daily_tips_available']; ?></div>
                    <?php else: ?>
                        <?php foreach ($daily_tips as $tip): ?>
                            <div class="tip-card">
                                <?php if (!empty($tip['images'])): ?>
                                    <div class="tip-image">
                                        <img src="../public/images/tips/<?php echo h($tip['images']); ?>" alt="<?php echo h($tip['title']); ?>">
                                    </div>
                                <?php endif; ?>
                                <div class="tip-details">
                                    <h3><?php echo h($tip['title']); ?></h3>
                                    <p><?php echo h($tip['description']); ?></p>
                                    <?php if (!empty($tip['f_tip'])): ?>
                                        <div class="tip-content">
                                            <strong><?php echo $trans['first_tip']; ?>:</strong>
                                            <p><?php echo h($tip['f_tip']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($tip['s_tip'])): ?>
                                        <div class="tip-content">
                                            <strong><?php echo $trans['second_tip']; ?>:</strong>
                                            <p><?php echo h($tip['s_tip']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="tip-actions">
                                    <a href="edit_dailyTip.php?id=<?php echo h($tip['tip_id']); ?>&lang=<?php echo $lang; ?>" class="btn btn-edit">
                                        <i class="bx bx-edit"></i> <?php echo $trans['edit']; ?>
                                    </a>
                                    <form method="POST" onsubmit="return confirm('<?php echo $trans['confirm_delete_tip']; ?>');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                        <input type="hidden" name="tip_id" value="<?php echo h($tip['tip_id']); ?>">
                                        <button type="submit" name="delete_tip" class="btn btn-delete">
                                            <i class="bx bx-trash"></i> <?php echo $trans['delete']; ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Seasonal Tips Section -->
            <div class="tips-section">
                <h2 class="section-title"><?php echo $trans['seasonal_tips']; ?></h2>
                <div class="tips-grid">
                    <?php if (empty($seasonal_tips)): ?>
                        <div class="no-data"><?php echo $trans['no_seasonal_tips_available']; ?></div>
                    <?php else: ?>
                        <?php foreach ($seasonal_tips as $tip): ?>
                            <div class="tip-card">
                                <?php if (!empty($tip['images'])): ?>
                                    <div class="tip-image">
                                        <img src="../public/images/tips/<?php echo h($tip['images']); ?>" alt="<?php echo h($tip['title']); ?>">
                                    </div>
                                <?php endif; ?>
                                <div class="tip-details">
                                    <h3><?php echo h($tip['title']); ?></h3>
                                    <p><?php echo h($tip['description']); ?></p>
                                    <?php if (!empty($tip['f_tip'])): ?>
                                        <div class="tip-content">
                                            <strong><?php echo $trans['first_tip']; ?>:</strong>
                                            <p><?php echo h($tip['f_tip']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($tip['s_tip'])): ?>
                                        <div class="tip-content">
                                            <strong><?php echo $trans['second_tip']; ?>:</strong>
                                            <p><?php echo h($tip['s_tip']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="tip-actions">
                                    <a href="edit_seasonalTip.php?id=<?php echo h($tip['tip_id']); ?>&lang=<?php echo $lang; ?>" class="btn btn-edit">
                                        <i class="bx bx-edit"></i> <?php echo $trans['edit']; ?>
                                    </a>
                                    <form method="POST" onsubmit="return confirm('<?php echo $trans['confirm_delete_tip']; ?>');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                        <input type="hidden" name="tip_id" value="<?php echo h($tip['tip_id']); ?>">
                                        <button type="submit" name="delete_tip" class="btn btn-delete">
                                            <i class="bx bx-trash"></i> <?php echo $trans['delete']; ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="button">
                <a href="tips.php?lang=<?php echo $lang; ?>"><?php echo $trans['back_to_tips']; ?></a>
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

    .recent-sales.box {
        width: 100%;
        padding: 20px;
        box-sizing: border-box;
    }

    .tips-section {
        margin-bottom: 40px;
    }

    .section-title {
        color: #f97f4b;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
    }

    .tips-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
        width: 100%;
        padding: 0;
        box-sizing: border-box;
    }

    /* On larger screens (min-width: 1024px), force exactly 3 columns */
    @media (min-width: 1024px) {
        .tips-grid {
            grid-template-columns: repeat(3, minmax(300px, 1fr));
        }
    }

    .tip-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    .tip-card:hover {
        transform: translateY(-5px);
    }

    .tip-image {
        height: 200px;
        overflow: hidden;
    }

    .tip-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .tip-details {
        padding: 15px;
    }

    .tip-details h3 {
        color: #f97f4b;
        margin-bottom: 10px;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .tip-details > p {
        color: #3a3a3ae1;
        font-style: italic;
        font-size: 14px;
        line-height: 1.5;
        margin-bottom: 15px;
    }

    .tip-content {
        margin-bottom: 12px;
        padding: 10px;
        background-color: #f8f9fa;
        border-radius: 4px;
        border-left: 3px solid #f97f4b;
    }

    .tip-content strong {
        color: #f97f4b;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .tip-content p {
        color: #555;
        font-size: 13px;
        line-height: 1.4;
        margin: 5px 0 0 0;
    }

    .tip-actions {
        display: flex;
        padding: 0 15px 15px;
        justify-content: space-between;
        gap: 10px;
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
        text-decoration: none;
    }

    .btn i {
        margin-right: 5px;
    }

    .btn-edit {
        background-color: #79bcb1;
        color: white;
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
        grid-column: 1 / -1;
    }

    .button {
        text-align: center;
        margin-top: 30px;
    }

    .button a {
        display: inline-block;
        padding: 12px 24px;
        background-color: #f97f4b;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .button a:hover {
        background-color: #e96f3b;
        transform: translateY(-2px);
    }

    /* RTL support for Arabic */
    <?php if ($lang === 'ar'): ?>
    body {
        direction: rtl;
        text-align: right;
    }

    .tip-actions {
        flex-direction: row-reverse;
    }

    .btn i {
        margin-right: 0;
        margin-left: 5px;
    }

    .tips-grid {
        direction: rtl;
    }

    .tip-card {
        direction: rtl;
        text-align: right;
    }

    .tip-details h3,
    .tip-details p {
        text-align: right;
    }

    .tip-content {
        text-align: right;
        border-left: none;
        border-right: 3px solid #f97f4b;
    }

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
        .tips-section {
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
    <?php endif; ?>

    /* French language adjustments */
    <?php if ($lang === 'fr'): ?>
    .tip-details h3,
    .tip-details p,
    .tip-content p {
        line-height: 1.4;
    }
    <?php endif; ?>

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .tips-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .tip-actions {
            flex-direction: column;
            gap: 8px;
        }
        
        .btn {
            width: 100%;
            justify-content: center;
        }
        
        .section-title {
            font-size: 1.3rem;
            text-align: center;
        }
    }

    @media (max-width: 480px) {
        .recent-sales.box {
            padding: 15px;
        }
        
        .tip-details {
            padding: 12px;
        }
        
        .tip-actions {
            padding: 0 12px 12px;
        }
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
</style>

<?php include_once 'includes/footer.php'; ?>