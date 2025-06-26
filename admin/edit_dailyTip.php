<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'lang.php'; // Include the language file

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', $trans['please_log_in']);
}

// Initialize variables
$tip = null;
$error_message = '';
$success_message = '';
$tip_id = null;

// Get tip ID from URL or POST
if (isset($_GET['id'])) {
    $tip_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
} elseif (isset($_POST['tip_id'])) {
    $tip_id = filter_var($_POST['tip_id'], FILTER_VALIDATE_INT);
}

// Validate tip ID
if ($tip_id === false || $tip_id <= 0) {
    redirect('all_tips.php?lang=' . $lang, $trans['invalid_tip_id'] ?? "Invalid tip ID.");
}

// Fetch the tip data
try {
    $stmt = $pdo->prepare("SELECT * FROM tips WHERE tip_id = :tip_id AND type = 'daily tips'");
    $stmt->bindParam(':tip_id', $tip_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $tip = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tip) {
        redirect('all_tips.php?lang=' . $lang, $trans['tip_not_found'] ?? "Daily tip not found.");
    }
} catch (PDOException $e) {
    error_log('Database Error in edit_dailyTip.php (fetch): ' . $e->getMessage());
    redirect('all_tips.php?lang=' . $lang, $trans['database_error']);
}

// Handle form submission for updating tip
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_tip'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token'])) {
        set_flash_message('error', $trans['invalid_form_submission']);
        header('Location: edit_dailyTip.php?id=' . $tip_id . '&lang=' . $lang);
        exit();
    }

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        set_flash_message('error', $trans['invalid_form_submission']);
        header('Location: edit_dailyTip.php?id=' . $tip_id . '&lang=' . $lang);
        exit();
    }
    
    // Sanitize input for all languages - DON'T HTML encode here
    $title = trim(stripslashes($_POST['title'] ?? ''));
    $description = trim(stripslashes($_POST['description'] ?? ''));
    $title_fr = trim(stripslashes($_POST['title_fr'] ?? ''));
    $description_fr = trim(stripslashes($_POST['description_fr'] ?? ''));
    $title_ar = trim(stripslashes($_POST['title_ar'] ?? ''));
    $description_ar = trim(stripslashes($_POST['description_ar'] ?? ''));
    
    // Validate input - at least English fields are required
    if (empty($title) || empty($description)) {
        set_flash_message('error', $trans['all_fields_required']);
        header('Location: edit_dailyTip.php?id=' . $tip_id . '&lang=' . $lang);
        exit();
    }
    
    try {
        // Check if a new image was uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            // Validate file type and size
            if (!in_array($_FILES['image']['type'], $allowed_types)) {
                set_flash_message('error', $trans['only_jpg_png_gif_allowed']);
                header('Location: edit_dailyTip.php?id=' . $tip_id . '&lang=' . $lang);
                exit();
            }
            
            if ($_FILES['image']['size'] > $max_size) {
                set_flash_message('error', $trans['image_size_less_than_2mb']);
                header('Location: edit_dailyTip.php?id=' . $tip_id . '&lang=' . $lang);
                exit();
            }
            
            // Generate a unique filename
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('tip_') . '.' . $file_extension;
            $upload_path = '../public/images/tips/' . $filename;
            
            // Create directory if it doesn't exist
            $dir = '../public/images/tips';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Move the uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if it exists
                if (!empty($tip['images'])) {
                    $old_image_path = '../public/images/tips/' . $tip['images'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                
                // Update database with new image and all language fields
                $stmt = $pdo->prepare("UPDATE tips SET title = :title, description = :description, title_fr = :title_fr, description_fr = :description_fr, title_ar = :title_ar, description_ar = :description_ar, images = :images WHERE tip_id = :tip_id");
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':title_fr', $title_fr);
                $stmt->bindParam(':description_fr', $description_fr);
                $stmt->bindParam(':title_ar', $title_ar);
                $stmt->bindParam(':description_ar', $description_ar);
                $stmt->bindParam(':images', $filename);
                $stmt->bindParam(':tip_id', $tip_id);
            } else {
                set_flash_message('error', $trans['failed_upload_image']);
                header('Location: edit_dailyTip.php?id=' . $tip_id . '&lang=' . $lang);
                exit();
            }
        } else {
            // Update only text fields
            $stmt = $pdo->prepare("UPDATE tips SET title = :title, description = :description, title_fr = :title_fr, description_fr = :description_fr, title_ar = :title_ar, description_ar = :description_ar WHERE tip_id = :tip_id");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':title_fr', $title_fr);
            $stmt->bindParam(':description_fr', $description_fr);
            $stmt->bindParam(':title_ar', $title_ar);
            $stmt->bindParam(':description_ar', $description_ar);
            $stmt->bindParam(':tip_id', $tip_id);
        }
        
        if ($stmt->execute()) {
            set_flash_message('success', $trans['tip_updated_successfully'] ?? 'Daily tip updated successfully.');
            header('Location: all_tips.php?lang=' . $lang);
            exit();
        } else {
            set_flash_message('error', $trans['failed_update_tip'] ?? 'Failed to update tip.');
        }
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        set_flash_message('error', $trans['database_error']);
    }
    
    // Redirect back to edit page
    header('Location: edit_dailyTip.php?id=' . $tip_id . '&lang=' . $lang);
    exit();
}

