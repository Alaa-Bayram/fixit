<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Include language file
require_once 'lang.php';

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', $trans['please_log_in']);
}

//include articles sum
$total_articles = get_total_articles($pdo);

// Include articles data
require_once 'includes/articles_data.php';

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
                <div class="box-topic"><?php echo $trans['total_articles']; ?></div>
                <div class="number"><?php echo number_format($total_articles); ?></div>
                <div class="indicator">
                    <i class="bx bx-up-arrow-alt"></i>
                    <span class="text"><?php echo $trans['up_from_yesterday']; ?></span>
                </div>
            </div>
            <i class="bx bx-news cart three"></i> <!-- News icon -->
        </div>
    </div>

    <div class="sales-boxes">
        <div class="recent-sales box">
            <div class="title"><?php echo $trans['add_article']; ?></div>
            <div class="sales-details">
                <form method="POST" action="includes/add_article.php?lang=<?php echo $lang; ?>" enctype="multipart/form-data" class="article-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    <input type="hidden" name="lang" value="<?php echo $lang; ?>">
                    <div class="field">
                        <label for="title"><?php echo $trans['title']; ?></label>
                        <input type="text" id="title" name="title" required maxlength="100" placeholder="<?php echo $trans['enter_article_title']; ?>">
                        <div class="error-message" id="title_error"></div>
                    </div>
                    <div class="field">
                        <label for="desc"><?php echo $trans['description']; ?></label>
                        <textarea id="desc" name="desc" required maxlength="500" rows="3" placeholder="<?php echo $trans['enter_article_description']; ?>"></textarea>
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
                    <div class="field">
                        <label for="sec_title"><?php echo $trans['secondary_title']; ?></label>
                        <input type="text" id="sec_title" name="sec_title" required maxlength="100" placeholder="<?php echo $trans['enter_secondary_title']; ?>">
                        <div class="error-message" id="sec_title_error"></div>
                    </div>
                    <div class="field">
                        <label for="content1"><?php echo $trans['content']; ?></label>
                        <textarea id="content1" name="content1" required maxlength="1000" rows="4" placeholder="<?php echo $trans['enter_main_content']; ?>"></textarea>
                        <div class="error-message" id="content1_error"></div>
                    </div>
                    <div class="field">
                        <label for="tert_title"><?php echo $trans['tertiary_title']; ?></label>
                        <input type="text" id="tert_title" name="tert_title" required maxlength="100" placeholder="<?php echo $trans['enter_tertiary_title']; ?>">
                        <div class="error-message" id="tert_title_error"></div>
                    </div>
                    <div class="field">
                        <label for="content2"><?php echo $trans['content']; ?></label>
                        <textarea id="content2" name="content2" required maxlength="1000" rows="4" placeholder="<?php echo $trans['enter_additional_content']; ?>"></textarea>
                        <div class="error-message" id="content2_error"></div>
                    </div>
                    <div id="popup" class="popup">
                        <?php if (!empty($popup_message)): ?>
                            <?php echo h($popup_message); ?>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-article">
                        <span class="btn-text"><?php echo $trans['add_article']; ?></span>
                        <span class="btn-loader" style="display:none;">
                            <i class="bx bx-loader bx-spin"></i>
                        </span>
                    </button>
                </form>
            </div>
        </div>

        <div class="top-sales box">
            <div class="title"><?php echo $trans['latest_articles']; ?></div>
            <ul class="top-sales-details">
                <?php if (empty($articles)): ?>
                    <li class="no-data"><?php echo $trans['no_articles_available']; ?></li>
                <?php else: ?>
                    <?php foreach ($articles as $article): ?>
                        <?php
                        // Get localized content based on language
                        $display_title = $article['title']; // Default
                        $display_description = $article['description']; // Default
                        $display_sec_title = $article['sec_title']; // Default
                        $display_content1 = $article['content1']; // Default

                        if ($lang === 'fr') {
                            if (!empty($article['title_fr'])) $display_title = $article['title_fr'];
                            if (!empty($article['description_fr'])) $display_description = $article['description_fr'];
                            if (!empty($article['sec_title_fr'])) $display_sec_title = $article['sec_title_fr'];
                            if (!empty($article['content1_fr'])) $display_content1 = $article['content1_fr'];
                        } elseif ($lang === 'ar') {
                            if (!empty($article['title_ar'])) $display_title = $article['title_ar'];
                            if (!empty($article['description_ar'])) $display_description = $article['description_ar'];
                            if (!empty($article['sec_title_ar'])) $display_sec_title = $article['sec_title_ar'];
                            if (!empty($article['content1_ar'])) $display_content1 = $article['content1_ar'];
                        }
                        ?>
                        <li style="margin-bottom: 30px;">
                            <a href="#?id=<?php echo $article['id']; ?>&lang=<?php echo $lang; ?>">
                                <img src="../public/images/<?php echo h($article['images']); ?>" alt="<?php echo h($display_title); ?>" class="article-thumbnail" onerror="this.style.display='none'">
                            </a>
                            <div class="article-info">
                                <span class="product" style="color:#f97f4b"><?php echo h($display_title); ?></span>
                                <br>
                                <span class="details" style="font-size: 15px;font-style: italic;color:#3a3a3ae1"><?php echo h($display_description); ?></span>
                                <br>
                                <span class="product" style="color:gold"><?php echo h($display_sec_title); ?></span>
                                <br>
                                <span class="details" style="font-size: 15px;font-style: italic;color:#3a3a3ae1"><?php echo h($display_content1); ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
            <div class="button">
                <a href="all_articles.php?lang=<?php echo $lang; ?>"><?php echo $trans['see_all']; ?></a>
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

