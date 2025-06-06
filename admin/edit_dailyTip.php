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
    if (!isset($_POST['csrf_token'])) {
        $error_message = "Invalid form submission.";
    } else if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_message = "Invalid form submission.";
    } else {
        $tip_id = filter_var($_POST['tip_id'], FILTER_VALIDATE_INT);

        if ($tip_id === false || $tip_id <= 0) {
            $error_message = "Invalid tip ID.";
        } else {
            try {
                // Fetch the existing daily tip details
                $stmt = $pdo->prepare("SELECT * FROM tips WHERE tip_id = :tip_id AND type = 'daily tips'");
                $stmt->bindParam(':tip_id', $tip_id, PDO::PARAM_INT);
                $stmt->execute();

                $tip = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$tip) {
                    $error_message = "Daily tip not found.";
                }
            } catch (PDOException $e) {
                error_log('Database Error in edit_dailyTip.php (fetch): ' . $e->getMessage());
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
            <div class="title">Edit Daily Tip</div>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo h($success_message); ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo h($error_message); ?></div>
            <?php endif; ?>

            <div class="sales-details">
                <?php if ($tip): ?>
                    <form action="includes/edit_dailyTip.php" method="POST" class="tip-form" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <input type="hidden" name="tip_id" value="<?php echo h($tip['tip_id']); ?>">
                        <input type="hidden" name="update_tip" value="1">

                        <div class="field">
                            <label for="title">Title</label>
                            <input type="text" id="title" name="title" value="<?php echo h($tip['title']); ?>" required maxlength="100" placeholder="Enter tip title">
                            <div class="error-message" id="title_error"></div>
                        </div>

                        <div class="field">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" required maxlength="500" rows="4" placeholder="Enter tip description"><?php echo h($tip['description']); ?></textarea>
                            <div class="error-message" id="description_error"></div>
                        </div>

                        <?php if (!empty($tip['images'])): ?>
                            <div class="field">
                                <label>Current Image</label>
                                <div class="current-image">
                                    <?php
                                    $image_path = '../public/images/tips/' . h($tip['images']);
                                    if (file_exists($image_path) || filter_var($image_path, FILTER_VALIDATE_URL)):
                                    ?>
                                        <img src="<?php echo $image_path; ?>" alt="Current Image" class="tip-thumbnail" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                        <p style="display: none; color: #e74c3c;">Image could not be loaded: <?php echo h($tip['images']); ?></p>
                                    <?php else: ?>
                                        <p style="color: #e74c3c;">Image file not found: <?php echo h($tip['images']); ?></p>
                                    <?php endif; ?>
                                    <p>Current image: <?php echo h($tip['images']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="field">
                            <label for="image"><?php echo !empty($tip['images']) ? 'Replace Image (Optional)' : 'Upload Image'; ?></label>
                            <div class="file-upload-wrapper">
                                <input type="file" id="image" name="image" accept="image/x-png,image/gif,image/jpeg,image/jpg" class="upload">
                                <label for="image" class="file-upload-label">
                                    <i class="bx bx-cloud-upload"></i>
                                    <span>Choose a file</span>
                                </label>
                                <div class="file-upload-name" id="file_name">No file chosen</div>
                            </div>
                            <small class="form-text">Max size: 2MB | Formats: JPG, PNG, GIF</small>
                            <div class="error-message" id="image_error"></div>
                        </div>

                        <div class="button-group">
                            <button type="submit" class="btn btn-update">
                                <span class="btn-text">Update Daily Tip</span>
                                <span class="btn-loader" style="display:none;">
                                    <i class="bx bx-loader bx-spin"></i>
                                </span>
                            </button>
                            <a href="all_tips.php" class="btn btn-cancel">Cancel</a>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="no-data">
                        <p>Daily tip not found or could not be loaded.</p>
                        <a href="all_tips.php" class="btn btn-cancel">Back to All Tips</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    /* Base styles */
    .tip-form {
        margin-top: 30px;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 0 20px 20px;
    }

    .tip-form .field {
        margin-bottom: 15px;
        width: 100%;
        max-width: 750px;
    }

    .tip-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #444;
        font-size: 14px;
    }

    .tip-form input[type="text"],
    .tip-form textarea {
        width: 100%;
        padding: 12px 15px;
        margin-top: 5px;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-sizing: border-box;
        font-family: inherit;
        font-size: 14px;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .tip-form textarea {
        resize: vertical;
        min-height: 100px;
    }

    /* File upload styling */
    .file-upload-wrapper {
        position: relative;
        margin-top: 5px;
    }

    .file-upload-label {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 12px 15px;
        background: #f5f5f5;
        border: 1px dashed #ccc;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .file-upload-label:hover {
        background: #eee;
        border-color: #999;
    }

    .file-upload-label i {
        margin-right: 8px;
        font-size: 18px;
        color: #666;
    }

    .file-upload-label span {
        color: #666;
    }

    .file-upload-name {
        margin-top: 5px;
        font-size: 13px;
        color: #666;
        text-align: center;
    }

    .upload {
        position: absolute;
        width: 0.1px;
        height: 0.1px;
        opacity: 0;
        overflow: hidden;
        z-index: -1;
    }

    .tip-form small.form-text {
        color: #888;
        font-size: 12px;
        margin-top: 5px;
        display: block;
    }

    .error-message {
        color: #e74c3c;
        font-size: 12px;
        margin-top: 5px;
        display: none;
    }

    /* Current image styling */
    .current-image {
        margin-bottom: 15px;
    }

    .tip-thumbnail {
        max-height: 150px;
        max-width: 100%;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Button styling */
    .btn {
        padding: 12px 24px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
        min-width: 150px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        border: none;
    }

    .btn-update {
        background-color: #f97f4b;
        color: white;
    }

    .btn-cancel {
        background-color: #95a5a6;
        color: white;
        text-decoration: none;
    }

    .btn-update:hover {
        background-color: #d35400;
    }

    .btn-cancel:hover {
        background-color: #7f8c8d;
    }

    .btn-loader {
        margin-left: 8px;
    }

    /* Alert styling */
    .alert {
        padding: 12px 15px;
        margin-bottom: 20px;
        border-radius: 4px;
        width: 100%;
        max-width: 750px;
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

    /* Button group */
    .button-group {
        display: flex;
        gap: 15px;
        margin-top: 20px;
        justify-content: center;
    }

    /* No data message */
    .no-data {
        text-align: center;
        padding: 30px;
        font-style: italic;
        color: #666;
    }

    /* Responsive styles */
    @media (max-width: 768px) {
        .tip-form {
            padding: 0 15px 15px;
        }

        .tip-form input[type="text"],
        .tip-form textarea {
            padding: 10px 12px;
            font-size: 16px;
        }

        .button-group {
            flex-direction: column;
            gap: 10px;
        }

        .btn {
            width: 100%;
        }
    }
</style>

<script>
    // Display the popup if it has content
    document.addEventListener('DOMContentLoaded', function() {
        // File upload name display
        document.getElementById('image').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : 'No file chosen';
            document.getElementById('file_name').textContent = fileName;
        });

        // Form validation
        const form = document.querySelector('.tip-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Clear previous errors
                this.querySelectorAll('.error-message').forEach(el => {
                    el.style.display = 'none';
                });

                // Validate title
                const title = this.querySelector('#title');
                if (title.value.trim().length < 3) {
                    document.getElementById('title_error').textContent = 'Title must be at least 3 characters long';
                    document.getElementById('title_error').style.display = 'block';
                    isValid = false;
                }

                // Validate description
                const description = this.querySelector('#description');
                if (description.value.trim().length < 10) {
                    document.getElementById('description_error').textContent = 'Description must be at least 10 characters long';
                    document.getElementById('description_error').style.display = 'block';
                    isValid = false;
                }

                // Validate image if new one is selected
                const image = this.querySelector('#image');
                if (image.files.length > 0) {
                    const file = image.files[0];
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    const maxSize = 2 * 1024 * 1024; // 2MB

                    if (!allowedTypes.includes(file.type)) {
                        document.getElementById('image_error').textContent = 'Only JPG, PNG, and GIF images are allowed';
                        document.getElementById('image_error').style.display = 'block';
                        isValid = false;
                    }

                    if (file.size > maxSize) {
                        document.getElementById('image_error').textContent = 'Image size must be less than 2MB';
                        document.getElementById('image_error').style.display = 'block';
                        isValid = false;
                    }
                }

                if (!isValid) {
                    e.preventDefault();
                    return false;
                }

                // Show loading state
                const btn = this.querySelector('.btn-update');
                if (btn) {
                    const btnText = btn.querySelector('.btn-text');
                    const btnLoader = btn.querySelector('.btn-loader');
                    if (btnText) btnText.style.display = 'none';
                    if (btnLoader) btnLoader.style.display = 'block';
                    btn.disabled = true;
                }
            });
        }

        // Auto-hide alerts after 5 seconds
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

        // Image error handling
        document.querySelectorAll('.tip-thumbnail').forEach(img => {
            img.addEventListener('error', function() {
                this.style.display = 'none';
                // Create a placeholder
                const placeholder = document.createElement('span');
                placeholder.className = 'no-image';
                placeholder.textContent = 'Image not available';
                this.parentNode.insertBefore(placeholder, this);
            });
        });
    });
</script>

<?php include_once 'includes/footer.php'; ?>