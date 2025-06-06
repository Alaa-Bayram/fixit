<?php
session_start();
include_once "db.php";

if (!isset($_SESSION['unique_id'])) {
    header("location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = mysqli_real_escape_string($conn, $_POST['current_password']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $lang = isset($_POST['lang']) ? $_POST['lang'] : 'en';
    
    $unique_id = $_SESSION['unique_id'];
    $query = "SELECT password FROM users WHERE unique_id = '$unique_id'";
    $result = mysqli_query($conn, $query);
    
    if (!$result || mysqli_num_rows($result) == 0) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }
    
    $user = mysqli_fetch_assoc($result);
    
    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        $message = ($lang == 'ar') ? 'كلمة المرور الحالية غير صحيحة' : 'Current password is incorrect';
        echo json_encode(['success' => false, 'message' => $message]);
        exit();
    }
    
    // Check if new password matches confirmation
    if ($new_password !== $confirm_password) {
        $message = ($lang == 'ar') ? 'كلمة المرور الجديدة غير متطابقة' : 'New passwords do not match';
        echo json_encode(['success' => false, 'message' => $message]);
        exit();
    }
    
    // Check password strength (minimum 8 characters)
    if (strlen($new_password) < 8) {
        $message = ($lang == 'ar') ? 'كلمة المرور يجب أن تكون 8 أحرف على الأقل' : 'Password must be at least 8 characters';
        echo json_encode(['success' => false, 'message' => $message]);
        exit();
    }
    
    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password in database
    $update_query = "UPDATE users SET password = '$hashed_password' WHERE unique_id = '$unique_id'";
    
    if (mysqli_query($conn, $update_query)) {
        $message = ($lang == 'ar') ? 'تم تحديث كلمة المرور بنجاح' : 'Password updated successfully';
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        $message = ($lang == 'ar') ? 'فشل تحديث كلمة المرور' : 'Failed to update password';
        echo json_encode(['success' => false, 'message' => $message]);
    }
} else {
    header("location: profile.php");
    exit();
}
?>