<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('../login.php', 'Please log in to access the dashboard');
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../view_approved_workers.php', 'Invalid request method', 'error');
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    redirect('../view_approved_workers.php', 'Invalid security token', 'error');
}

// Validate worker ID
if (!isset($_POST['worker_id']) || !is_numeric($_POST['worker_id'])) {
    redirect('../view_approved_workers.php', 'Invalid worker ID', 'error');
}

$worker_id = (int)$_POST['worker_id'];

try {
    // First, get worker email for notification
    $stmt = $pdo->prepare("SELECT email, fname, lname FROM users WHERE user_id = :worker_id AND usertype = 'worker' LIMIT 1");
    $stmt->bindParam(':worker_id', $worker_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $worker = $stmt->fetch();
    
    if (!$worker) {
        redirect('../view_approved_workers.php', 'Worker not found', 'error');
    }
    
    // Update worker status to disabled
    $stmt = $pdo->prepare("UPDATE users SET access_status = 'disabled' WHERE user_id = :worker_id");
    $stmt->bindParam(':worker_id', $worker_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        // Send notification email
        $subject = "Your account has been disabled";
        $message = "Your FixIt account has been disabled. Please contact admin for more information.";
        
        if (function_exists('sendEmail')) {
            require_once '../includes/send_email.php';
            sendEmail($worker['email'], $worker['fname'], $worker['lname'], $subject, $message);
        }
        
        redirect('../view_approved_workers.php', 'Worker has been disabled successfully', 'success');
    } else {
        redirect('../view_approved_workers.php', 'Failed to disable worker', 'error');
    }
} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    redirect('../view_approved_workers.php', 'A system error occurred', 'error');
}
?>