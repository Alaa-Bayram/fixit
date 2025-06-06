<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Include language file
require_once 'lang.php';

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', $trans['please_log_in'] ?? 'Please log in to access the dashboard');
}

// Include services data
require_once 'includes/services_data.php';

// Check for success or error messages
$popup_message = '';
if (isset($_SESSION['error_message'])) {
    $popup_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

if (isset($_SESSION['success_message'])) {
    $popup_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Include header
include_once 'includes/header.php';
?>

<div class="home-content">
    <div class="overview-boxes">
        <div class="box">
            <div class="right-side">
                <div class="box-topic"><?php echo $trans['total_services']; ?></div>
                <div class="number"><?php echo number_format($total_services); ?></div>
                <div class="indicator">
                    <i class="bx bx-up-arrow-alt"></i>
                    <span class="text"><?php echo $trans['up_from_yesterday']; ?></span>
                </div>
            </div>
            <i class="bx bxs-wrench cart three"></i> <!-- Wrench icon -->
        </div>
    </div>

    <div class="sales-boxes">
        <div class="recent-sales box">
            <div class="title"><?php echo $trans['add_service']; ?></div>
            <div class="sales-details">
                <form method="POST" action="includes/add_services.php?lang=<?php echo $lang; ?>" enctype="multipart/form-data" class="service-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    <input type="hidden" name="lang" value="<?php echo $lang; ?>">
                    <div class="field">
                        <label for="title"><?php echo $trans['title']; ?></label>
                        <input type="text" id="title" name="title" required maxlength="100" placeholder="<?php echo $trans['enter_service_title']; ?>">
                        <div class="error-message" id="title_error"></div>
                    </div>
                    <div class="field">
                        <label for="desc"><?php echo $trans['description']; ?></label>
                        <input type="text" id="desc" name="desc" required maxlength="500" placeholder="<?php echo $trans['enter_service_description']; ?>">
                        <div class="error-message" id="desc_error"></div>
                    </div>
                    <div class="field">
                        <label for="image"><?php echo $trans['image']; ?></label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="image" name="image" accept="image/x-png,image/gif,image/jpeg,image/jpg" required class="upload">
                            <label for="image" class="file-upload-label">
                                <i class="bx bx-cloud-upload"></i>
                                <span><?php echo $trans['choose_file']; ?></span>
                            </label>
                            <div class="file-upload-name" id="file_name"><?php echo $trans['no_file_chosen']; ?></div>
                        </div>
                        <small class="form-text"><?php echo $trans['file_upload_help']; ?></small>
                        <div class="error-message" id="image_error"></div>
                    </div>
                    <div id="popup" class="popup">
                        <?php if (!empty($popup_message)): ?>
                            <?php echo h($popup_message); ?>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-service">
                        <span class="btn-text"><?php echo $trans['add_service']; ?></span>
                        <span class="btn-loader" style="display:none;">
                            <i class="bx bx-loader bx-spin"></i>
                        </span>
                    </button>
                </form>
            </div>
        </div>

        <div class="top-sales box">
            <div class="title"><?php echo $trans['all_services']; ?></div>
            <ul class="top-sales-details">
                <?php if (empty($serv)): ?>
                    <li class="no-data"><?php echo $trans['no_services_available']; ?></li>
                <?php else: ?>
                    <?php foreach ($serv as $service): ?>
                        <?php
                        // Get localized content based on language
                        $display_title = $service['title']; // Default
                        $display_description = $service['description']; // Default

                        if ($lang === 'fr' && !empty($service['title_fr'])) {
                            $display_title = $service['title_fr'];
                        } elseif ($lang === 'ar' && !empty($service['title_ar'])) {
                            $display_title = $service['title_ar'];
                        }

                        if ($lang === 'fr' && !empty($service['description_fr'])) {
                            $display_description = $service['description_fr'];
                        } elseif ($lang === 'ar' && !empty($service['description_ar'])) {
                            $display_description = $service['description_ar'];
                        }
                        ?>
                        <li style="margin-bottom: 30px;">
                            <a href="#?id=<?php echo $service['id']; ?>&lang=<?php echo $lang; ?>">
                                <img src="../public/images/<?php echo h($service['images']); ?>" alt="<?php echo h($display_title); ?>" class="service-thumbnail" onerror="this.style.display='none'">
                            </a>
                            <div class="service-info">
                                <span class="product" style="color:#f97f4b"><?php echo h($display_title); ?></span>
                                <br>
                                <span class="details" style="font-size: 15px;font-style: italic;color:#3a3a3ae1"><?php echo h($display_description); ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
            <div class="button">
                <a href="all_services.php?lang=<?php echo $lang; ?>"><?php echo $trans['see_all']; ?></a>
            </div>
        </div>
    </div>
</div>

<style>
    /* Main container styling */
    .home-content {
        padding: 20px;
        transition: all 0.3s ease;
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

    /* Service form styling */
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

    .service-form input[type="text"] {
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

    .service-form input[type="text"]:focus {
        border-color: #f97f4b;
        box-shadow: 0 0 0 2px rgba(249, 127, 75, 0.2);
        outline: none;
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

    /* Button styling */
    .btn-service {
        padding: 12px 24px;
        background-color: #f97f4b;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
        margin-top: 15px;
        min-width: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .btn-service:hover {
        background-color: #e76b3c;
        transform: translateY(-2px);
    }

    .btn-loader {
        margin-left: 8px;
    }

    /* Popup styling */
    .popup {
        color: var(--success, #27ae60);
        margin-top: 10px;
        padding: 12px 15px;
        border-radius: 8px;
        display: none;
        background-color: rgba(39, 174, 96, 0.1);
        border-left: 4px solid var(--success, #27ae60);
        width: 100%;
        text-align: center;
        animation: fadeInOut 3s ease-in-out;
    }

    /* Service thumbnail */
    .service-thumbnail {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Animation */
    @keyframes fadeInOut {
        0% {
            opacity: 0;
            transform: translateY(-10px);
        }

        10% {
            opacity: 1;
            transform: translateY(0);
        }

        90% {
            opacity: 1;
            transform: translateY(0);
        }

        100% {
            opacity: 0;
            transform: translateY(-10px);
        }
    }

    /* Responsive styles */
    @media (max-width: 768px) {
        .service-form {
            padding: 0 15px 15px;
        }

        .service-form input[type="text"] {
            padding: 10px 12px;
            font-size: 16px;
        }

        .btn-service {
            width: 100%;
            padding: 12px 20px;
            font-size: 16px;
        }

        .service-thumbnail {
            width: 100%;
            max-width: 200px;
            height: auto;
        }
    }
</style>

<script>
    // Translations for JavaScript
    const translations = {
        'title_min_length': '<?php echo addslashes($trans['title_min_length'] ?? 'Title must be at least 3 characters long'); ?>',
        'desc_min_length': '<?php echo addslashes($trans['desc_min_length'] ?? 'Description must be at least 10 characters long'); ?>',
        'please_select_image': '<?php echo addslashes($trans['please_select_image'] ?? 'Please select an image'); ?>',
        'file_size_limit': '<?php echo addslashes($trans['file_size_limit'] ?? 'File size must be less than 2MB'); ?>',
        'invalid_file_type': '<?php echo addslashes($trans['invalid_file_type'] ?? 'Please select a valid image file (JPG, PNG, or GIF)'); ?>',
        'no_image': '<?php echo addslashes($trans['no_image'] ?? 'No Image'); ?>',
        'no_file_chosen': '<?php echo addslashes($trans['no_file_chosen'] ?? 'No file chosen'); ?>'
    };

    // Display the popup if it has content
    document.addEventListener('DOMContentLoaded', function() {
        // Popup handling
        var popup = document.getElementById('popup');
        if (popup && popup.innerHTML.trim() !== '') {
            popup.style.display = 'block';
            setTimeout(function() {
                popup.style.display = 'none';
            }, 3000);
        }

        // File upload name display
        document.getElementById('image').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : translations.no_file_chosen;
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

                // Validate title
                const title = this.querySelector('#title');
                if (title.value.trim().length < 3) {
                    document.getElementById('title_error').textContent = translations.title_min_length;
                    document.getElementById('title_error').style.display = 'block';
                    isValid = false;
                }

                // Validate description
                const desc = this.querySelector('#desc');
                if (desc.value.trim().length < 10) {
                    document.getElementById('desc_error').textContent = translations.desc_min_length;
                    document.getElementById('desc_error').style.display = 'block';
                    isValid = false;
                }

                // Validate image
                const image = this.querySelector('#image');
                if (image.files.length === 0) {
                    document.getElementById('image_error').textContent = translations.please_select_image;
                    document.getElementById('image_error').style.display = 'block';
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    return false;
                }

                // Show loading state
                const btn = this.querySelector('.btn-service');
                if (btn) {
                    const btnText = btn.querySelector('.btn-text');
                    const btnLoader = btn.querySelector('.btn-loader');
                    if (btnText) btnText.style.display = 'none';
                    if (btnLoader) btnLoader.style.display = 'block';
                    btn.disabled = true;
                }
            });
        }

        // File upload validation
        document.getElementById('image').addEventListener('change', function() {
            const file = this.files[0];
            const errorEl = document.getElementById('image_error');

            if (file) {
                // Check file size (2MB limit)
                if (file.size > 2 * 1024 * 1024) {
                    errorEl.textContent = translations.file_size_limit;
                    errorEl.style.display = 'block';
                    this.value = '';
                    return;
                }

                // Check file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    errorEl.textContent = translations.invalid_file_type;
                    errorEl.style.display = 'block';
                    this.value = '';
                    return;
                }

                // Hide error if valid
                errorEl.style.display = 'none';
            }
        });

        // Image error handling for thumbnails
        document.querySelectorAll('.service-thumbnail').forEach(img => {
            img.addEventListener('error', function() {
                this.style.display = 'none';
                // Create a placeholder
                const placeholder = document.createElement('span');
                placeholder.className = 'no-image';
                placeholder.textContent = translations.no_image;
                this.parentNode.insertBefore(placeholder, this);
            });
        });
    });
</script>

<?php include_once 'includes/footer.php'; ?>