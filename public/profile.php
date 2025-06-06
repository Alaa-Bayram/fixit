<?php 
session_start();
include_once "php/db.php";
if(!isset($_SESSION['unique_id'])){
    header("location: login.html");
    exit();
}

// Set language from URL or session
$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en');
$_SESSION['lang'] = $lang;

// Include translations
$lang_file = "lang/$lang.php";
$translations = file_exists($lang_file) ? include($lang_file) : include("lang/en.php");

$unique_id = $_SESSION['unique_id'];
$query = "SELECT * FROM users WHERE unique_id = '$unique_id'";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $translations['profile'] ?? 'Profile' ?> - FixIt</title>
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php include_once "header.php"; ?>
    <style>
        .toast-notification.success {
            background-color: #f9684b !important;
            color: white !important;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
            animation: modalFadeIn 0.3s ease-out;
        }
        
        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #777;
            transition: color 0.2s;
        }
        
        .close-modal:hover {
            color: #333;
        }
        
        body.modal-open {
            overflow: hidden;
        }
    </style>
</head>
<body>

<section class="profile-container">
    <div class="profile-header">
        <h1><i class="fas fa-user-circle"></i> <?= $translations['profile'] ?? 'My Profile' ?></h1>
        <p><?= $translations['welcome_back'] ?? 'Welcome back' ?>, <?= htmlspecialchars($user['fname']) ?>!</p>
    </div>

    <div class="profile-card">
        <div class="profile-sidebar">
            <div class="profile-picture">
                <img src="images/<?= htmlspecialchars($user['img']) ?>" alt="<?= $translations['profile_picture'] ?? 'Profile Picture' ?>">
                <form id="pictureForm" action="php/update_picture.php" method="POST" enctype="multipart/form-data">
                    <label for="profileImage" class="upload-btn">
                        <i class="fas fa-camera"></i> <?= $translations['change_photo'] ?? 'Change Photo' ?>
                    </label>
                    <input type="file" id="profileImage" name="profile_image" accept="image/*" style="display: none;">
                    <input type="hidden" name="lang" value="<?= $lang ?>">
                </form>
            </div>
            <div class="profile-actions">
                <a href="edit_profile.php?lang=<?= $lang ?>" class="edit-btn">
                    <i class="fas fa-edit"></i> <?= $translations['edit_profile'] ?? 'Edit Profile' ?>
                </a>
                <button id="changePasswordBtn" class="password-btn">
                    <i class="fas fa-lock"></i> <?= $translations['change_password'] ?? 'Change Password' ?>
                </button>
            </div>
        </div>
        
        <div class="profile-content">
            <div class="profile-section">
                <h2><i class="fas fa-id-card"></i> <?= $translations['personal_info'] ?? 'Personal Information' ?></h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label"><?= $translations['first_name'] ?? 'First Name' ?>:</span>
                        <span class="info-value"><?= htmlspecialchars($user['fname']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><?= $translations['last_name'] ?? 'Last Name' ?>:</span>
                        <span class="info-value"><?= htmlspecialchars($user['lname']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><?= $translations['email'] ?? 'Email' ?>:</span>
                        <span class="info-value"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><?= $translations['phone'] ?? 'Phone' ?>:</span>
                        <span class="info-value"><?= htmlspecialchars($user['phone']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><?= $translations['region'] ?? 'Region' ?>:</span>
                        <span class="info-value"><?= htmlspecialchars($user['region']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><?= $translations['address'] ?? 'Address' ?>:</span>
                        <span class="info-value"><?= htmlspecialchars($user['address']) ?></span>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <h2><i class="fas fa-user-shield"></i> <?= $translations['account_security'] ?? 'Account Security' ?></h2>
                <div class="security-info">
                    <div class="security-item">
                        <i class="fas fa-check-circle"></i>
                        <span><p class="member-since"><?= $translations['member_since'] ?? 'Member since' ?>: <?= date('F Y', strtotime($user['created_at'])) ?></p></span>
                    </div>
                    <div class="security-item">
                        <i class="fas fa-shield-alt"></i>
                        <span> <?= $translations['account_status'] ?? 'Account status' ?>: <span class="status-active"><?= $translations['active'] ?? 'Active' ?></span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Password Change Modal -->
<div id="passwordModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2><i class="fas fa-key"></i> <?= $translations['change_password'] ?? 'Change Password' ?></h2>
        <form id="passwordForm" action="php/update_password.php" method="POST">
            <input type="hidden" name="lang" value="<?= $lang ?>">
            
            <div class="form-group">
                <label for="currentPassword"><?= $translations['current_password'] ?? 'Current Password' ?>:</label>
                <div class="password-input-container">
                    <input type="password" id="currentPassword" name="current_password" required>
                    <i class="fas fa-eye toggle-password" data-target="currentPassword"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="newPassword"><?= $translations['new_password'] ?? 'New Password' ?>:</label>
                <div class="password-input-container">
                    <input type="password" id="newPassword" name="new_password" required 
                           title="<?= $translations['password_requirements'] ?? 'Must contain at least one number, one uppercase and lowercase letter, and at least 8 or more characters' ?>">
                    <i class="fas fa-eye toggle-password" data-target="newPassword"></i>
                </div>
                <div class="password-strength-meter">
                    <span class="strength-bar"></span>
                    <span class="strength-bar"></span>
                    <span class="strength-bar"></span>
                    <span class="strength-text"></span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirmPassword"><?= $translations['confirm_password'] ?? 'Confirm New Password' ?>:</label>
                <div class="password-input-container">
                    <input type="password" id="confirmPassword" name="confirm_password" required>
                    <i class="fas fa-eye toggle-password" data-target="confirmPassword"></i>
                </div>
                <span id="passwordMatch" class="validation-message"></span>
            </div>
            
            <div class="form-actions">
                <button type="button" class="cancel-btn"><?= $translations['cancel'] ?? 'Cancel' ?></button>
                <button type="submit" class="save-btn"><?= $translations['save_changes'] ?? 'Save Changes' ?></button>
            </div>
        </form>
    </div>
</div>

<?php include_once "footer.html"; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password modal functionality
        const changePasswordBtn = document.getElementById('changePasswordBtn');
        const passwordModal = document.getElementById('passwordModal');
        const closeModal = document.querySelector('.close-modal');
        const cancelBtn = document.querySelector('.cancel-btn');
        
        if (changePasswordBtn) {
            changePasswordBtn.addEventListener('click', function() {
                passwordModal.style.display = 'flex';
                document.body.classList.add('modal-open');
            });
        }
        
        // Close modal when clicking X, cancel button, or outside modal
        [closeModal, cancelBtn].forEach(element => {
            if (element) {
                element.addEventListener('click', function() {
                    passwordModal.style.display = 'none';
                    document.body.classList.remove('modal-open');
                    resetPasswordForm();
                });
            }
        });
        
        window.addEventListener('click', function(event) {
            if (event.target === passwordModal) {
                passwordModal.style.display = 'none';
                document.body.classList.remove('modal-open');
                resetPasswordForm();
            }
        });
        
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye-slash');
            });
        });
        
        // Password strength meter
        const newPasswordInput = document.getElementById('newPassword');
        if (newPasswordInput) {
            newPasswordInput.addEventListener('input', function() {
                checkPasswordStrength(this.value);
            });
        }
        
        // Password confirmation check
        const confirmPasswordInput = document.getElementById('confirmPassword');
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                checkPasswordMatch();
            });
        }
        
        // Handle profile picture upload
        const profileImageInput = document.getElementById('profileImage');
        if (profileImageInput) {
            profileImageInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    // Show loading indicator
                    const uploadBtn = document.querySelector('.upload-btn');
                    const originalText = uploadBtn.innerHTML;
                    uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= $translations['uploading'] ?? 'Uploading...' ?>';
                    uploadBtn.style.pointerEvents = 'none';
                    
                    // Validate file size (max 2MB)
                    if (this.files[0].size > 2 * 1024 * 1024) {
                        showToast('<?= $translations['file_too_large'] ?? 'File too large. Maximum size is 2MB.' ?>', 'error');
                        uploadBtn.innerHTML = originalText;
                        uploadBtn.style.pointerEvents = 'auto';
                        this.value = '';
                        return;
                    }
                    
                    // Validate file type
                    const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!validTypes.includes(this.files[0].type)) {
                        showToast('<?= $translations['invalid_file_type'] ?? 'Invalid file type. Only JPG, PNG, and GIF are allowed.' ?>', 'error');
                        uploadBtn.innerHTML = originalText;
                        uploadBtn.style.pointerEvents = 'auto';
                        this.value = '';
                        return;
                    }
                    
                    // Preview image before upload
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.querySelector('.profile-picture img').src = e.target.result;
                    }
                    reader.readAsDataURL(this.files[0]);
                    
                    // Upload the file
                    const form = document.getElementById('pictureForm');
                    const formData = new FormData(form);
                    
                    fetch(form.action, {
                        method: 'POST',
                        body: formData
                    })
                    .then(async response => {
                        const contentType = response.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            const text = await response.text();
                            throw new Error(`Expected JSON, got: ${text.substring(0, 100)}...`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success', '#f9684b');
                            if (data.newImagePath) {
                                document.querySelector('.profile-picture img').src = 'images/' + data.newImagePath;
                            }
                        } else {
                            showToast(data.message || 'Update failed', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast(error.message || 'An error occurred', 'error');
                    })
                    .finally(() => {
                        // Reset loading state
                        const uploadBtn = document.querySelector('.upload-btn');
                        uploadBtn.innerHTML = '<i class="fas fa-camera"></i> <?= $translations['change_photo'] ?? 'Change Photo' ?>';
                        uploadBtn.style.pointerEvents = 'auto';
                    });
                }
            });
        }
        
        // Handle password form submission
        const passwordForm = document.getElementById('passwordForm');
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!validatePasswordForm()) {
                    return;
                }
                
                const formData = new FormData(this);
                
                // Show loading state
                const submitBtn = this.querySelector('.save-btn');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= $translations['processing'] ?? 'Processing...' ?>';
                submitBtn.disabled = true;
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showToast(data.message || '<?= $translations['password_updated'] ?? 'Password updated successfully' ?>', 'success');
                        passwordModal.style.display = 'none';
                        document.body.classList.remove('modal-open');
                        resetPasswordForm();
                    } else {
                        showToast(data.message || '<?= $translations['password_update_failed'] ?? 'Failed to update password' ?>', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('<?= $translations['server_error'] ?? 'Server error. Please try again.' ?>', 'error');
                })
                .finally(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            });
        }
        
        // Password strength checker
        function checkPasswordStrength(password) {
            const strengthBars = document.querySelectorAll('.strength-bar');
            const strengthText = document.querySelector('.strength-text');
            
            // Reset all bars
            strengthBars.forEach(bar => {
                bar.style.backgroundColor = '#e0e0e0';
            });
            
            if (!password) {
                strengthText.textContent = '';
                return;
            }
            
            // Calculate strength
            let strength = 0;
            if (password.length > 7) strength += 1;
            if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 1;
            if (password.match(/([0-9])/)) strength += 1;
            if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1;
            
            // Update UI
            for (let i = 0; i < strength; i++) {
                strengthBars[i].style.backgroundColor = getStrengthColor(strength);
            }
            
            // Update text
            const strengthMessages = [
                '<?= $translations['very_weak'] ?? 'Very Weak' ?>',
                '<?= $translations['weak'] ?? 'Weak' ?>',
                '<?= $translations['medium'] ?? 'Medium' ?>',
                '<?= $translations['strong'] ?? 'Strong' ?>',
                '<?= $translations['very_strong'] ?? 'Very Strong' ?>'
            ];
            strengthText.textContent = strengthMessages[strength];
            strengthText.style.color = getStrengthColor(strength);
        }
        
        function getStrengthColor(strength) {
            const colors = ['#ff3e36', '#ff691f', '#ffda36', '#0be881', '#05c46b'];
            return colors[strength] || '#e0e0e0';
        }
        
        // Password match checker
        function checkPasswordMatch() {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const matchMessage = document.getElementById('passwordMatch');
            
            if (!newPassword || !confirmPassword) {
                matchMessage.textContent = '';
                return;
            }
            
            if (newPassword === confirmPassword) {
                matchMessage.textContent = '<?= $translations['passwords_match'] ?? 'Passwords match' ?>';
                matchMessage.style.color = '#05c46b';
            } else {
                matchMessage.textContent = '<?= $translations['passwords_dont_match'] ?? 'Passwords do not match' ?>';
                matchMessage.style.color = '#ff3e36';
            }
        }
        
        // Validate password form
        function validatePasswordForm() {
            let isValid = true;
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (!currentPassword) {
                showToast('<?= $translations['enter_current_password'] ?? 'Please enter your current password' ?>', 'error');
                isValid = false;
            }
            
            if (!newPassword) {
                showToast('<?= $translations['enter_new_password'] ?? 'Please enter a new password' ?>', 'error');
                isValid = false;
            } else if (newPassword.length < 8) {
                showToast('<?= $translations['password_too_short'] ?? 'Password must be at least 8 characters' ?>', 'error');
                isValid = false;
            }
            
            if (newPassword !== confirmPassword) {
                showToast('<?= $translations['passwords_must_match'] ?? 'New passwords must match' ?>', 'error');
                isValid = false;
            }
            
            return isValid;
        }
        
        // Reset password form
        function resetPasswordForm() {
            const form = document.getElementById('passwordForm');
            if (form) form.reset();
            
            document.querySelectorAll('.strength-bar').forEach(bar => {
                bar.style.backgroundColor = '#e0e0e0';
            });
            
            document.querySelector('.strength-text').textContent = '';
            document.getElementById('passwordMatch').textContent = '';
        }
        
        // Toast notification function
        function showToast(message, type = 'info', color = null) {
            const toast = document.createElement('div');
            toast.className = `toast-notification ${type}`;
            toast.textContent = message;
            
            // Apply custom color if provided
            if (color && type === 'success') {
                toast.style.backgroundColor = color;
            }
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('show');
            }, 10);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 5000);
        }
    });
</script>
</body>
</html>