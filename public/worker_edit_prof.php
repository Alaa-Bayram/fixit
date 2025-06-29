<?php 
session_start();
include_once "php/db.php";

if (!isset($_SESSION['unique_id'])) {
    header("location: login.html");
    exit();
}

  $lang = 'en'; 

if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
}

$lang_file = "lang/{$lang}.php";
if (file_exists($lang_file)) {
    $translations = include $lang_file;
} else {
    $translations = include "lang/en.php";
}

$unique_id = $_SESSION['unique_id'];

// Fetch user data
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
    $img = $_FILES['img']['name'];
    $img_tmp = $_FILES['img']['tmp_name'];

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $update_query = "UPDATE users SET fname='$fname', lname='$lname', email='$email', phone='$phone', address='$address', region='$region', password='$hashedPassword' WHERE unique_id='$unique_id'";
    } else {
        if (!empty($img)) {
            move_uploaded_file($img_tmp, "php/images/$img");
            $update_query = "UPDATE users SET fname='$fname', lname='$lname', email='$email', phone='$phone', address='$address', region='$region', img='$img' WHERE unique_id='$unique_id'";
        } else {
            $update_query = "UPDATE users SET fname='$fname', lname='$lname', email='$email', phone='$phone', address='$address', region='$region' WHERE unique_id='$unique_id'";
        }
    }

    if (mysqli_query($conn, $update_query)) {
        header("location: worker_profile.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Add these styles to make all inputs consistent */
        .info-item.edit-mode input[type="text"],
        .info-item.edit-mode input[type="email"],
        .info-item.edit-mode input[type="tel"],
        .info-item.edit-mode input[type="password"],
        .info-item.edit-mode input[type="file"],
        .info-item.edit-mode select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            margin-bottom: 15px;
            box-sizing: border-box;
            background-color: white;
        }
        
        .info-item.edit-mode select {
            height: 46px;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 1em;
        }
        
        .info-item.edit-mode .info-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #f9684b;
            font-size: 0.9rem;
        }
        
        .info-item.edit-mode {
            flex-direction: column;
            align-items: flex-start;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            gap: 5px;
            margin-bottom: 15px;
        }
        
        /* Password toggle button */
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #777;
        }
        
        /* File input styling */
        .info-item.edit-mode input[type="file"] {
            padding: 8px 15px;
        }
        
        /* Password strength meter */
        .password-strength-meter {
            margin-top: 10px;
            display: flex;
            align-items: center;
        }
        
        .strength-bar {
            height: 5px;
            flex: 1;
            margin-right: 5px;
            background-color: #e0e0e0;
            border-radius: 2px;
            transition: background-color 0.3s;
        }
        
        .strength-text {
            margin-left: 10px;
            font-size: 14px;
            font-weight: 600;
        }
        
        /* Toast notification */
        .toast-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 5px;
            color: white;
            background: #333;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 1000;
            max-width: 300px;
        }
        
        .toast-notification.show {
            transform: translateY(0);
            opacity: 1;
        }
        
        .toast-notification.success {
            background: #4CAF50;
        }
        
        .toast-notification.error {
            background: #f44336;
        }
        
        /* Responsive adjustments */
        @media (max-width: 576px) {
            .info-item.edit-mode input[type="text"],
            .info-item.edit-mode input[type="email"],
            .info-item.edit-mode input[type="tel"],
            .info-item.edit-mode input[type="password"],
            .info-item.edit-mode input[type="file"],
            .info-item.edit-mode select {
                padding: 10px 12px;
                font-size: 14px;
            }
            
            .info-item.edit-mode select {
                height: 42px;
            }
            
            .toast-notification {
                max-width: calc(100% - 40px);
                left: 20px;
                right: auto;
            }
        }
    </style>
</head>
<body>
<?php include_once "worker_header.php"; ?>

