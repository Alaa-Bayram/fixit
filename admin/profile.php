<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', 'Please log in to access your profile');
}

// Get admin details
$admin = get_admin_details($pdo);

if (!$admin) {
    set_flash_message('error', 'Unable to load profile information.');
    redirect('index.php');
}

// Handle profile image upload
if (isset($_POST['upload_image'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        set_flash_message('error', 'Invalid form submission. Please try again.');
        redirect('profile.php');
    }

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../public/images/';
        
        // Supported image types (including webp and svg)
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/pjpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'image/x-icon' => 'ico',
            'image/bmp' => 'bmp',
            'image/tiff' => 'tiff'
        ];
        
        $maxSize = 10 * 1024 * 1024; // Increased to 10MB

        $fileInfo = $_FILES['profile_image'];
        $fileType = $fileInfo['type'];
        $fileSize = $fileInfo['size'];
        $tmpName = $fileInfo['tmp_name'];

        // Validate file type by checking both MIME type and extension
        $fileExt = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $validMime = array_key_exists($fileType, $allowedTypes);
        $validExt = in_array($fileExt, array_values($allowedTypes));
        
        if (!$validMime || !$validExt) {
            set_flash_message('error', 'Please upload a valid image file (JPEG, PNG, GIF, WebP, SVG, ICO, BMP, or TIFF).');
            redirect('profile.php');
        }

        // Validate file size
        if ($fileSize > $maxSize) {
            set_flash_message('error', 'Image file is too large. Maximum size is 10MB.');
            redirect('profile.php');
        }

        // Additional security check - verify the image is actually an image
        $imageInfo = @getimagesize($tmpName);
        if (!$imageInfo) {
            set_flash_message('error', 'The uploaded file is not a valid image.');
            redirect('profile.php');
        }

        // Generate unique filename using the correct extension from MIME type
        $extension = $allowedTypes[$fileType];
        $filename = 'profile_' . $admin['user_id'] . '_' . time() . '.' . $extension;
        $targetPath = $uploadDir . $filename;

        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Sanitize the filename
        $filename = preg_replace("/[^a-zA-Z0-9\._-]/", "", $filename);
        $targetPath = $uploadDir . $filename;

        // Move uploaded file
        if (move_uploaded_file($tmpName, $targetPath)) {
            try {
                // Delete old profile image if it exists and is not default
                if ($admin['img'] && $admin['img'] !== 'default-profile.jpg' && file_exists($uploadDir . $admin['img'])) {
                    unlink($uploadDir . $admin['img']);
                }

                // Update database with new image filename
                $stmt = $pdo->prepare("UPDATE users SET img = :img WHERE user_id = :user_id");
                $stmt->bindParam(':img', $filename);
                $stmt->bindParam(':user_id', $admin['user_id']);

                if ($stmt->execute()) {
                    set_flash_message('success', 'Profile image updated successfully.');
                    // Refresh admin data
                    $admin = get_admin_details($pdo);
                } else {
                    set_flash_message('error', 'Failed to update profile image in database.');
                }
            } catch (PDOException $e) {
                error_log('Image Update Error: ' . $e->getMessage());
                set_flash_message('error', 'A system error occurred while updating the image.');
            }
        } else {
            set_flash_message('error', 'Failed to upload image file.');
        }
    } else {
        $uploadError = $_FILES['profile_image']['error'] ?? 0;
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
        ];
        
        $errorMessage = $errorMessages[$uploadError] ?? 'Please select an image file to upload.';
        set_flash_message('error', $errorMessage);
    }

    redirect('profile.php');
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['upload_image'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        set_flash_message('error', 'Invalid form submission. Please try again.');
        redirect('profile.php');
    }

    // Check if this is a password change request
    $isPasswordChange = !empty($_POST['new_password']) || !empty($_POST['current_password']) || !empty($_POST['confirm_password']);

    if ($isPasswordChange) {
        // Handle password change
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate required fields for password change
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            set_flash_message('error', 'Please fill in all password fields.');
            redirect('profile.php');
        }

        // Validate current password
        if (!password_verify($current_password, $admin['password'])) {
            set_flash_message('error', 'Current password is incorrect.');
            redirect('profile.php');
        }

        // Validate new password
        if (strlen($new_password) < 8) {
            set_flash_message('error', 'New password must be at least 8 characters long.');
            redirect('profile.php');
        }

        if ($new_password !== $confirm_password) {
            set_flash_message('error', 'New passwords do not match.');
            redirect('profile.php');
        }

        try {
            // Update password only
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE user_id = :user_id");
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':user_id', $admin['user_id']);

            if ($stmt->execute()) {
                set_flash_message('success', 'Password updated successfully.');
            } else {
                set_flash_message('error', 'Failed to update password.');
            }
        } catch (PDOException $e) {
            error_log('Password Update Error: ' . $e->getMessage());
            set_flash_message('error', 'A system error occurred. Please try again later.');
        }
    } else {
        // Handle profile information update
        $fname = sanitize_input($_POST['fname']);
        $lname = sanitize_input($_POST['lname']);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $phone = sanitize_input($_POST['phone']);

        // Validate required fields for profile update
        if (empty($fname) || empty($lname) || empty($email)) {
            set_flash_message('error', 'Please fill in all required fields.');
            redirect('profile.php');
        }

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash_message('error', 'Please provide a valid email address.');
            redirect('profile.php');
        }

        try {
            $pdo->beginTransaction();

            // Update profile information only
            $stmt = $pdo->prepare("UPDATE users SET fname = :fname, lname = :lname, email = :email, phone = :phone WHERE user_id = :user_id");
            $stmt->bindParam(':fname', $fname);
            $stmt->bindParam(':lname', $lname);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':user_id', $admin['user_id']);

            if ($stmt->execute()) {
                $pdo->commit();
                set_flash_message('success', 'Profile updated successfully.');

                // Refresh admin data
                $admin = get_admin_details($pdo);
            } else {
                $pdo->rollback();
                set_flash_message('error', 'Failed to update profile.');
            }
        } catch (PDOException $e) {
            $pdo->rollback();
            error_log('Profile Update Error: ' . $e->getMessage());
            set_flash_message('error', 'A system error occurred. Please try again later.');
        }
    }

    redirect('profile.php');
}

