<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'lang.php'; // Include the language file

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', $trans['please_log_in']);
}

// Check if service ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('all_services.php?lang=' . $lang, $trans['invalid_service_id']);
}

$service_id = sanitize_input($_GET['id']);
$service = null;

// Fetch service details
try {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE service_id = :service_id");
    $stmt->bindParam(':service_id', $service_id);
    $stmt->execute();
    $service = $stmt->fetch();
    
    if (!$service) {
        redirect('all_services.php?lang=' . $lang, $trans['service_not_found']);
    }
} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    redirect('all_services.php?lang=' . $lang, $trans['database_error']);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token'])) {
        set_flash_message('error', $trans['invalid_form_submission']);
        header('Location: edit_service.php?id=' . $service_id . '&lang=' . $lang);
        exit();
    }

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        set_flash_message('error', $trans['invalid_form_submission']);
        header('Location: edit_service.php?id=' . $service_id . '&lang=' . $lang);
        exit();
    }
    
    // Sanitize input for all languages
    $title = sanitize_input($_POST['title'] ?? '');
    $description = sanitize_input($_POST['desc'] ?? '');
    $title_fr = sanitize_input($_POST['title_fr'] ?? '');
    $description_fr = sanitize_input($_POST['desc_fr'] ?? '');
    $title_ar = sanitize_input($_POST['title_ar'] ?? '');
    $description_ar = sanitize_input($_POST['desc_ar'] ?? '');
    
    // Validate input - at least English fields are required
    if (empty($title) || empty($description)) {
        set_flash_message('error', $trans['all_fields_required']);
        header('Location: edit_service.php?id=' . $service_id . '&lang=' . $lang);
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
                header('Location: edit_service.php?id=' . $service_id . '&lang=' . $lang);
                exit();
            }
            
            if ($_FILES['image']['size'] > $max_size) {
                set_flash_message('error', $trans['image_size_less_than_2mb']);
                header('Location: edit_service.php?id=' . $service_id . '&lang=' . $lang);
                exit();
            }
            
            // Generate a unique filename
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('service_') . '.' . $file_extension;
            $upload_path = '../public/images/' . $filename;
            
            // Create directory if it doesn't exist
            $dir = '../public/images';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Move the uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if it exists
                if (!empty($service['images'])) {
                    $old_image_path = '../public/images/' . $service['images'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                
                // Update database with new image and all language fields
                $stmt = $pdo->prepare("UPDATE services SET title = :title, description = :description, title_fr = :title_fr, description_fr = :description_fr, title_ar = :title_ar, description_ar = :description_ar, images = :images WHERE service_id = :service_id");
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':title_fr', $title_fr);
                $stmt->bindParam(':description_fr', $description_fr);
                $stmt->bindParam(':title_ar', $title_ar);
                $stmt->bindParam(':description_ar', $description_ar);
                $stmt->bindParam(':images', $filename);
                $stmt->bindParam(':service_id', $service_id);
            } else {
                set_flash_message('error', $trans['failed_upload_image']);
                header('Location: edit_service.php?id=' . $service_id . '&lang=' . $lang);
                exit();
            }
        } else {
            // Update only title, description and language fields
            $stmt = $pdo->prepare("UPDATE services SET title = :title, description = :description, title_fr = :title_fr, description_fr = :description_fr, title_ar = :title_ar, description_ar = :description_ar WHERE service_id = :service_id");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':title_fr', $title_fr);
            $stmt->bindParam(':description_fr', $description_fr);
            $stmt->bindParam(':title_ar', $title_ar);
            $stmt->bindParam(':description_ar', $description_ar);
            $stmt->bindParam(':service_id', $service_id);
        }
        
        if ($stmt->execute()) {
            set_flash_message('success', $trans['service_updated_successfully']);
            header('Location: all_services.php?lang=' . $lang);
            exit();
        } else {
            set_flash_message('error', $trans['failed_update_service']);
        }
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        set_flash_message('error', $trans['database_error']);
    }
    
    // Redirect back to edit page
    header('Location: edit_service.php?id=' . $service_id . '&lang=' . $lang);
    exit();
}

// Include header
include_once 'includes/header.php';
?>