<div class="profile-container">
    <div class="profile-header">
        <h1><?= $translations['edit_profile'] ?? 'Edit Profile' ?></h1>
        <p><?= $translations['update_your_info'] ?? 'Update your personal information' ?></p>
    </div>

    <div class="profile-card">
        <div class="profile-sidebar">
            <div class="profile-picture">
                <img src="php/images/<?php echo htmlspecialchars($user['img']); ?>" alt="Profile Picture">
            </div>
        </div>

        <div class="profile-content">
            <form action="" method="post" enctype="multipart/form-data" class="profile-section" onsubmit="return validateForm()">
                <h2><?= $translations['personal_info'] ?? 'Personal Information' ?></h2>
                
                <div class="info-grid">
                    <div class="info-item edit-mode">
                        <span class="info-label"><?= $translations['first_name'] ?? 'First Name' ?>:</span>
                        <input type="text" name="fname" value="<?php echo htmlspecialchars($user['fname']); ?>" required>
                    </div>
                    
                    <div class="info-item edit-mode">
                        <span class="info-label"><?= $translations['last_name'] ?? 'Last Name' ?>:</span>
                        <input type="text" name="lname" value="<?php echo htmlspecialchars($user['lname']); ?>" required>
                    </div>
                    
                    <div class="info-item edit-mode">
                        <span class="info-label"><?= $translations['email'] ?? 'Email' ?>:</span>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="info-item edit-mode">
                        <span class="info-label"><?= $translations['phone'] ?? 'Phone' ?>:</span>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                    </div>
                    
                    <div class="info-item edit-mode">
                        <span class="info-label"><?= $translations['address'] ?? 'Address' ?>:</span>
                        <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>
                    </div>

                    <div class="info-item edit-mode">
                        <span class="info-label"><?= $translations['region'] ?? 'Region' ?>:</span>
                        <select id="region" name="region" required>
                            <option value="Beirut" <?= $user['region'] == 'Beirut' ? 'selected' : '' ?>>Beirut</option>
                            <option value="Baalbek-Hermel" <?= $user['region'] == 'Baalbek-Hermel' ? 'selected' : '' ?>>Baalbek-Hermel</option>
                            <option value="Bekaa" <?= $user['region'] == 'Bekaa' ? 'selected' : '' ?>>Bekaa</option>
                            <option value="South Lebanon" <?= $user['region'] == 'South Lebanon' ? 'selected' : '' ?>>South Lebanon</option>
                            <option value="Nabatieh" <?= $user['region'] == 'Nabatieh' ? 'selected' : '' ?>>Nabatieh</option>
                            <option value="Mount Lebanon" <?= $user['region'] == 'Mount Lebanon' ? 'selected' : '' ?>>Mount Lebanon</option>
                            <option value="North Lebanon" <?= $user['region'] == 'North Lebanon' ? 'selected' : '' ?>>North Lebanon</option>
                            <option value="Akkar" <?= $user['region'] == 'Akkar' ? 'selected' : '' ?>>Akkar</option>
                        </select>
                    </div>
                    
                    <div class="info-item edit-mode">
                        <span class="info-label"><?= $translations['profile_picture'] ?? 'Profile Picture' ?>:</span>
                        <input type="file" name="img" id="img">
                    </div>
                    
                    <div class="info-item edit-mode password-input-container" style="position: relative;">
                        <span class="info-label"><?= $translations['new_password'] ?? 'New Password' ?>:</span>
                        <input type="password" name="password" id="password" placeholder="Leave blank to keep current" oninput="checkPasswordStrength(this.value)">
                        <span class="password-toggle" onclick="togglePassword()"><i class="fas fa-eye" style="margin-right: 30px;"></i></span>
                        <div class="password-strength-meter">
                            <div class="strength-bar"></div>
                            <div class="strength-bar"></div>
                            <div class="strength-bar"></div>
                            <div class="strength-bar"></div>
                            <div class="strength-bar"></div>
                            <span class="strength-text"></span>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="window.location.href='worker_profile.php'">Cancel</button>
                    <button type="submit" class="save-btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordField = document.getElementById('password');
    const icon = document.querySelector('.password-toggle i');
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

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
    const strengthMessages = ['Very Weak', 'Weak', 'Medium', 'Strong', 'Very Strong'];
    strengthText.textContent = strengthMessages[strength] || '';
    strengthText.style.color = getStrengthColor(strength);
}

function getStrengthColor(strength) {
    const colors = ['#ff3e36', '#ff691f', '#ffda36', '#0be881', '#05c46b'];
    return colors[strength] || '#e0e0e0';
}

function validateForm() {
    const password = document.getElementById('password').value;
    
    if (password && password.length < 8) {
        showToast('Password should be at least 8 characters long', 'error');
        return false;
    }
    
    showToast('Profile updated successfully!', 'success');
    return true;
}

function showToast(message, type = 'success') {
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
</script>
</body>
</html>