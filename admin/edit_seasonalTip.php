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
    $stmt = $pdo->prepare("SELECT * FROM tips WHERE tip_id = :tip_id AND type = 'seasonal tips'");
    $stmt->bindParam(':tip_id', $tip_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $tip = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tip) {
        redirect('all_tips.php?lang=' . $lang, $trans['tip_not_found'] ?? "Seasonal tip not found.");
    }
} catch (PDOException $e) {
    error_log('Database Error in edit_seasonalTip.php (fetch): ' . $e->getMessage());
    redirect('all_tips.php?lang=' . $lang, $trans['database_error']);
}

// Handle form submission for updating tip
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_tip'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token'])) {
        set_flash_message('error', $trans['invalid_form_submission']);
        header('Location: edit_seasonalTip.php?id=' . $tip_id . '&lang=' . $lang);
        exit();
    }

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        set_flash_message('error', $trans['invalid_form_submission']);
        header('Location: edit_seasonalTip.php?id=' . $tip_id . '&lang=' . $lang);
        exit();
    }
    
    // Sanitize input for all languages - DON'T HTML encode here
    $title = trim(stripslashes($_POST['title'] ?? ''));
    $description = trim(stripslashes($_POST['description'] ?? ''));
    $f_tip = trim(stripslashes($_POST['f_tip'] ?? ''));
    $s_tip = trim(stripslashes($_POST['s_tip'] ?? ''));
    
    $title_fr = trim(stripslashes($_POST['title_fr'] ?? ''));
    $description_fr = trim(stripslashes($_POST['description_fr'] ?? ''));
    $f_tip_fr = trim(stripslashes($_POST['f_tip_fr'] ?? ''));
    $s_tip_fr = trim(stripslashes($_POST['s_tip_fr'] ?? ''));
    
    $title_ar = trim(stripslashes($_POST['title_ar'] ?? ''));
    $description_ar = trim(stripslashes($_POST['description_ar'] ?? ''));
    $f_tip_ar = trim(stripslashes($_POST['f_tip_ar'] ?? ''));
    $s_tip_ar = trim(stripslashes($_POST['s_tip_ar'] ?? ''));
    
    // Validate input - at least English fields are required
    if (empty($title) || empty($description) || empty($f_tip) || empty($s_tip)) {
        set_flash_message('error', $trans['all_fields_required']);
        header('Location: edit_seasonalTip.php?id=' . $tip_id . '&lang=' . $lang);
        exit();
    }
    
    try {
        // Update database with all language fields
        $stmt = $pdo->prepare("UPDATE tips SET 
            title = :title, 
            description = :description, 
            f_tip = :f_tip, 
            s_tip = :s_tip,
            title_fr = :title_fr, 
            description_fr = :description_fr, 
            f_tip_fr = :f_tip_fr, 
            s_tip_fr = :s_tip_fr,
            title_ar = :title_ar, 
            description_ar = :description_ar, 
            f_tip_ar = :f_tip_ar, 
            s_tip_ar = :s_tip_ar
            WHERE tip_id = :tip_id");
            
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':f_tip', $f_tip);
        $stmt->bindParam(':s_tip', $s_tip);
        $stmt->bindParam(':title_fr', $title_fr);
        $stmt->bindParam(':description_fr', $description_fr);
        $stmt->bindParam(':f_tip_fr', $f_tip_fr);
        $stmt->bindParam(':s_tip_fr', $s_tip_fr);
        $stmt->bindParam(':title_ar', $title_ar);
        $stmt->bindParam(':description_ar', $description_ar);
        $stmt->bindParam(':f_tip_ar', $f_tip_ar);
        $stmt->bindParam(':s_tip_ar', $s_tip_ar);
        $stmt->bindParam(':tip_id', $tip_id);
        
        if ($stmt->execute()) {
            set_flash_message('success', $trans['tip_updated_successfully'] ?? 'Seasonal tip updated successfully.');
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
    header('Location: edit_seasonalTip.php?id=' . $tip_id . '&lang=' . $lang);
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
            <div class="title"><?php echo $trans['edit_seasonal_tip'] ?? 'Edit Seasonal Tip'; ?></div>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo h($success_message); ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo h($error_message); ?></div>
            <?php endif; ?>

            <div class="sales-details">
                <form method="POST" class="tip-form">
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

                        <div class="field">
                            <label for="f_tip"><?php echo $trans['first_tip'] ?? 'First Seasonal Tip'; ?> (English) *</label>
                            <input type="text" id="f_tip" name="f_tip" value="<?php echo h($tip['f_tip']); ?>" required maxlength="255" placeholder="<?php echo $trans['enter_first_tip'] ?? 'Enter first seasonal tip'; ?>">
                            <div class="error-message" id="f_tip_error"></div>
                        </div>

                        <div class="field">
                            <label for="s_tip"><?php echo $trans['second_tip'] ?? 'Second Seasonal Tip'; ?> (English) *</label>
                            <input type="text" id="s_tip" name="s_tip" value="<?php echo h($tip['s_tip']); ?>" required maxlength="255" placeholder="<?php echo $trans['enter_second_tip'] ?? 'Enter second seasonal tip'; ?>">
                            <div class="error-message" id="s_tip_error"></div>
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

                        <div class="field">
                            <label for="f_tip_fr"><?php echo $trans['first_tip'] ?? 'First Seasonal Tip'; ?> (Français)</label>
                            <input type="text" id="f_tip_fr" name="f_tip_fr" value="<?php echo h($tip['f_tip_fr'] ?? ''); ?>" maxlength="255" placeholder="Entrez le premier conseil saisonnier">
                        </div>

                        <div class="field">
                            <label for="s_tip_fr"><?php echo $trans['second_tip'] ?? 'Second Seasonal Tip'; ?> (Français)</label>
                            <input type="text" id="s_tip_fr" name="s_tip_fr" value="<?php echo h($tip['s_tip_fr'] ?? ''); ?>" maxlength="255" placeholder="Entrez le deuxième conseil saisonnier">
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

                        <div class="field">
                            <label for="f_tip_ar"><?php echo $trans['first_tip'] ?? 'First Seasonal Tip'; ?> (العربية)</label>
                            <input type="text" id="f_tip_ar" name="f_tip_ar" value="<?php echo h($tip['f_tip_ar'] ?? ''); ?>" maxlength="255" placeholder="أدخل النصيحة الموسمية الأولى" dir="rtl">
                        </div>

                        <div class="field">
                            <label for="s_tip_ar"><?php echo $trans['second_tip'] ?? 'Second Seasonal Tip'; ?> (العربية)</label>
                            <input type="text" id="s_tip_ar" name="s_tip_ar" value="<?php echo h($tip['s_tip_ar'] ?? ''); ?>" maxlength="255" placeholder="أدخل النصيحة الموسمية الثانية" dir="rtl">
                        </div>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn btn-update">
                            <span class="btn-text"><?php echo $trans['update_seasonal_tip'] ?? 'Update Seasonal Tip'; ?></span>
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

    .tip-form input[type="text"]:focus,
    .tip-form textarea:focus {
        border-color: #f97f4b;
        box-shadow: 0 0 0 2px rgba(249, 127, 75, 0.2);
        outline: none;
    }

    .tip-form textarea {
        resize: vertical;
        min-height: 100px;
    }

    .error-message {
        color: #e74c3c;
        font-size: 12px;
        margin-top: 5px;
        display: none;
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

                // Validate first tip
                const f_tip = this.querySelector('#f_tip');
                if (f_tip.value.trim().length < 5) {
                    document.getElementById('f_tip_error').textContent = 'First tip must be at least 5 characters long';
                    document.getElementById('f_tip_error').style.display = 'block';
                    isValid = false;
                }

                // Validate second tip
                const s_tip = this.querySelector('#s_tip');
                if (s_tip.value.trim().length < 5) {
                    document.getElementById('s_tip_error').textContent = 'Second tip must be at least 5 characters long';
                    document.getElementById('s_tip_error').style.display = 'block';
                    isValid = false;
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
    });
</script>

<?php include_once 'includes/footer.php'; ?>