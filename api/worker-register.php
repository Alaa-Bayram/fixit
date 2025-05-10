<?php
header('Content-Type: application/json');
session_start();
include_once "../db.php";

$response = ['success' => false, 'message' => ''];

try {
    // Required fields
    $required = ['fname', 'lname', 'email', 'phone', 'address',  'experience'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("All required fields must be filled.");
        }
    }

    // Validate email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format.");
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->execute([$_POST['email']]);
    if ($stmt->rowCount() > 0) {
        throw new Exception("This email already exists!");
    }

    // Handle file uploads
    if (!isset($_FILES['image']) || !isset($_FILES['pdf'])) {
        throw new Exception("Both ID proof and CV are required.");
    }

    // Process image (ID proof)
    $img = $_FILES['image'];
    $img_ext = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));
    $allowed_img = ['jpeg', 'jpg', 'png'];
    if (!in_array($img_ext, $allowed_img)) {
        throw new Exception("Only JPG, JPEG, PNG images are allowed for ID proof.");
    }

    // Process PDF (CV)
    $pdf = $_FILES['pdf'];
    $pdf_ext = strtolower(pathinfo($pdf['name'], PATHINFO_EXTENSION));
    if ($pdf_ext !== 'pdf') {
        throw new Exception("Only PDF files are allowed for CV.");
    }

    // Generate unique filenames
    $time = time();
    $new_img_name = $time . '_' . basename($img['name']);
    $new_pdf_name = $time . '_' . basename($pdf['name']);

    // Move uploaded files
    $img_path = "../public/images/" . $new_img_name;
    $pdf_path = "../admin/cv/" . $new_pdf_name;

    if (!move_uploaded_file($img['tmp_name'], $img_path)) {
        throw new Exception("Failed to upload ID image.");
    }

    if (!move_uploaded_file($pdf['tmp_name'], $pdf_path)) {
        unlink($img_path); // Clean up the already uploaded image if PDF fails
        throw new Exception("Failed to upload CV.");
    }

    // Get skill title
    $stmt = $conn->prepare("SELECT title FROM services WHERE service_id = ?");
    $stmt->execute([$_POST['service_id']]);
    $skill = $stmt->fetchColumn();

    // Hash password
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users 
        (unique_id, fname, lname, email, password, phone, skills, service_id, 
         experience, region, address, fees, img, pdf, status, usertype, access_status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $unique_id = rand(time(), 100000000);
    $status = "Offline Now";
    $success = $stmt->execute([
        $unique_id,
        $_POST['fname'],
        $_POST['lname'],
        $_POST['email'],
        $password,
        $_POST['phone'],
        $skill,
        $_POST['service_id'],
        $_POST['experience'],
        $_POST['region'] ?? 'Unknown',
        $_POST['address'],
        $_POST['fees'] ?? 0,
        $new_img_name,
        $pdf_path,
        $status,
        $_POST['usertype'] ?? 'worker',
        $_POST['access_status'] ?? 'pending'
    ]);

    if (!$success) {
        // Clean up uploaded files if DB insert fails
        unlink($img_path);
        unlink($pdf_path);
        throw new Exception("Database error. Please try again.");
    }

    $response['success'] = true;
    $response['message'] = "Registration successful! Your account is pending approval.";
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>