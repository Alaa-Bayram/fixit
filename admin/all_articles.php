<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Include language file
require_once 'lang.php';

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', $trans['please_log_in']);
}

// Include articles data
require_once 'includes/articles_data.php';

// Handle article deletion if requested
if (isset($_POST['delete_article']) && isset($_POST['article_id'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        set_flash_message('error', $trans['invalid_form_submission']);
    } else {
        $article_id = sanitize_input($_POST['article_id']);

        try {
            // Get image filename before deletion
            $stmt = $pdo->prepare("SELECT images FROM articles WHERE article_id = :article_id");
            $stmt->bindParam(':article_id', $article_id);
            $stmt->execute();
            $article = $stmt->fetch();

            if ($article) {
                // Delete from database
                $stmt = $pdo->prepare("DELETE FROM articles WHERE article_id = :article_id");
                $stmt->bindParam(':article_id', $article_id);

                if ($stmt->execute()) {
                    // Delete image file if it exists
                    $image_path = '../public/images/' . $article['images'];
                    if (file_exists($image_path)) {
                        unlink($image_path);
                    }

                    set_flash_message('success', $trans['article_deleted_successfully']);
                } else {
                    set_flash_message('error', $trans['failed_delete_article']);
                }
            } else {
                set_flash_message('error', $trans['article_not_found']);
            }
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            set_flash_message('error', $trans['database_error']);
        }
    }

    // Refresh the page to show updated data
    header('Location: all_articles.php?lang=' . $lang);
    exit();
}

// Get articles with proper translation handling
try {
    $stmt = $pdo->prepare("SELECT article_id, user_id, title, description, sec_title, content1, tert_title, content2, images, date,
                                  title_fr, description_fr, sec_title_fr, content1_fr, tert_title_fr, content2_fr,
                                  title_ar, description_ar, sec_title_ar, content1_ar, tert_title_ar, content2_ar 
                           FROM articles ORDER BY date DESC");
    $stmt->execute();
    $articles_raw = $stmt->fetchAll();

    // Process articles with translation support
    $all_articles = [];
    foreach ($articles_raw as $article) {
        $processed_article = [
            'article_id' => $article['article_id'],
            'user_id' => $article['user_id'],
            'images' => $article['images'],
            'date' => $article['date']
        ];

        // Handle translations based on current language
        if ($lang === 'fr') {
            $processed_article['title'] = !empty($article['title_fr']) ? $article['title_fr'] : $article['title'];
            $processed_article['description'] = !empty($article['description_fr']) ? $article['description_fr'] : $article['description'];
            $processed_article['sec_title'] = !empty($article['sec_title_fr']) ? $article['sec_title_fr'] : $article['sec_title'];
            $processed_article['content1'] = !empty($article['content1_fr']) ? $article['content1_fr'] : $article['content1'];
            $processed_article['tert_title'] = !empty($article['tert_title_fr']) ? $article['tert_title_fr'] : $article['tert_title'];
            $processed_article['content2'] = !empty($article['content2_fr']) ? $article['content2_fr'] : $article['content2'];
        } elseif ($lang === 'ar') {
            $processed_article['title'] = !empty($article['title_ar']) ? $article['title_ar'] : $article['title'];
            $processed_article['description'] = !empty($article['description_ar']) ? $article['description_ar'] : $article['description'];
            $processed_article['sec_title'] = !empty($article['sec_title_ar']) ? $article['sec_title_ar'] : $article['sec_title'];
            $processed_article['content1'] = !empty($article['content1_ar']) ? $article['content1_ar'] : $article['content1'];
            $processed_article['tert_title'] = !empty($article['tert_title_ar']) ? $article['tert_title_ar'] : $article['tert_title'];
            $processed_article['content2'] = !empty($article['content2_ar']) ? $article['content2_ar'] : $article['content2'];
        } else {
            // Default to English
            $processed_article['title'] = $article['title'];
            $processed_article['description'] = $article['description'];
            $processed_article['sec_title'] = $article['sec_title'];
            $processed_article['content1'] = $article['content1'];
            $processed_article['tert_title'] = $article['tert_title'];
            $processed_article['content2'] = $article['content2'];
        }

        // Add main_title for backward compatibility (using sec_title)
        $processed_article['main_title'] = $processed_article['sec_title'];

        $all_articles[] = $processed_article;
    }
} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    $all_articles = [];
    set_flash_message('error', $trans['database_error']);
}

// Include header
include_once 'includes/header.php';
?>

<div class="home-content">
    <div class="sales-boxes">
        <div class="recent-sales box">
            <div class="title"><?php echo $trans['all_articles']; ?></div>

            <?php if (has_flash_message('success')): ?>
                <div class="alert alert-success"><?php echo get_flash_message('success'); ?></div>
            <?php endif; ?>

            <?php if (has_flash_message('error')): ?>
                <div class="alert alert-danger"><?php echo get_flash_message('error'); ?></div>
            <?php endif; ?>

            <div class="articles-grid">
                <?php if (empty($all_articles)): ?>
                    <div class="no-data"><?php echo $trans['no_articles_available']; ?></div>
                <?php else: ?>
                    <?php foreach ($all_articles as $article): ?>
                        <div class="article-card">
                            <div class="article-image">
                                <img src="../public/images/<?php echo h($article['images']); ?>" alt="<?php echo h($article['title']); ?>">
                            </div>
                            <div class="article-details">
                                <h3><?php echo h($article['title']); ?></h3>
                                <p class="article-description"><?php echo h($article['description']); ?></p>
                                <?php if (!empty($article['sec_title'])): ?>
                                    <p class="article-main-title"><?php echo h($article['sec_title']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($article['tert_title'])): ?>
                                    <p class="article-sec-title"><?php echo h($article['tert_title']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($article['content1'])): ?>
                                    <p class="article-content"><?php echo h($article['content1']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($article['content2'])): ?>
                                    <p class="article-content"><?php echo h($article['content2']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="article-actions">
                                <a href="edit_article.php?id=<?php echo h($article['article_id']); ?>&lang=<?php echo $lang; ?>" class="btn btn-edit">
                                    <i class="bx bx-edit"></i> <?php echo $trans['edit']; ?>
                                </a>
                                <form method="POST" onsubmit="return confirm('<?php echo $trans['confirm_delete_article']; ?>');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                    <input type="hidden" name="article_id" value="<?php echo h($article['article_id']); ?>">
                                    <button type="submit" name="delete_article" class="btn btn-delete">
                                        <i class="bx bx-trash"></i> <?php echo $trans['delete']; ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="button">
                <a href="articles.php?lang=<?php echo $lang; ?>"><?php echo $trans['back_to_articles']; ?></a>
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
    
    .articles-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
        width: 100%;
        padding: 0;
        box-sizing: border-box;
    }

    .recent-sales.box {
        width: 100%;
        padding: 20px;
        box-sizing: border-box;
    }

    /* On larger screens (min-width: 1024px), force exactly 3 columns */
    @media (min-width: 1024px) {
        .articles-grid {
            grid-template-columns: repeat(3, minmax(300px, 1fr));
        }
    }

    .article-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    .article-card:hover {
        transform: translateY(-5px);
    }

    .article-image {
        height: 200px;
        overflow: hidden;
    }

    .article-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .article-details {
        padding: 15px;
    }

    .article-details h3 {
        color: #f97f4b;
        margin-bottom: 10px;
    }

    .article-description {
        color: #3a3a3ae1;
        font-style: italic;
        font-size: 14px;
        margin-bottom: 10px;
    }

    .article-main-title {
        color: #333;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .article-sec-title {
        color: gold;
        font-weight: 500;
        margin-bottom: 5px;
    }

    .article-content {
        color: #555;
        font-size: 14px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 10px;
    }

    .article-actions {
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
        background-color: #79bcb1;
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

    /* RTL support for Arabic */
    <?php if ($lang === 'ar'): ?>
    body {
        direction: rtl;
        text-align: right;
    }
    
    .article-actions {
        flex-direction: row-reverse;
    }
    
    .btn i {
        margin-right: 0;
        margin-left: 5px;
    }
    
    .articles-grid {
        direction: rtl;
    }

    .article-card {
        direction: rtl;
        text-align: right;
    }

    .article-details h3,
    .article-details p {
        text-align: right;
    }
    <?php endif; ?>

    /* French language adjustments */
    <?php if ($lang === 'fr'): ?>
    .article-details h3,
    .article-details p {
        line-height: 1.4;
    }
    <?php endif; ?>
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

<?php include_once 'includes/footer.php'; ?>