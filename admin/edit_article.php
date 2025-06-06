<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'lang.php'; // Include the language file

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', $trans['please_log_in']);
}

// Check if article ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('all_articles.php?lang=' . $lang, $trans['invalid_article_id']);
}

$article_id = sanitize_input($_GET['id']);
$article = null;

// Fetch article details
try {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE article_id = :article_id");
    $stmt->bindParam(':article_id', $article_id);
    $stmt->execute();
    $article = $stmt->fetch();
    
    if (!$article) {
        redirect('all_articles.php?lang=' . $lang, $trans['article_not_found']);
    }
} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    redirect('all_articles.php?lang=' . $lang, $trans['database_error']);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token'])) {
        set_flash_message('error', $trans['invalid_form_submission']);
        header('Location: edit_article.php?id=' . $article_id . '&lang=' . $lang);
        exit();
    }

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        set_flash_message('error', $trans['invalid_form_submission']);
        header('Location: edit_article.php?id=' . $article_id . '&lang=' . $lang);
        exit();
    }
    
    // Sanitize input for all languages
    $title = sanitize_input($_POST['title'] ?? '');
    $description = sanitize_input($_POST['desc'] ?? '');
    $sec_title = sanitize_input($_POST['sec_title'] ?? '');
    $content1 = sanitize_input($_POST['content1'] ?? '');
    $tert_title = sanitize_input($_POST['tert_title'] ?? '');
    $content2 = sanitize_input($_POST['content2'] ?? '');
    
    // French fields
    $title_fr = sanitize_input($_POST['title_fr'] ?? '');
    $description_fr = sanitize_input($_POST['desc_fr'] ?? '');
    $sec_title_fr = sanitize_input($_POST['sec_title_fr'] ?? '');
    $content1_fr = sanitize_input($_POST['content1_fr'] ?? '');
    $tert_title_fr = sanitize_input($_POST['tert_title_fr'] ?? '');
    $content2_fr = sanitize_input($_POST['content2_fr'] ?? '');
    
    // Arabic fields
    $title_ar = sanitize_input($_POST['title_ar'] ?? '');
    $description_ar = sanitize_input($_POST['desc_ar'] ?? '');
    $sec_title_ar = sanitize_input($_POST['sec_title_ar'] ?? '');
    $content1_ar = sanitize_input($_POST['content1_ar'] ?? '');
    $tert_title_ar = sanitize_input($_POST['tert_title_ar'] ?? '');
    $content2_ar = sanitize_input($_POST['content2_ar'] ?? '');
    
    // Validate input - at least English fields are required
    if (empty($title) || empty($description) || empty($sec_title) || empty($content1)) {
        set_flash_message('error', $trans['all_fields_required']);
        header('Location: edit_article.php?id=' . $article_id . '&lang=' . $lang);
        exit();
    }
    
    try {
        // Initialize image filename with current value
        $image_filename = $article['images'];
        
        // Check if a new image was uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            // Validate file type and size
            if (!in_array($_FILES['image']['type'], $allowed_types)) {
                set_flash_message('error', $trans['only_jpg_png_gif_allowed']);
                header('Location: edit_article.php?id=' . $article_id . '&lang=' . $lang);
                exit();
            }
            
            if ($_FILES['image']['size'] > $max_size) {
                set_flash_message('error', $trans['image_size_less_than_2mb']);
                header('Location: edit_article.php?id=' . $article_id . '&lang=' . $lang);
                exit();
            }
            
            // Generate a unique filename
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_filename = uniqid('article_') . '.' . $file_extension;
            $upload_path = '../public/images/' . $image_filename;
            
            // Create directory if it doesn't exist
            $dir = '../public/images';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Move the uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if it exists
                if (!empty($article['images'])) {
                    $old_image_path = '../public/images/' . $article['images'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
            } else {
                set_flash_message('error', $trans['failed_upload_image']);
                header('Location: edit_article.php?id=' . $article_id . '&lang=' . $lang);
                exit();
            }
        }
        
        // Update database with all fields including language translations
        $stmt = $pdo->prepare("UPDATE articles SET 
            title = :title, 
            description = :description, 
            sec_title = :sec_title, 
            content1 = :content1, 
            tert_title = :tert_title, 
            content2 = :content2, 
            title_fr = :title_fr,
            description_fr = :description_fr,
            sec_title_fr = :sec_title_fr,
            content1_fr = :content1_fr,
            tert_title_fr = :tert_title_fr,
            content2_fr = :content2_fr,
            title_ar = :title_ar,
            description_ar = :description_ar,
            sec_title_ar = :sec_title_ar,
            content1_ar = :content1_ar,
            tert_title_ar = :tert_title_ar,
            content2_ar = :content2_ar,
            images = :images 
            WHERE article_id = :article_id");
        
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':sec_title', $sec_title);
        $stmt->bindParam(':content1', $content1);
        $stmt->bindParam(':tert_title', $tert_title);
        $stmt->bindParam(':content2', $content2);
        $stmt->bindParam(':title_fr', $title_fr);
        $stmt->bindParam(':description_fr', $description_fr);
        $stmt->bindParam(':sec_title_fr', $sec_title_fr);
        $stmt->bindParam(':content1_fr', $content1_fr);
        $stmt->bindParam(':tert_title_fr', $tert_title_fr);
        $stmt->bindParam(':content2_fr', $content2_fr);
        $stmt->bindParam(':title_ar', $title_ar);
        $stmt->bindParam(':description_ar', $description_ar);
        $stmt->bindParam(':sec_title_ar', $sec_title_ar);
        $stmt->bindParam(':content1_ar', $content1_ar);
        $stmt->bindParam(':tert_title_ar', $tert_title_ar);
        $stmt->bindParam(':content2_ar', $content2_ar);
        $stmt->bindParam(':images', $image_filename);
        $stmt->bindParam(':article_id', $article_id);
        
        if ($stmt->execute()) {
            set_flash_message('success', $trans['article_updated_successfully']);
            header('Location: all_articles.php?lang=' . $lang);
            exit();
        } else {
            error_log('Update failed: ' . implode(', ', $stmt->errorInfo()));
            set_flash_message('error', $trans['failed_update_article']);
        }
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        set_flash_message('error', $trans['database_error']);
    }
    
    // Redirect back to edit page
    header('Location: edit_article.php?id=' . $article_id . '&lang=' . $lang);
    exit();
}

