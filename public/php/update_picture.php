<?php
session_start();
include_once "db.php";
header('Content-Type: application/json'); // Always set JSON header

if (!isset($_SESSION['unique_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$unique_id = $_SESSION['unique_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image'])) {
    $img = $_FILES['profile_image']['name'];
    $img_tmp = $_FILES['profile_image']['tmp_name'];
    
    if (empty($img)) {
        echo json_encode(['success' => false, 'message' => 'No file selected']);
        exit();
    }

    $target_dir = "../images/";
    $target_file = $target_dir . basename($img);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image
    if (getimagesize($img_tmp) === false) {
        echo json_encode(['success' => false, 'message' => 'File is not an image']);
        exit();
    }
    
    // Check file size
    if ($_FILES['profile_image']['size'] > 5000000) {
        echo json_encode(['success' => false, 'message' => 'File too large (max 5MB)']);
        exit();
    }
    
    // Allow certain file formats
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        echo json_encode(['success' => false, 'message' => 'Only JPG, JPEG, PNG & GIF allowed']);
        exit();
    }
    
    // Upload the file
    if (move_uploaded_file($img_tmp, $target_file)) {
        // Update database
        $update_query = "UPDATE users SET img='$img' WHERE unique_id='$unique_id'";
        if (mysqli_query($conn, $update_query)) {
            echo json_encode([
                'success' => true,
                'message' => 'Profile picture updated',
                'newImagePath' => $img
            ]);
            exit();
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . mysqli_error($conn)
            ]);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error uploading file']);
        exit();
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>