<div class="home-content">
    <div class="sales-boxes">
        <div class="recent-sales box">
            <div class="title"><?php echo $trans['edit_service']; ?></div>
            
            <?php if (has_flash_message('error')): ?>
                <div class="alert alert-danger"><?php echo get_flash_message('error'); ?></div>
            <?php endif; ?>
            
            <?php if (has_flash_message('success')): ?>
                <div class="alert alert-success"><?php echo get_flash_message('success'); ?></div>
            <?php endif; ?>
            
            <div class="sales-details">
                <form method="POST" action="edit_service.php?id=<?php echo h($service_id); ?>&lang=<?php echo $lang; ?>" enctype="multipart/form-data" class="service-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    
                    <!-- Language Tabs -->
                    <div class="language-tabs">
                        <div class="tab active" data-lang="en">English</div>
                        <div class="tab" data-lang="fr">Français</div>
                        <div class="tab" data-lang="ar">العربية</div>
                    </div>
                    
                    <!-- English Fields -->
                    <div class="language-content active" id="lang-en">
                        <div class="field">
                            <label for="title"><?php echo $trans['title']; ?> (English) *</label>
                            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars_decode(h($service['title'], ENT_QUOTES)); ?>" required maxlength="100" placeholder="<?php echo $trans['enter_service_title']; ?>">
                            <div class="error-message" id="title_error"></div>
                        </div>
                        
                        <div class="field">
                            <label for="desc"><?php echo $trans['description']; ?> (English) *</label>
                            <textarea id="desc" name="desc" required maxlength="500" rows="4" placeholder="<?php echo $trans['enter_service_description']; ?>"><?php echo htmlspecialchars_decode(h($service['description'], ENT_QUOTES)); ?></textarea>
                            <div class="error-message" id="desc_error"></div>
                        </div>
                    </div>
                    
                    <!-- French Fields -->
                    <div class="language-content" id="lang-fr">
                        <div class="field">
                            <label for="title_fr"><?php echo $trans['title']; ?> (Français)</label>
                            <input type="text" id="title_fr" name="title_fr" value="<?php echo htmlspecialchars_decode(h($service['title_fr'] ?? '', ENT_QUOTES)); ?>" maxlength="100" placeholder="Entrez le titre du service">
                        </div>
                        
                        <div class="field">
                            <label for="desc_fr"><?php echo $trans['description']; ?> (Français)</label>
                            <textarea id="desc_fr" name="desc_fr" maxlength="500" rows="4" placeholder="Entrez la description du service"><?php echo htmlspecialchars_decode(h($service['description_fr'] ?? '', ENT_QUOTES)); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Arabic Fields -->
                    <div class="language-content" id="lang-ar">
                        <div class="field">
                            <label for="title_ar"><?php echo $trans['title']; ?> (العربية)</label>
                            <input type="text" id="title_ar" name="title_ar" value="<?php echo htmlspecialchars_decode(h($service['title_ar'] ?? '', ENT_QUOTES)); ?>" maxlength="100" placeholder="أدخل عنوان الخدمة" dir="rtl">
                        </div>
                        
                        <div class="field">
                            <label for="desc_ar"><?php echo $trans['description']; ?> (العربية)</label>
                            <textarea id="desc_ar" name="desc_ar" maxlength="500" rows="4" placeholder="أدخل وصف الخدمة" dir="rtl"><?php echo htmlspecialchars_decode(h($service['description_ar'] ?? '', ENT_QUOTES)); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Image Field -->
                    <div class="field">
                        <label for="image"><?php echo !empty($service['images']) ? $trans['replace_image_optional'] : $trans['upload_image']; ?></label>
                        <?php if (!empty($service['images'])): ?>
                            <div class="current-image">
                                <img src="../public/images/<?php echo htmlspecialchars_decode(h($service['images'], ENT_QUOTES)); ?>" alt="<?php echo $trans['current_image']; ?>" class="service-thumbnail" onerror="this.style.display='none'">
                                <p><?php echo $trans['current_image']; ?>: <?php echo htmlspecialchars_decode(h($service['images'], ENT_QUOTES)); ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="file-upload-wrapper">
                            <input type="file" id="image" name="image" accept="image/x-png,image/gif,image/jpeg,image/jpg" class="upload">
                            <label for="image" class="file-upload-label">
                                <i class="bx bx-cloud-upload"></i>
                                <span><?php echo $trans['choose_file']; ?></span>
                            </label>
                            <div class="file-upload-name" id="file_name"><?php echo $trans['no_file_chosen']; ?></div>
                        </div>
                        <small class="form-text"><?php echo $trans['image_size_2mb_formats']; ?> | <?php echo $trans['only_jpg_png_gif']; ?></small>
                        <div class="error-message" id="image_error"></div>
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" class="btn btn-update">
                            <span class="btn-text"><?php echo $trans['update_service']; ?></span>
                            <span class="btn-loader" style="display:none;">
                                <i class="bx bx-loader bx-spin"></i>
                            </span>
                        </button>
                        <a href="all_services.php?lang=<?php echo $lang; ?>" class="btn btn-cancel"><?php echo $trans['cancel']; ?></a>
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
        color: #ff6c40e4;
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
    .service-form {
        margin-top: 30px;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 0 20px 20px;
    }

    .service-form .field {
        margin-bottom: 15px;
        width: 100%;
        max-width: 750px;
    }

    .service-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #444;
        font-size: 14px;
    }

    .service-form input[type="text"],
    .service-form textarea {
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

    .service-form textarea {
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

    .service-form small.form-text {
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

    .service-thumbnail {
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
        background-color: #ff6c40e4;
        color: white;
    }

    .btn-cancel {
        background-color: #95a5a6;
        color: white;
        text-decoration: none;
    }

    .btn-update:hover {
        background-color: #e65c30;
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
        .service-form {
            padding: 0 15px 15px;
        }

        .service-form input[type="text"],
        .service-form textarea {
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

<script>
    // Display the popup if it has content
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
            const fileName = this.files[0] ? this.files[0].name : '<?php echo $trans['no_file_chosen']; ?>';
            document.getElementById('file_name').textContent = fileName;
        });

        // Form validation
        const form = document.querySelector('.service-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Clear previous errors
                this.querySelectorAll('.error-message').forEach(el => {
                    el.style.display = 'none';
                });

                // Validate title (English is required)
                const title = this.querySelector('#title');
                if (title.value.trim().length < 3) {
                    document.getElementById('title_error').textContent = '<?php echo $trans['title_min_length'] ?? 'Title must be at least 3 characters long.'; ?>';
                    document.getElementById('title_error').style.display = 'block';
                    isValid = false;
                }

                // Validate description (English is required)
                const desc = this.querySelector('#desc');
                if (desc.value.trim().length < 10) {
                    document.getElementById('desc_error').textContent = '<?php echo $trans['desc_min_length'] ?? 'Description must be at least 10 characters long.'; ?>';
                    document.getElementById('desc_error').style.display = 'block';
                    isValid = false;
                }

                // Validate image if new one is selected
                const image = this.querySelector('#image');
                if (image.files.length > 0) {
                    const file = image.files[0];
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    const maxSize = 2 * 1024 * 1024; // 2MB

                    if (!allowedTypes.includes(file.type)) {
                        document.getElementById('image_error').textContent = '<?php echo $trans['invalid_file_type'] ?? 'Invalid file type. Only JPG, PNG and GIF are allowed.'; ?>';
                        document.getElementById('image_error').style.display = 'block';
                        isValid = false;
                    }

                    if (file.size > maxSize) {
                        document.getElementById('image_error').textContent = '<?php echo $trans['file_size_limit'] ?? 'File size must be less than 2MB.'; ?>';
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
        document.querySelectorAll('.service-thumbnail').forEach(img => {
            img.addEventListener('error', function() {
                this.style.display = 'none';
                // Create a placeholder
                const placeholder = document.createElement('span');
                placeholder.className = 'no-image';
                placeholder.textContent = '<?php echo $trans['image_not_available'] ?? 'Image not available'; ?>';
                this.parentNode.insertBefore(placeholder, this);
            });
        });
    });
</script>

<?php include_once 'includes/footer.php'; ?>