// Include header
include_once 'includes/header.php';
?>

<div class="home-content">
    <div class="sales-boxes">
        <div class="recent-sales box">
            <div class="title"><?php echo $trans['edit_article']; ?></div>
            
            <?php if (has_flash_message('error')): ?>
                <div class="alert alert-danger"><?php echo get_flash_message('error'); ?></div>
            <?php endif; ?>
            
            <?php if (has_flash_message('success')): ?>
                <div class="alert alert-success"><?php echo get_flash_message('success'); ?></div>
            <?php endif; ?>
            
            <div class="sales-details">
                <form method="POST" action="edit_article.php?id=<?php echo h($article_id); ?>&lang=<?php echo $lang; ?>" enctype="multipart/form-data" class="article-form">
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
                            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars_decode(h($article['title'], ENT_QUOTES)); ?>" required maxlength="100" placeholder="<?php echo $trans['enter_article_title']; ?>">
                            <div class="error-message" id="title_error"></div>
                        </div>
                        
                        <div class="field">
                            <label for="desc"><?php echo $trans['description']; ?> (English) *</label>
                            <textarea id="desc" name="desc" required maxlength="500" rows="3" placeholder="<?php echo $trans['enter_article_description']; ?>"><?php echo htmlspecialchars_decode(h($article['description'], ENT_QUOTES)); ?></textarea>
                            <div class="error-message" id="desc_error"></div>
                        </div>
                        
                        <div class="field">
                            <label for="sec_title"><?php echo $trans['secondary_title']; ?> (English) *</label>
                            <input type="text" id="sec_title" name="sec_title" value="<?php echo htmlspecialchars_decode(h($article['sec_title'], ENT_QUOTES)); ?>" required maxlength="100" placeholder="<?php echo $trans['enter_secondary_title']; ?>">
                            <div class="error-message" id="sec_title_error"></div>
                        </div>
                        
                        <div class="field">
                            <label for="content1"><?php echo $trans['content']; ?> (English) *</label>
                            <textarea id="content1" name="content1" required maxlength="1000" rows="4" placeholder="<?php echo $trans['enter_main_content']; ?>"><?php echo htmlspecialchars_decode(h($article['content1'], ENT_QUOTES)); ?></textarea>
                            <div class="error-message" id="content1_error"></div>
                        </div>
                        
                        <div class="field">
                            <label for="tert_title"><?php echo $trans['tertiary_title']; ?> (English)</label>
                            <input type="text" id="tert_title" name="tert_title" value="<?php echo htmlspecialchars_decode(h($article['tert_title'], ENT_QUOTES)); ?>" maxlength="100" placeholder="<?php echo $trans['enter_tertiary_title']; ?>">
                        </div>
                        
                        <div class="field">
                            <label for="content2"><?php echo $trans['content']; ?> (English)</label>
                            <textarea id="content2" name="content2" maxlength="1000" rows="4" placeholder="<?php echo $trans['enter_additional_content']; ?>"><?php echo htmlspecialchars_decode(h($article['content2'], ENT_QUOTES)); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- French Fields -->
                    <div class="language-content" id="lang-fr">
                        <div class="field">
                            <label for="title_fr"><?php echo $trans['title']; ?> (Français)</label>
                            <input type="text" id="title_fr" name="title_fr" value="<?php echo htmlspecialchars_decode(h($article['title_fr'] ?? '', ENT_QUOTES)); ?>" maxlength="100" placeholder="Entrez le titre de l'article">
                        </div>
                        
                        <div class="field">
                            <label for="desc_fr"><?php echo $trans['description']; ?> (Français)</label>
                            <textarea id="desc_fr" name="desc_fr" maxlength="500" rows="3" placeholder="Entrez la description de l'article"><?php echo htmlspecialchars_decode(h($article['description_fr'] ?? '', ENT_QUOTES)); ?></textarea>
                        </div>
                        
                        <div class="field">
                            <label for="sec_title_fr"><?php echo $trans['secondary_title']; ?> (Français)</label>
                            <input type="text" id="sec_title_fr" name="sec_title_fr" value="<?php echo htmlspecialchars_decode(h($article['sec_title_fr'] ?? '', ENT_QUOTES)); ?>" maxlength="100" placeholder="Entrez le titre secondaire">
                        </div>
                        
                        <div class="field">
                            <label for="content1_fr"><?php echo $trans['content']; ?> (Français)</label>
                            <textarea id="content1_fr" name="content1_fr" maxlength="1000" rows="4" placeholder="Entrez le contenu principal"><?php echo htmlspecialchars_decode(h($article['content1_fr'] ?? '', ENT_QUOTES)); ?></textarea>
                        </div>
                        
                        <div class="field">
                            <label for="tert_title_fr"><?php echo $trans['tertiary_title']; ?> (Français)</label>
                            <input type="text" id="tert_title_fr" name="tert_title_fr" value="<?php echo htmlspecialchars_decode(h($article['tert_title_fr'] ?? '', ENT_QUOTES)); ?>" maxlength="100" placeholder="Entrez le titre tertiaire">
                        </div>
                        
                        <div class="field">
                            <label for="content2_fr"><?php echo $trans['content']; ?> (Français)</label>
                            <textarea id="content2_fr" name="content2_fr" maxlength="1000" rows="4" placeholder="Entrez le contenu supplémentaire"><?php echo htmlspecialchars_decode(h($article['content2_fr'] ?? '', ENT_QUOTES)); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Arabic Fields -->
                    <div class="language-content" id="lang-ar">
                        <div class="field">
                            <label for="title_ar"><?php echo $trans['title']; ?> (العربية)</label>
                            <input type="text" id="title_ar" name="title_ar" value="<?php echo htmlspecialchars_decode(h($article['title_ar'] ?? '', ENT_QUOTES)); ?>" maxlength="100" placeholder="أدخل عنوان المقال" dir="rtl">
                        </div>
                        
                        <div class="field">
                            <label for="desc_ar"><?php echo $trans['description']; ?> (العربية)</label>
                            <textarea id="desc_ar" name="desc_ar" maxlength="500" rows="3" placeholder="أدخل وصف المقال" dir="rtl"><?php echo htmlspecialchars_decode(h($article['description_ar'] ?? '', ENT_QUOTES)); ?></textarea>
                        </div>
                        
                        <div class="field">
                            <label for="sec_title_ar"><?php echo $trans['secondary_title']; ?> (العربية)</label>
                            <input type="text" id="sec_title_ar" name="sec_title_ar" value="<?php echo htmlspecialchars_decode(h($article['sec_title_ar'] ?? '', ENT_QUOTES)); ?>" maxlength="100" placeholder="أدخل العنوان الثانوي" dir="rtl">
                        </div>
                        
                        <div class="field">
                            <label for="content1_ar"><?php echo $trans['content']; ?> (العربية)</label>
                            <textarea id="content1_ar" name="content1_ar" maxlength="1000" rows="4" placeholder="أدخل المحتوى الرئيسي" dir="rtl"><?php echo htmlspecialchars_decode(h($article['content1_ar'] ?? '', ENT_QUOTES)); ?></textarea>
                        </div>
                        
                        <div class="field">
                            <label for="tert_title_ar"><?php echo $trans['tertiary_title']; ?> (العربية)</label>
                            <input type="text" id="tert_title_ar" name="tert_title_ar" value="<?php echo htmlspecialchars_decode(h($article['tert_title_ar'] ?? '', ENT_QUOTES)); ?>" maxlength="100" placeholder="أدخل العنوان الثالثي" dir="rtl">
                        </div>
                        
                        <div class="field">
                            <label for="content2_ar"><?php echo $trans['content']; ?> (العربية)</label>
                            <textarea id="content2_ar" name="content2_ar" maxlength="1000" rows="4" placeholder="أدخل المحتوى الإضافي" dir="rtl"><?php echo htmlspecialchars_decode(h($article['content2_ar'] ?? '', ENT_QUOTES)); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Image Field -->
                    <div class="field">
                        <label for="image"><?php echo !empty($article['images']) ? $trans['replace_image_optional'] : $trans['upload_image']; ?></label>
                        <?php if (!empty($article['images'])): ?>
                            <div class="current-image">
                                <img src="../public/images/<?php echo htmlspecialchars_decode(h($article['images'], ENT_QUOTES)); ?>" alt="<?php echo $trans['current_image']; ?>" class="article-thumbnail" onerror="this.style.display='none'">
                                <p><?php echo $trans['current_image']; ?>: <?php echo htmlspecialchars_decode(h($article['images'], ENT_QUOTES)); ?></p>
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
                            <span class="btn-text"><?php echo $trans['edit_article']; ?></span>
                            <span class="btn-loader" style="display:none;">
                                <i class="bx bx-loader bx-spin"></i>
                            </span>
                        </button>
                        <a href="all_articles.php?lang=<?php echo $lang; ?>" class="btn btn-cancel"><?php echo $trans['cancel']; ?></a>
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

    .article-form textarea {
        resize: vertical;
        min-height: 100px;
    }

    /* File upload styling - Matching tips.php */
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

    /* Current image styling */
    .current-image {
        margin-bottom: 15px;
    }

    .article-thumbnail {
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
        background-color:rgba(247, 79, 28, 0.89);
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
        .article-form {
            padding: 0 15px 15px;
        }

        .article-form input[type="text"],
        .article-form textarea {
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
        const form = document.querySelector('.article-form');
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

                // Validate secondary title (English is required)
                const secTitle = this.querySelector('#sec_title');
                if (secTitle.value.trim().length < 3) {
                    document.getElementById('sec_title_error').textContent = '<?php echo $trans['sec_title_min_length'] ?? 'Secondary title must be at least 3 characters long.'; ?>';
                    document.getElementById('sec_title_error').style.display = 'block';
                    isValid = false;
                }

                // Validate content1 (English is required)
                const content1 = this.querySelector('#content1');
                if (content1.value.trim().length < 10) {
                    document.getElementById('content1_error').textContent = '<?php echo $trans['content_min_length'] ?? 'Content must be at least 10 characters long.'; ?>';
                    document.getElementById('content1_error').style.display = 'block';
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
        document.querySelectorAll('.article-thumbnail').forEach(img => {
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