<?php
require_once 'includes/config.php';
require_once 'includes/functions.php'; // Assuming you have a functions file

// Security headers (add to config.php if possible)
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// Redirect if already logged in
if (is_admin_logged_in()) {
    redirect('index.php');
}

// Initialize CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        set_flash_message('error', 'Invalid form submission. Please try again.');
        redirect('login.php');
    }
    
    // Validate inputs
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash_message('error', 'Please provide a valid email address.');
        redirect('login.php');
    }
    
    // Validate password length (adjust minimum as needed)
    if (strlen($password) < 8) {
        set_flash_message('error', 'Invalid credentials.');
        redirect('login.php');
    }
    
    try {
        // Find user with prepared statement
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND usertype = 'admin' LIMIT 1");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Update user status
            $stmt = $pdo->prepare("UPDATE users SET status = 'Active now' WHERE unique_id = :unique_id");
            $stmt->bindParam(':unique_id', $user['unique_id'], PDO::PARAM_INT);
            $stmt->execute();
            
            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['unique_id'] = $user['unique_id'];
            $_SESSION['usertype'] = $user['usertype'];
            $_SESSION['last_activity'] = time();
            
            // Create new CSRF token
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            
            // Set secure cookie parameters
            setcookie(session_name(), session_id(), [
                'expires' => time() + 3600, // 1 hour
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'],
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            
            redirect('index.php');
        } else {
            // Generic error message to prevent user enumeration
            set_flash_message('error', 'Invalid credentials.');
            redirect('login.php');
        }
    } catch (PDOException $e) {
        error_log('Login Error: ' . $e->getMessage());
        set_flash_message('error', 'A system error occurred. Please try again later.');
        redirect('login.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="Admin login for FixIt system">
    <title>Admin Login - FixIt</title>

    <!-- Security-relevant meta tags -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' https://unpkg.com; style-src 'self' 'unsafe-inline' https://unpkg.com https://cdn.jsdelivr.net; img-src 'self' data:; font-src 'self' https://unpkg.com https://cdn.jsdelivr.net;">

    <!-- Preload critical resources -->
    <link rel="preload" href="assets/css/login.css" as="style">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" as="style">
    <link rel="preload" href="assets/images/logo1.png" as="image">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="background" aria-hidden="true">
        <img src="assets/images/wp1.jpg" alt="" loading="lazy">
    </div>

    <main class="login-container">
        <div class="login-wrapper">
            <div class="logo">
                <img src="assets/images/logo1.png" alt="FixIt Logo" width="150" height="auto">
            </div>
            <?php if (has_flash_message('error')): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars(get_flash_message('error'), ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
            
            <?php if (has_flash_message('success')): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars(get_flash_message('success'), ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
            
            <form id="loginForm" action="login.php" method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="input-field">
                    <i class="bx bx-envelope" aria-hidden="true"></i>
                    <input type="email" name="email" id="email" required autocomplete="email" aria-describedby="emailHelp">
                    <label for="email">Email Address</label>
                    <span id="emailHelp" class="visually-hidden">Please enter your admin email address</span>
                </div>
                
                <div class="input-field">
                    <i class="bx bx-lock-alt" aria-hidden="true"></i>
                    <input type="password" name="password" id="password" required autocomplete="current-password" minlength="8" aria-describedby="passwordHelp">
                    <label for="password">Password</label>
                    <span id="passwordHelp" class="visually-hidden">Please enter your password (minimum 8 characters)</span>
                </div>
                
                <button type="submit" class="login-btn">
                    <span class="btn-text">Log In</span>
                    <span class="btn-loader" aria-hidden="true"></span>
                </button>
                
                <div class="links">
                    <a href="forgot-password.php" class="text-link">Forgot Password?</a>
                </div>
            </form>
        </div>
    </main>

    <!-- Loading indicator for better UX -->
    <div class="page-loader" aria-hidden="true"></div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-hide alerts
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
        
        // Form submission handler
        const form = document.getElementById('loginForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.querySelector('.btn-text').textContent = 'Logging in...';
                    submitBtn.querySelector('.btn-loader').style.display = 'inline-block';
                }
            });
        }
        
        // Add password visibility toggle
        const passwordField = document.getElementById('password');
        if (passwordField) {
            const toggle = document.createElement('button');
            toggle.type = 'button';
            toggle.className = 'password-toggle';
            toggle.innerHTML = '<i class="bx bx-hide" aria-hidden="true"></i>';
            toggle.setAttribute('aria-label', 'Show password');
            passwordField.parentNode.appendChild(toggle);
            
            toggle.addEventListener('click', function() {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                this.innerHTML = type === 'password' ? 
                    '<i class="bx bx-hide" aria-hidden="true"></i>' : 
                    '<i class="bx bx-show" aria-hidden="true"></i>';
                this.setAttribute('aria-label', type === 'password' ? 'Show password' : 'Hide password');
            });
        }
    });
    </script>
</body>
</html>