<?php
header('Content-Type: application/json'); // Set the response content type to JSON
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../db.php');

session_start(); // Start the session

// Function to generate a unique ID with 10 characters
function generateUniqueId() {
    $timestamp = microtime(true); // Get the current timestamp with microseconds
    $randomNumber = mt_rand(100000, 999999); // Generate a random number with 6 digits

    // Format the timestamp and random number to ensure a fixed length
    $uniqueId = sprintf('%011.6F', $timestamp) . sprintf('%06d', $randomNumber);

    return $uniqueId;
}

// Check if all required POST data is present
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate required fields
    $required = ['fname', 'lname', 'email', 'password', 'phone', 'address', 'region'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(["error" => "All fields are required"]);
            exit;
        }
    }

    // Generate a unique ID
    $unique_id = generateUniqueId();

    $fname = htmlspecialchars($_POST['fname']);
    $lname = htmlspecialchars($_POST['lname']);
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash the password
    $phone = htmlspecialchars($_POST['phone']);
    $address = htmlspecialchars($_POST['address']);
    $region = htmlspecialchars($_POST['region']);
    $usertype = 'client'; // Set usertype to 'client'
    $access_status = 'approved'; // Set access_status to 'approved'
    $status = 'Offline now'; // Set initial status

    // Set default image filename
    $filename = 'default-profile.jpg';
    
    // Handle image upload if provided
    if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
        $img = $_FILES['img'];
        $target_dir = "../public/images/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            if (!mkdir($target_dir, 0755, true)) {
                echo json_encode(["error" => "Failed to create upload directory"]);
                exit;
            }
        }

        // Check if directory is writable
        if (!is_writable($target_dir)) {
            echo json_encode(["error" => "Upload directory is not writable"]);
            exit;
        }

        // Validate and move the uploaded image
        $target_file = $target_dir . basename($img["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is an actual image or fake image
        $check = getimagesize($img["tmp_name"]);
        if($check === false) {
            echo json_encode(["error" => "File is not an image."]);
            exit;
        }

        // Check file size (5MB max)
        if ($img["size"] > 5000000) {
            echo json_encode(["error" => "Sorry, your file is too large. Maximum allowed size is 5MB."]);
            exit;
        }

        // Allow certain file formats
        $allowedTypes = ["jpg", "png", "jpeg", "gif"];
        if(!in_array($imageFileType, $allowedTypes)) {
            echo json_encode(["error" => "Sorry, only JPG, JPEG, PNG & GIF files are allowed."]);
            exit;
        }

        // Generate unique filename to prevent overwrites
        $filename = uniqid() . '.' . $imageFileType;
        $target_file = $target_dir . $filename;

        if (!move_uploaded_file($img["tmp_name"], $target_file)) {
            echo json_encode(["error" => "Sorry, there was an error uploading your file."]);
            exit;
        }
    }

    // Insert the user data into the database
    $sql = "INSERT INTO users (unique_id, fname, lname, email, password, phone, address, region, usertype, access_status, img, status) 
            VALUES (:unique_id, :fname, :lname, :email, :password, :phone, :address, :region, :usertype, :access_status, :img, :status)";
    $stmt = $conn->prepare($sql);

    // Bind parameters to the prepared statement
    $stmt->bindParam(':unique_id', $unique_id);
    $stmt->bindParam(':fname', $fname);
    $stmt->bindParam(':lname', $lname);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':region', $region);
    $stmt->bindParam(':usertype', $usertype);
    $stmt->bindParam(':access_status', $access_status);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':img', $filename);

    // Execute the prepared statement
    if ($stmt->execute()) {
        // Set session variables if needed
        $_SESSION['unique_id'] = $unique_id;
        $_SESSION['email'] = $email;
        
        echo json_encode(["message" => "Registration successful!"]);
    } else {
        // Delete the uploaded file if database insertion failed (only if user uploaded one)
        if (isset($target_file) && file_exists($target_file)) {
            unlink($target_file);
        }
        echo json_encode(["error" => "Database error: " . implode(" ", $stmt->errorInfo())]);
    }
} else {
    echo json_encode(["error" => "Invalid request method or missing data"]);
}
?>