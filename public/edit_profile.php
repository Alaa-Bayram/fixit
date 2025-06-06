<?php 
session_start();
include_once "php/db.php";

if (!isset($_SESSION['unique_id'])) {
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

// Update user data if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $region = mysqli_real_escape_string($conn, $_POST['region']);
    $password = isset($_POST['password']) ? mysqli_real_escape_string($conn, $_POST['password']) : '';

    // Build the SQL query for updating user data
    $update_fields = "fname='$fname', lname='$lname', email='$email', phone='$phone', address='$address', region='$region'";

    // Check if a new password is provided and hash it
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $update_fields .= ", password='$hashedPassword'";
    }


    // Construct and execute the update query
    $update_query = "UPDATE users SET $update_fields WHERE unique_id='$unique_id'";

    if (mysqli_query($conn, $update_query)) {
        header("location: profile.php?lang=$lang");
        exit();
    } else {
        $error_message = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $translations['edit_profile'] ?? 'Edit Profile' ?> - FixIt</title>
    <link rel="stylesheet" href="css/profile.css"> <!-- Reusing profile.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php include_once "header.php"; ?>
    <style>
        /* Add these styles to make all inputs consistent */
        .edit-form input[type="text"],
        .edit-form input[type="email"],
        .edit-form input[type="tel"],
        .edit-form select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }
        
        .edit-form select {
            height: 46px;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 1em;
        }
        
        .edit-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #f9684b;
            font-size: 0.9rem;
        }
        
        .info-item.edit-mode {
            flex-direction: column;
            align-items: flex-start;
            padding: 0;
            background: transparent;
            gap: 5px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 576px) {
            .edit-form input[type="text"],
            .edit-form input[type="email"],
            .edit-form input[type="tel"],
            .edit-form select {
                padding: 10px 12px;
                font-size: 14px;
            }
            
            .edit-form select {
                height: 42px;
            }
        }
    </style>
</head>
<body>

