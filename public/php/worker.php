<?php
ob_start(); // Start output buffering
header('Content-Type: application/json');

session_start();
include_once "db.php";

// Initialize response
$response = ['success' => false, 'message' => ''];

try {
    // Check required POST fields
    $required = ['fname', 'lname', 'email', 'phone', 'service_id', 'experience', 'region', 'address', 'fees'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("All input fields are required!");
        }
    }

    // Validate email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format!");
    }

    // Check if email exists
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $check_email = mysqli_query($conn, "SELECT email FROM users WHERE email = '{$email}'");
    if (mysqli_num_rows($check_email) > 0) {
        throw new Exception("This email already exists!");
    }

    // Check file uploads
    if (empty($_FILES['image']['name']) || empty($_FILES['pdf']['name'])) {
        throw new Exception("Both image and PDF files are required!");
    }

    // Process image upload
    $img_name = $_FILES['image']['name'];
    $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
    $allowed_img = ['jpeg', 'png', 'jpg'];
    if (!in_array($img_ext, $allowed_img)) {
        throw new Exception("Only JPG, JPEG, PNG images are allowed!");
    }

    // Process PDF upload
    $pdf_name = $_FILES['pdf']['name'];
    $pdf_ext = strtolower(pathinfo($pdf_name, PATHINFO_EXTENSION));
    if ($pdf_ext !== 'pdf') {
        throw new Exception("Only PDF files are allowed for CV!");
    }

    // Upload files
    $time = time();
    $new_img_name = $time . '_' . $img_name;
    $new_pdf_name = $time . '_' . $pdf_name;
    $img_path = "images/" . $new_img_name;
    $pdf_path = "../../admin/cv/" . $new_pdf_name;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $img_path)) {
        throw new Exception("Failed to upload image!");
    }

    if (!move_uploaded_file($_FILES['pdf']['tmp_name'], $pdf_path)) {
        unlink($img_path); // Clean up image if PDF fails
        throw new Exception("Failed to upload PDF!");
    }

    // Get skill title from services table
    $service_id = mysqli_real_escape_string($conn, $_POST['service_id']);
    $skill_query = mysqli_query($conn, "SELECT title FROM services WHERE service_id = '{$service_id}'");
    if (!$skill_query || mysqli_num_rows($skill_query) === 0) {
        throw new Exception("Invalid service selected!");
    }
    $skill = mysqli_fetch_assoc($skill_query);
    $skills_title = mysqli_real_escape_string($conn, $skill['title']);

    // Prepare data
    $data = [
        'unique_id' => rand(time(), 100000000),
        'fname' => mysqli_real_escape_string($conn, $_POST['fname']),
        'lname' => mysqli_real_escape_string($conn, $_POST['lname']),
        'email' => $email,
        'password' => password_hash("fixit@2025", PASSWORD_DEFAULT), // Auto-set password
        'phone' => mysqli_real_escape_string($conn, $_POST['phone']),
        'service_id' => $service_id,
        'skills' => $skills_title, // Set from services table
        'experience' => (int)$_POST['experience'],
        'region' => mysqli_real_escape_string($conn, $_POST['region']),
        'address' => mysqli_real_escape_string($conn, $_POST['address']),
        'fees' => mysqli_real_escape_string($conn, $_POST['fees']),
        'img' => $new_img_name,
        'pdf' => $pdf_path,
        'status' => 'Offline Now',
        'usertype' => 'worker',
        'access_status' => 'pending'
    ];

    // Build SQL query
    $columns = implode(', ', array_keys($data));
    $values = "'" . implode("', '", array_values($data)) . "'";
    $query = "INSERT INTO users ($columns) VALUES ($values)";

    // Execute query
    if (!mysqli_query($conn, $query)) {
        unlink($img_path);
        unlink($pdf_path);
        throw new Exception("Database error: " . mysqli_error($conn));
    }

    $response['success'] = true;
    $response['message'] = "Registration successful!";

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

ob_end_clean(); // Clean any accidental output
die(json_encode($response));
?>