// Generate new CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include_once "includes/header.php";
?>

<div class="home-content">
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                <img src="../public/images/<?php echo h($admin['img'] ?? 'default-profile.jpg'); ?>" alt="Profile Picture" id="profileImage">
                <div class="avatar-overlay" onclick="document.getElementById('imageUpload').click()">
                    <i class="bx bx-camera"></i>
                </div>
            </div>
            <div class="profile-info">
                <h1><?php echo h($admin['fname'] . ' ' . $admin['lname']); ?></h1>
                <p class="profile-role">System Administrator</p>
                <p class="profile-status">
                    <i class="bx bx-circle" style="color: #4CAF50;"></i>
                    <?php echo h($admin['status'] ?? 'Active'); ?>
                </p>
            </div>
        </div>

        <!-- Hidden file input for image upload -->
        <form method="POST" enctype="multipart/form-data" id="imageUploadForm" style="display: none;">
            <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
            <input type="file" id="imageUpload" name="profile_image" accept="image/*" onchange="uploadImage()">
            <input type="hidden" name="upload_image" value="1">
        </form>

        <?php if (has_flash_message('error')): ?>
            <div class="alert alert-danger">
                <i class="bx bx-error-circle"></i>
                <?php echo h(get_flash_message('error')); ?>
            </div>
        <?php endif; ?>

        <?php if (has_flash_message('success')): ?>
            <div class="alert alert-success">
                <i class="bx bx-check-circle"></i>
                <?php echo h(get_flash_message('success')); ?>
            </div>
        <?php endif; ?>

        <div class="profile-tabs">
            <div class="tab-buttons">
                <button class="tab-btn active" data-tab="personal">
                    <i class="bx bx-user"></i>
                    Personal Information
                </button>
                <button class="tab-btn" data-tab="security">
                    <i class="bx bx-shield"></i>
                    Security Settings
                </button>
                <button class="tab-btn" data-tab="activity">
                    <i class="bx bx-time"></i>
                    Activity Log
                </button>
            </div>

            <div class="tab-content">
                <!-- Personal Information Tab -->
                <div class="tab-pane active" id="personal">
                    <div class="profile-card">
                        <div class="card-header">
                            <h3><i class="bx bx-user"></i> Personal Information</h3>
                            <p>Update your personal details and contact information</p>
                        </div>
                        <form method="POST" class="profile-form">
                            <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="fname">First Name *</label>
                                    <input type="text" id="fname" name="fname" value="<?php echo h($admin['fname']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="lname">Last Name *</label>
                                    <input type="text" id="lname" name="lname" value="<?php echo h($admin['lname']); ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" value="<?php echo h($admin['email']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" value="<?php echo h($admin['phone'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="created_at">Member Since</label>
                                <input type="text" value="<?php echo date('F j, Y', strtotime($admin['created_at'] ?? 'now')); ?>" readonly>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save"></i>
                                    Update Information
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Security Settings Tab -->
                <div class="tab-pane" id="security">
                    <div class="profile-card">
                        <div class="card-header">
                            <h3><i class="bx bx-shield"></i> Change Password</h3>
                            <p>Keep your account secure with a strong password</p>
                        </div>
                        <form method="POST" class="profile-form" id="passwordForm">
                            <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">

                            <div class="form-group">
                                <label for="current_password">Current Password *</label>
                                <div class="password-input">
                                    <input type="password" id="current_password" name="current_password" required>
                                    <button type="button" class="password-toggle" data-target="current_password">
                                        <i class="bx bx-hide"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="new_password">New Password *</label>
                                <div class="password-input">
                                    <input type="password" id="new_password" name="new_password" minlength="8" required>
                                    <button type="button" class="password-toggle" data-target="new_password">
                                        <i class="bx bx-hide"></i>
                                    </button>
                                </div>
                                <div class="password-strength" id="passwordStrength"></div>
                                <small class="password-hint">Use at least 8 characters with a mix of letters, numbers & symbols</small>
                            </div>

                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password *</label>
                                <div class="password-input">
                                    <input type="password" id="confirm_password" name="confirm_password" required>
                                    <button type="button" class="password-toggle" data-target="confirm_password">
                                        <i class="bx bx-hide"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-key"></i>
                                    Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Activity Log Tab -->
                <div class="tab-pane" id="activity">
                    <div class="profile-card">
                        <div class="card-header">
                            <h3><i class="bx bx-time"></i> Recent Activity</h3>
                            <p>Your recent account activity and login history</p>
                        </div>
                        <div class="activity-list">
                            <div class="activity-item">
                                <div class="activity-icon success">
                                    <i class="bx bx-log-in"></i>
                                </div>
                                <div class="activity-details">
                                    <h4>Successful Login</h4>
                                    <p>Logged in from <?php echo h($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown device'); ?></p>
                                    <span class="activity-time">
                                        <?php
                                        date_default_timezone_set('Asia/Beirut');
                                        echo date('M j, Y \a\t g:i A');
                                        ?>
                                    </span>
                                </div>
                            </div>

                            <div class="activity-item">
                                <div class="activity-icon info">
                                    <i class="bx bx-edit"></i>
                                </div>
                                <div class="activity-details">
                                    <h4>Profile Updated</h4>
                                    <p>Personal information was modified</p>
                                    <span class="activity-time">Last updated</span>
                                </div>
                            </div>

                            <div class="activity-item">
                                <div class="activity-icon warning">
                                    <i class="bx bx-shield"></i>
                                </div>
                                <div class="activity-details">
                                    <h4>Security Check</h4>
                                    <p>Password strength validation passed</p>
                                    <span class="activity-time">System check</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .profile-header {
        background: linear-gradient(135deg, #79bcb1 0%, #5a9e92 100%);
        border-radius: 15px;
        padding: 40px;
        display: flex;
        align-items: center;
        gap: 30px;
        margin-bottom: 30px;
        color: white;
        box-shadow: 0 10px 30px rgba(121, 188, 177, 0.3);
    }

    .profile-avatar {
        position: relative;
        cursor: pointer;
    }

    .profile-avatar img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid rgba(255, 255, 255, 0.3);
        transition: all 0.3s ease;
        object-fit: cover;
    }

    .profile-avatar:hover img {
        transform: scale(1.05);
    }

    .avatar-overlay {
        position: absolute;
        bottom: 0;
        right: 0;
        background: #ff6c40e4;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid white;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .avatar-overlay:hover {
        background: #d35400;
        transform: scale(1.1);
    }

    .profile-info h1 {
        margin: 0 0 10px 0;
        font-size: 2.5rem;
        font-weight: 600;
    }

    .profile-role {
        font-size: 1.2rem;
        opacity: 0.9;
        margin: 0 0 10px 0;
    }

    .profile-status {
        display: flex;
        align-items: center;
        gap: 8px;
        opacity: 0.8;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-danger {
        background: #ffebee;
        color: #c62828;
        border-left: 4px solid #f44336;
    }

    .alert-success {
        background: #e8f5e8;
        color: #2e7d32;
        border-left: 4px solid #4caf50;
    }

    .profile-tabs {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .tab-buttons {
        display: flex;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }

    .tab-btn {
        flex: 1;
        padding: 20px;
        border: none;
        background: transparent;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1rem;
        color: #6c757d;
        transition: all 0.3s ease;
    }

    .tab-btn:hover {
        background: rgba(121, 188, 177, 0.1);
        color: #79bcb1;
    }

    .tab-btn.active {
        background: #79bcb1;
        color: white;
    }

    .tab-content {
        padding: 0;
    }

    .tab-pane {
        display: none;
        padding: 30px;
    }

    .tab-pane.active {
        display: block;
    }

    .profile-card {
        background: white;
    }

    .card-header {
        margin-bottom: 30px;
    }

    .card-header h3 {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #333;
        margin: 0 0 10px 0;
        font-size: 1.5rem;
    }

    .card-header p {
        color: #6c757d;
        margin: 0;
    }

    .profile-form {
        max-width: 600px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-weight: 500;
    }

    .form-group input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-group input:focus {
        outline: none;
        border-color: #79bcb1;
        box-shadow: 0 0 0 3px rgba(121, 188, 177, 0.1);
    }

    .form-group input[readonly] {
        background: #f8f9fa;
        color: #6c757d;
    }

    .password-input {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: #6c757d;
        font-size: 1.2rem;
    }

    .password-strength {
        margin-top: 8px;
        height: 4px;
        background: #e9ecef;
        border-radius: 2px;
        overflow: hidden;
    }

    .password-strength::after {
        content: '';
        display: block;
        height: 100%;
        width: 0%;
        background: #dc3545;
        transition: all 0.3s ease;
    }

    .password-strength.weak::after {
        width: 33%;
        background: #dc3545;
    }

    .password-strength.medium::after {
        width: 66%;
        background: #ffc107;
    }

    .password-strength.strong::after {
        width: 100%;
        background: #28a745;
    }

    .password-hint {
        display: block;
        margin-top: 5px;
        font-size: 0.8rem;
        color: #6c757d;
    }

    .form-actions {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e9ecef;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .btn-primary {
        background: #ff6c40e4;
        color: white;
    }

    .btn-primary:hover {
        background: #d35400;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 108, 64, 0.4);
    }

    .activity-list {
        space-y: 20px;
    }

    .activity-item {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
        margin-bottom: 15px;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: white;
    }

    .activity-icon.success {
        background: #28a745;
    }

    .activity-icon.info {
        background: #17a2b8;
    }

    .activity-icon.warning {
        background: #ffc107;
    }

    .activity-details h4 {
        margin: 0 0 5px 0;
        color: #333;
    }

    .activity-details p {
        margin: 0 0 5px 0;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .activity-time {
        font-size: 0.8rem;
        color: #adb5bd;
    }

    @media (max-width: 768px) {
        .profile-header {
            flex-direction: column;
            text-align: center;
            padding: 30px 20px;
        }

        .tab-buttons {
            flex-direction: column;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .tab-pane {
            padding: 20px;
        }

        .profile-info h1 {
            font-size: 2rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab functionality
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabPanes = document.querySelectorAll('.tab-pane');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const targetTab = button.getAttribute('data-tab');

                // Remove active class from all buttons and panes
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabPanes.forEach(pane => pane.classList.remove('active'));

                // Add active class to clicked button and corresponding pane
                button.classList.add('active');
                document.getElementById(targetTab).classList.add('active');
            });
        });

        // Password toggle functionality
        const passwordToggles = document.querySelectorAll('.password-toggle');
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', () => {
                const targetId = toggle.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = toggle.querySelector('i');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.className = 'bx bx-show';
                } else {
                    passwordInput.type = 'password';
                    icon.className = 'bx bx-hide';
                }
            });
        });

        // Password strength checker
        const newPasswordInput = document.getElementById('new_password');
        const strengthIndicator = document.getElementById('passwordStrength');

        if (newPasswordInput && strengthIndicator) {
            newPasswordInput.addEventListener('input', () => {
                const password = newPasswordInput.value;
                const strength = checkPasswordStrength(password);

                strengthIndicator.className = `password-strength ${strength}`;
            });
        }

        // Password confirmation validation
        const confirmPasswordInput = document.getElementById('confirm_password');
        if (newPasswordInput && confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', () => {
                if (newPasswordInput.value !== confirmPasswordInput.value) {
                    confirmPasswordInput.setCustomValidity('Passwords do not match');
                } else {
                    confirmPasswordInput.setCustomValidity('');
                }
            });
        }

        // Auto-hide alerts
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });

        // Image upload preview
        const imageUpload = document.getElementById('imageUpload');
        const profileImage = document.getElementById('profileImage');
        
        if (imageUpload && profileImage) {
            imageUpload.addEventListener('change', function(e) {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        profileImage.src = e.target.result;
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    });

    function checkPasswordStrength(password) {
        if (password.length < 8) return 'weak';

        let score = 0;

        // Check for lowercase
        if (/[a-z]/.test(password)) score++;

        // Check for uppercase
        if (/[A-Z]/.test(password)) score++;

        // Check for numbers
        if (/\d/.test(password)) score++;

        // Check for special characters
        if (/[^A-Za-z0-9]/.test(password)) score++;

        // Check length
        if (password.length >= 12) score++;

        if (score < 3) return 'weak';
        if (score < 4) return 'medium';
        return 'strong';
    }

    function uploadImage() {
        const form = document.getElementById('imageUploadForm');
        const fileInput = document.getElementById('imageUpload');
        
        if (fileInput.files.length > 0) {
            // Show a loading indicator
            const overlay = document.querySelector('.avatar-overlay');
            overlay.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i>';
            
            // Submit the form
            form.submit();
        }
    }
</script>

<?php include_once "includes/footer.php"; ?>