<section class="profile-container">
    <div class="profile-header">
        <h1><i class="fas fa-user-edit"></i> <?= $translations['edit_profile'] ?? 'Edit Profile' ?></h1>
        <p><?= $translations['update_your_info'] ?? 'Update your personal information' ?></p>
    </div>

    <div class="profile-card">
        <div class="profile-sidebar">
        <div class="profile-picture">
            <img src="images/<?= htmlspecialchars($user['img']) ?>" alt="<?= $translations['profile_picture'] ?? 'Profile Picture' ?>">
            <label for="profile_image" class="upload-btn">
                <i class="fas fa-camera"></i> <?= $translations['change_photo'] ?? 'Change Photo' ?>
            </label>
            <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display: none;">
        </div>
            
            <div class="profile-actions">
                <a href="profile.php?lang=<?= $lang ?>" class="edit-btn">
                    <i class="fas fa-arrow-left"></i> <?= $translations['back_to_profile'] ?? 'Back to Profile' ?>
                </a>
            </div>
        </div>

        <div class="profile-content">
            <form action="" method="post" enctype="multipart/form-data" class="edit-form">
                <div class="profile-section">
                    <h2><i class="fas fa-id-card"></i> <?= $translations['personal_info'] ?? 'Personal Information' ?></h2>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="error-message">
                            <?= $error_message ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="info-grid">
                        <div class="info-item edit-mode">
                            <label for="fname"><?= $translations['first_name'] ?? 'First Name' ?>:</label>
                            <input type="text" id="fname" name="fname" value="<?= htmlspecialchars($user['fname']) ?>" required>
                        </div>
                        
                        <div class="info-item edit-mode">
                            <label for="lname"><?= $translations['last_name'] ?? 'Last Name' ?>:</label>
                            <input type="text" id="lname" name="lname" value="<?= htmlspecialchars($user['lname']) ?>" required>
                        </div>
                        
                        <div class="info-item edit-mode">
                            <label for="email"><?= $translations['email'] ?? 'Email' ?>:</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        
                        <div class="info-item edit-mode">
                            <label for="phone"><?= $translations['phone'] ?? 'Phone' ?>:</label>
                            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                        </div>
                        
                        <div class="info-item edit-mode">
                            <label for="region"><?= $translations['region'] ?? 'Region' ?>:</label>
                            <select id="region" name="region" required>
                                <option value="Beirut" <?= $user['region'] == 'Beirut' ? 'selected' : '' ?>><?= $translations['beirut'] ?? 'Beirut' ?></option>
                                <option value="Baalbek-Hermel" <?= $user['region'] == 'Baalbek-Hermel' ? 'selected' : '' ?>><?= $translations['baalbek_hermel'] ?? 'Baalbek-Hermel' ?></option>
                                <option value="Bekaa" <?= $user['region'] == 'Bekaa' ? 'selected' : '' ?>><?= $translations['bekaa'] ?? 'Bekaa' ?></option>
                                <option value="South Lebanon" <?= $user['region'] == 'South Lebanon' ? 'selected' : '' ?>><?= $translations['south_lebanon'] ?? 'South Lebanon' ?></option>
                                <option value="Nabatieh" <?= $user['region'] == 'Nabatieh' ? 'selected' : '' ?>><?= $translations['nabatieh'] ?? 'Nabatieh' ?></option>
                                <option value="Mount Lebanon" <?= $user['region'] == 'Mount Lebanon' ? 'selected' : '' ?>><?= $translations['mount_lebanon'] ?? 'Mount Lebanon' ?></option>
                                <option value="North Lebanon" <?= $user['region'] == 'North Lebanon' ? 'selected' : '' ?>><?= $translations['north_lebanon'] ?? 'North Lebanon' ?></option>
                                <option value="Akkar" <?= $user['region'] == 'Akkar' ? 'selected' : '' ?>><?= $translations['akkar'] ?? 'Akkar' ?></option>
                            </select>
                        </div>
                        
                        <div class="info-item edit-mode">
                            <label for="address"><?= $translations['address'] ?? 'Address' ?>:</label>
                            <input type="text" id="address" name="address" value="<?= htmlspecialchars($user['address']) ?>" required>
                        </div>
                    </div>
                </div>

                <div class="profile-section">
                    <h2><i class="fas fa-lock"></i> <?= $translations['security_settings'] ?? 'Security Settings' ?></h2>
                    
                    <div class="info-grid">
                        <div class="info-item edit-mode">
                            <label for="password"><?= $translations['new_password'] ?? 'New Password' ?>:</label>
                            <div class="password-input-container">
                                <input type="password" id="password" name="password" placeholder="<?= $translations['leave_blank'] ?? 'Leave blank to keep current password' ?>">
                                <i class="fas fa-eye toggle-password" data-target="password"></i>
                            </div>
                            <div class="password-strength-meter">
                                <span class="strength-bar"></span>
                                <span class="strength-bar"></span>
                                <span class="strength-bar"></span>
                                <span class="strength-text"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="reset" class="cancel-btn"><?= $translations['reset'] ?? 'Reset' ?></button>
                    <button type="submit" class="save-btn"><?= $translations['save_changes'] ?? 'Save Changes' ?></button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });
    }
    
// Replace the existing imgInput event listener with this:
const imgInput = document.getElementById('profile_image');
if (imgInput) {
    imgInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            // First show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                document.querySelector('.profile-picture img').src = e.target.result;
            }
            reader.readAsDataURL(this.files[0]);
            
            // Then upload via AJAX
            const formData = new FormData();
            formData.append('profile_image', this.files[0]);
            
            fetch('php/update_picture.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('<?= $translations['profile_picture_updated'] ?? 'Profile picture updated' ?>', 'success');
                } else {
                    showToast(data.message || '<?= $translations['upload_failed'] ?? 'Upload failed' ?>', 'error');
                    // Revert to original image if upload failed
                    document.querySelector('.profile-picture img').src = `images/${'<?= htmlspecialchars($user['img']) ?>'}`;
                }
            })
            .catch(error => {
                showToast('<?= $translations['upload_error'] ?? 'Error uploading picture' ?>', 'error');
                console.error('Error:', error);
            });
        }
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
    
    // Toast notification function
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast-notification ${type}`;
        toast.textContent = message;
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