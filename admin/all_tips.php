<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', 'Please log in to access the dashboard');
}

// Include tips data
require_once 'includes/tips_data.php';

// Include header
include_once 'includes/header.php';
?>

<div class="home-content">
    <div class="sales-boxes">
        <div class="recent-sales box">
            <div class="title">All Tips</div>

            <?php if (has_flash_message('success')): ?>
                <div class="alert alert-success"><?php echo get_flash_message('success'); ?></div>
            <?php endif; ?>

            <?php if (has_flash_message('error')): ?>
                <div class="alert alert-danger"><?php echo get_flash_message('error'); ?></div>
            <?php endif; ?>

            <!-- Daily Tips Section -->
            <div class="tips-section">
                <h2 class="section-title">Daily Tips</h2>
                <div class="tips-grid">
                    <?php if (empty($all_tips)): ?>
                        <div class="no-data">No daily tips available</div>
                    <?php else: ?>
                        <?php foreach ($all_tips as $tip): ?>
                            <div class="tip-card">
                                <?php if (!empty($tip['images'])): ?>
                                    <div class="tip-image">
                                        <img src="../public/images/tips/<?php echo h($tip['images']); ?>" alt="<?php echo h($tip['title']); ?>">
                                    </div>
                                <?php endif; ?>
                                <div class="tip-details">
                                    <h3><?php echo h($tip['title']); ?></h3>
                                    <p><?php echo h($tip['description']); ?></p>
                                </div>
                                <div class="tip-actions">
                                    <form action="edit_dailyTip.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="tip_id" value="<?php echo h($tip['tip_id']); ?>">
                                        <button type="submit" class="btn btn-edit">
                                            <i class="bx bx-edit"></i> Edit
                                        </button>
                                    </form>
                                    <form method="POST" action="includes/delete_tip.php" onsubmit="return confirm('Are you sure you want to delete this tip?');" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="tip_id" value="<?php echo h($tip['tip_id']); ?>">
                                        <input type="hidden" name="tip_type" value="daily tips">
                                        <button type="submit" class="btn btn-delete">
                                            <i class="bx bx-trash"></i> Delete
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
                <h2 class="section-title">Seasonal Tips</h2>
                <div class="tips-grid">
                    <?php if (empty($all_seasonal_tips)): ?>
                        <div class="no-data">No seasonal tips available</div>
                    <?php else: ?>
                        <?php foreach ($all_seasonal_tips as $tip): ?>
                            <div class="tip-card">
                                <div class="tip-details">
                                    <h3><?php echo h($tip['title']); ?></h3>
                                    <p><?php echo h($tip['description']); ?></p>
                                </div>
                                <div class="tip-actions">
                                    <form action="edit_seasonalTip.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="tip_id" value="<?php echo h($tip['tip_id']); ?>">
                                        <button type="submit" class="btn btn-edit">
                                            <i class="bx bx-edit"></i> Edit
                                        </button>
                                    </form>
                                    <form method="POST" action="includes/delete_tip.php" onsubmit="return confirm('Are you sure you want to delete this tip?');" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="tip_id" value="<?php echo h($tip['tip_id']); ?>">
                                        <input type="hidden" name="tip_type" value="seasonal tips">
                                        <button type="submit" class="btn btn-delete">
                                            <i class="bx bx-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="button">
                <a href="tips.php">Back to Tips</a>
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

    .tip-details p {
        color: #3a3a3ae1;
        font-style: italic;
        font-size: 14px;
        line-height: 1.5;
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
</style>

<?php include_once 'includes/footer.php'; ?>