<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', 'Please log in to access the dashboard');
}

// Initialize variables
$tip = null;
$error_message = '';
$success_message = '';

// Handle form submission to fetch tip for editing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tip_id']) && !isset($_POST['update_tip'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_message = "Invalid form submission.";
    } else {
        $tip_id = filter_var($_POST['tip_id'], FILTER_VALIDATE_INT);
        
        if ($tip_id === false || $tip_id <= 0) {
            $error_message = "Invalid tip ID.";
        } else {
            try {
                // Fetch the existing seasonal tip details
                $stmt = $pdo->prepare("SELECT * FROM tips WHERE tip_id = :tip_id AND type = 'seasonal tips'");
                $stmt->bindParam(':tip_id', $tip_id, PDO::PARAM_INT);
                $stmt->execute();
                
                $tip = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$tip) {
                    $error_message = "Seasonal tip not found.";
                }
                
            } catch (PDOException $e) {
                error_log('Database Error in edit_seasonalTip.php (fetch): ' . $e->getMessage());
                $error_message = "Database error occurred.";
            }
        }
    }
}

// Check for messages from update process
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// If no tip is loaded and no error, redirect to all tips
if (!$tip && !$error_message) {
    redirect('all_tips.php', 'Please select a tip to edit');
}

// Include header
include_once 'includes/header.php';
?>

<div class="home-content">
    <div class="sales-boxes">
        <div class="recent-sales box">
            <div class="title">Edit Seasonal Tip</div>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo h($success_message); ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo h($error_message); ?></div>
            <?php endif; ?>
            
            <div class="sales-details">
                <?php if ($tip): ?>
                    <form action="includes/edit_seasonalTip.php" method="POST" class="tip-form" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <input type="hidden" name="tip_id" value="<?php echo h($tip['tip_id']); ?>">
                        
                        <div class="field">
                            <label for="title">Title</label>
                            <input type="text" id="title" name="title" value="<?php echo h($tip['title']); ?>" required maxlength="100">
                        </div>
                        
                        <div class="field">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" required maxlength="500" placeholder="Enter seasonal tip description"><?php echo h($tip['description']); ?></textarea>
                        </div>
                        
                        <div class="field">
                            <label for="tip1">First Seasonal Tip</label>
                            <input type="text" id="tip1" name="tip1" value="<?php echo h($tip['f_tip']); ?>" required maxlength="255">
                        </div>
                        
                        <div class="field">
                            <label for="tip2">Second Seasonal Tip</label>
                            <input type="text" id="tip2" name="tip2" value="<?php echo h($tip['s_tip']); ?>" required maxlength="255">
                        </div>
                        
                        <div class="button-group">
                            <button type="submit" name="update_tip" class="btn btn-update">Update Seasonal Tip</button>
                            <a href="all_tips.php" class="btn btn-cancel">Cancel</a>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="no-data">
                        <p>Seasonal tip not found or could not be loaded.</p>
                        <a href="all_tips.php" class="btn btn-cancel">Back to All Tips</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .tip-form {
        margin-top: 30px;
        display: flex;
        flex-direction: column;
    }

    .tip-form .field {
        margin-bottom: 15px;
    }

    .tip-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #333;
    }

    .tip-form input[type="text"],
    .tip-form textarea,
    .tip-form input[type="file"] {
        width: 750px;
        padding: 10px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
    }

    .tip-form textarea {
        resize: vertical;
        min-height: 100px;
        font-family: inherit;
    }

    .tip-form .upload {
        padding: 3px;
    }

    .current-image {
        margin-bottom: 10px;
    }

    .button-group {
        display: flex;
        gap: 10px;
        margin-top: 15px;
        justify-content: center;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 500;
        border: none;
        transition: all 0.3s ease;
    }

    .btn-update {
        background-color: #ff6c40e4;
        color: white;
    }

    .btn-cancel {
        background-color: #95a5a6;
        color: white;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-update:hover, .btn-cancel:hover {
        opacity: 0.9;
    }

    .alert {
        padding: 10px 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }

    .alert-success {
        background-color: rgba(40, 167, 69, 0.1);
        color: #28a745;
        border-left: 4px solid #28a745;
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

    .tip-form small {
        color: #666;
        font-size: 12px;
        margin-top: 5px;
        display: block;
    }
</style>

<script>
    // Form validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('.tip-form');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                const title = form.querySelector('input[name="title"]');
                const description = form.querySelector('textarea[name="description"]');
                const tip1 = form.querySelector('input[name="tip1"]');
                const tip2 = form.querySelector('input[name="tip2"]');
                
                // Validate title
                if (title.value.trim().length < 3) {
                    e.preventDefault();
                    alert('Title must be at least 3 characters long.');
                    title.focus();
                    return false;
                }
                
                // Validate description
                if (description.value.trim().length < 10) {
                    e.preventDefault();
                    alert('Description must be at least 10 characters long.');
                    description.focus();
                    return false;
                }
                
                // Validate first tip
                if (tip1.value.trim().length < 5) {
                    e.preventDefault();
                    alert('First seasonal tip must be at least 5 characters long.');
                    tip1.focus();
                    return false;
                }
                
                // Validate second tip
                if (tip2.value.trim().length < 5) {
                    e.preventDefault();
                    alert('Second seasonal tip must be at least 5 characters long.');
                    tip2.focus();
                    return false;
                }
            });
        }
    });

    // Auto-hide success/error messages after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            }, 5000);
        });
    });
</script>

<?php include_once 'includes/footer.php'; ?>