/* Fix article items layout */
.top-sales-details li {
    padding: 10px 0 10px 20px;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    flex-direction: row-reverse;
}

/* Article info alignment */
.article-info {
    margin-left: 15px;
    margin-right: 0;
    text-align: right;
    flex: 1;
}

/* Form field alignment */
.article-form .field {
    text-align: right;
}

/* Input fields styling */
.article-form input[type="text"],
.article-form textarea {
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
    
    .article-info {
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

/* Article thumbnail container */
.top-sales-details li a {
    order: 2;
}

.article-info {
    order: 1;
}

/* Button loader fix for RTL */
.btn-loader {
    margin-left: 0;
    margin-right: 8px;
}
<?php endif; ?>

    /* Article form styling */
    .article-form {
        margin-top: 30px;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 0 20px 20px;
    }

    .article-form .field {
        margin-bottom: 15px;
        width: 100%;
        max-width: 750px;
    }

    .article-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #444;
        font-size: 14px;
    }

    .article-form input[type="text"],
    .article-form textarea {
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

    .article-form input[type="text"]:focus,
    .article-form textarea:focus {
        border-color: #ff6c40e4;
        box-shadow: 0 0 0 2px rgba(255, 108, 64, 0.2);
        outline: none;
    }

    .article-form textarea {
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

    .article-form small.form-text {
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
    .btn-article {
        padding: 12px 24px;
        background-color: #ff6c40e4;
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

    .btn-article:hover {
        background-color: #d35400;
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

    /* Article thumbnail */
    .article-thumbnail {
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
        .article-form {
            padding: 0 15px 15px;
        }

        .article-form input[type="text"],
        .article-form textarea {
            padding: 10px 12px;
            font-size: 16px;
        }

        .btn-article {
            width: 100%;
            padding: 12px 20px;
            font-size: 16px;
        }

        .article-thumbnail {
            width: 100%;
            max-width: 200px;
            height: auto;
        }
    }
</style>

<script>
    // Translation variables for JavaScript - Fixed to match services.php pattern
    const translations = {
        'title_min_length': '<?php echo addslashes($trans['title_min_length'] ?? 'Title must be at least 3 characters long'); ?>',
        'desc_min_length': '<?php echo addslashes($trans['desc_min_length'] ?? 'Description must be at least 10 characters long'); ?>',
        'please_select_image': '<?php echo addslashes($trans['please_select_image'] ?? 'Please select an image'); ?>',
        'file_size_limit': '<?php echo addslashes($trans['file_size_limit'] ?? 'File size must be less than 2MB'); ?>',
        'invalid_file_type': '<?php echo addslashes($trans['invalid_file_type'] ?? 'Please select a valid image file (JPG, PNG, or GIF)'); ?>',
        'no_image': '<?php echo addslashes($trans['no_image'] ?? 'No Image'); ?>',
        'no_file_chosen': '<?php echo addslashes($trans['no_file_chosen'] ?? 'No file chosen'); ?>',
        'content_min_length': '<?php echo addslashes($trans['content_min_length'] ?? 'Content must be at least 20 characters long'); ?>',
        'sec_title_min_length': '<?php echo addslashes($trans['sec_title_min_length'] ?? 'Secondary title must be at least 3 characters long'); ?>',
        'tert_title_min_length': '<?php echo addslashes($trans['tert_title_min_length'] ?? 'Tertiary title must be at least 3 characters long'); ?>'
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
        const form = document.querySelector('.article-form');
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

                // Validate secondary title
                const secTitle = this.querySelector('#sec_title');
                if (secTitle.value.trim().length < 3) {
                    document.getElementById('sec_title_error').textContent = translations.sec_title_min_length;
                    document.getElementById('sec_title_error').style.display = 'block';
                    isValid = false;
                }

                // Validate tertiary title
                const tertTitle = this.querySelector('#tert_title');
                if (tertTitle.value.trim().length < 3) {
                    document.getElementById('tert_title_error').textContent = translations.tert_title_min_length;
                    document.getElementById('tert_title_error').style.display = 'block';
                    isValid = false;
                }

                // Validate content1
                const content1 = this.querySelector('#content1');
                if (content1.value.trim().length < 20) {
                    document.getElementById('content1_error').textContent = translations.content_min_length;
                    document.getElementById('content1_error').style.display = 'block';
                    isValid = false;
                }

                // Validate content2
                const content2 = this.querySelector('#content2');
                if (content2.value.trim().length < 20) {
                    document.getElementById('content2_error').textContent = translations.content_min_length;
                    document.getElementById('content2_error').style.display = 'block';
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
                const btn = this.querySelector('.btn-article');
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
        document.querySelectorAll('.article-thumbnail').forEach(img => {
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