// Check for flash messages
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Include header
include_once 'includes/header.php';
?>

<div class="home-content">
    <div class="sales-boxes">
        <div class="recent-sales box">
            <div class="title"><?php echo $trans['edit_daily_tip'] ?? 'Edit Daily Tip'; ?></div>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo h($success_message); ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo h($error_message); ?></div>
            <?php endif; ?>

            <div class="sales-details">
                <form method="POST" class="tip-form" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    <input type="hidden" name="tip_id" value="<?php echo h($tip['tip_id']); ?>">
                    <input type="hidden" name="update_tip" value="1">

                    <!-- Language Tabs -->
                    <div class="language-tabs">
                        <div class="tab active" data-lang="en">English</div>
                        <div class="tab" data-lang="fr">Français</div>
                        <div class="tab" data-lang="ar">العربية</div>
                    </div>
                    
                    <!-- English Fields -->
                    <div class="language-content active" id="lang-en">
                        <div class="field">
                            <label for="title"><?php echo $trans['title'] ?? 'Title'; ?> (English) *</label>
                            <input type="text" id="title" name="title" value="<?php echo h($tip['title']); ?>" required maxlength="100" placeholder="<?php echo $trans['enter_tip_title'] ?? 'Enter tip title'; ?>">
                            <div class="error-message" id="title_error"></div>
                        </div>

                        <div class="field">
                            <label for="description"><?php echo $trans['description'] ?? 'Description'; ?> (English) *</label>
                            <textarea id="description" name="description" required maxlength="500" rows="4" placeholder="<?php echo $trans['enter_tip_description'] ?? 'Enter tip description'; ?>"><?php echo h($tip['description']); ?></textarea>
                            <div class="error-message" id="description_error"></div>
                        </div>
                    </div>
                    
                    <!-- French Fields -->
                    <div class="language-content" id="lang-fr">
                        <div class="field">
                            <label for="title_fr"><?php echo $trans['title'] ?? 'Title'; ?> (Français)</label>
                            <input type="text" id="title_fr" name="title_fr" value="<?php echo h($tip['title_fr'] ?? ''); ?>" maxlength="100" placeholder="Entrez le titre du conseil">
                        </div>
                        
                        <div class="field">
                            <label for="description_fr"><?php echo $trans['description'] ?? 'Description'; ?> (Français)</label>
                            <textarea id="description_fr" name="description_fr" maxlength="500" rows="4" placeholder="Entrez la description du conseil"><?php echo h($tip['description_fr'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Arabic Fields -->
                    <div class="language-content" id="lang-ar">
                        <div class="field">
                            <label for="title_ar"><?php echo $trans['title'] ?? 'Title'; ?> (العربية)</label>
                            <input type="text" id="title_ar" name="title_ar" value="<?php echo h($tip['title_ar'] ?? ''); ?>" maxlength="100" placeholder="أدخل عنوان النصيحة" dir="rtl">
                        </div>
                        
                        <div class="field">
                            <label for="description_ar"><?php echo $trans['description'] ?? 'Description'; ?> (العربية)</label>
                            <textarea id="description_ar" name="description_ar" maxlength="500" rows="4" placeholder="أدخل وصف النصيحة" dir="rtl"><?php echo h($tip['description_ar'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <?php if (!empty($tip['images'])): ?>
                        <div class="field">
                            <label><?php echo $trans['current_image'] ?? 'Current Image'; ?></label>
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
                        <label for="image"><?php echo !empty($tip['images']) ? ($trans['replace_image_optional'] ?? 'Replace Image (Optional)') : ($trans['upload_image'] ?? 'Upload Image'); ?></label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="image" name="image" accept="image/x-png,image/gif,image/jpeg,image/jpg" class="upload">
                            <label for="image" class="file-upload-label">
                                <i class="bx bx-cloud-upload"></i>
                                <span><?php echo $trans['choose_file'] ?? 'Choose a file'; ?></span>
                            </label>
                            <div class="file-upload-name" id="file_name"><?php echo $trans['no_file_chosen'] ?? 'No file chosen'; ?></div>
                        </div>
                        <small class="form-text"><?php echo $trans['image_size_2mb_formats'] ?? 'Max size: 2MB'; ?> | <?php echo $trans['only_jpg_png_gif'] ?? 'Formats: JPG, PNG, GIF'; ?></small>
                        <div class="error-message" id="image_error"></div>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn btn-update">
                            <span class="btn-text"><?php echo $trans['update_daily_tip'] ?? 'Update Daily Tip'; ?></span>
                            <span class="btn-loader" style="display:none;">
                                <i class="bx bx-loader bx-spin"></i>
                            </span>
                        </button>
                        <a href="all_tips.php?lang=<?php echo $lang; ?>" class="btn btn-cancel"><?php echo $trans['cancel'] ?? 'Cancel'; ?></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Language Tabs */
    .language-tabs {
        display: flex;
        margin-bottom: 20px;
        border-bottom: 2px solid #eee;
        width: 100%;
        max-width: 750px;
    }

    .tab {
        padding: 12px 20px;
        cursor: pointer;
        background: #f8f9fa;
        border: 1px solid #ddd;
        border-bottom: none;
        margin-right: 5px;
        border-radius: 8px 8px 0 0;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .tab:hover {
        background: #e9ecef;
    }

    .tab.active {
        background: #fff;
        border-bottom: 2px solid #fff;
        color: #f97f4b;
        font-weight: 600;
    }

    .language-content {
        display: none;
        width: 100%;
    }

    .language-content.active {
        display: block;
    }

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

    /* RTL support for Arabic tab content */
    #lang-ar {
        direction: rtl;
        text-align: right;
    }

    #lang-ar input,
    #lang-ar textarea {
        text-align: right;
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

        .language-tabs {
            flex-wrap: wrap;
        }

        .tab {
            flex: 1;
            min-width: 80px;
            text-align: center;
            margin-right: 2px;
            font-size: 12px;
            padding: 8px 12px;
        }
    }
    
    <?php if ($lang === 'ar'): ?>
    /* Arabic RTL Styling */
    body {
        direction: rtl;
        text-align: right;
        font-family: 'Tajawal', 'Arial', sans-serif;
    }

    .sidebar {
        left: auto;
        right: 0;
        transition: all 0.5s ease;
    }

    .home-section {
        position: relative;
        left: auto;
        right: 260px;
        width: calc(100% - 260px);
        transition: all 0.5s ease;
    }

    .sidebar.close {
        width: 78px;
    }

    .sidebar.close ~ .home-section {
        right: 78px;
        left: auto;
        width: calc(100% - 78px);
    }

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
    }

    .tip-form .field {
        text-align: right;
    }

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

    .box {
        text-align: right;
    }
    <?php endif; ?>
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Language tab switching
        const tabs = document.querySelectorAll('.tab');
        const contents = document.querySelectorAll('.language-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const lang = this.getAttribute('data-lang');
                
                // Remove active class from all tabs and contents
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                this.classList.add('active');
                document.getElementById('lang-' + lang).classList.add('active');
            });
        });

        // File upload name display
        document.getElementById('image').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : '<?php echo $trans['no_file_chosen'] ?? 'No file chosen'; ?>';
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
                    // Show the first tab with errors
                    tabs[0].click();
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