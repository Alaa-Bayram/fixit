<?php
session_start(); // Start session
include_once "db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and retrieve form data
    $service_id = htmlspecialchars($_POST['service']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $address = htmlspecialchars($_POST['address']);
    $phone = htmlspecialchars($_POST['phone']);
    $region = htmlspecialchars($_POST['region']);
    $image = '';
    $client_id = $_SESSION['user_id'];

    // Check if a file was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $imageTmpName = $_FILES['image']['tmp_name'];
        $imageName = uniqid() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $uploadDir = 'uploads/';
        $uploadFile = $uploadDir . $imageName;

        // Save the image file
        if (move_uploaded_file($imageTmpName, $uploadFile)) {
            $image = $uploadFile;
        } else {
            error_log("Error saving the file");
            header("HTTP/1.1 500 Internal Server Error");
            echo json_encode(array("success" => false, "message" => "An error occurred while saving the image."));
            exit();
        }
    } elseif (isset($_POST['captured_image_data']) && !empty($_POST['captured_image_data'])) {
        // Handle base64 image data
        $imageData = $_POST['captured_image_data'];
        $imageData = str_replace('data:image/png;base64,', '', $imageData);
        $imageData = base64_decode($imageData);
        $imageName = uniqid() . '.png';
        $uploadDir = 'uploads/';
        $uploadFile = $uploadDir . $imageName;

        // Save the image file
        if (file_put_contents($uploadFile, $imageData)) {
            $image = $uploadFile;
        } else {
            error_log("Error saving the file");
            header("HTTP/1.1 500 Internal Server Error");
            echo json_encode(array("success" => false, "message" => "An error occurred while saving the image."));
            exit();
        }
    }

    // Fetch title based on service_id
    $sql_title = "SELECT title FROM services WHERE service_id = ?";
    $stmt_title = $conn->prepare($sql_title);
    $stmt_title->bind_param("i", $service_id);
    $stmt_title->execute();
    $stmt_title->bind_result($title);
    $stmt_title->fetch();
    $stmt_title->close();

    // Prepare the statement to insert data into emergencies table
    $stmt = $conn->prepare("INSERT INTO emergencies (client_id, service_id, title, description, address, phone, region, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    // Bind parameters
    $stmt->bind_param("iissssss", $client_id, $service_id, $title, $description, $address, $phone, $region, $image);

    // Execute the main insert statement
    if ($stmt->execute()) {
        echo json_encode(array("success" => true));
    } else {
        error_log("Database error: " . mysqli_error($conn));
        header("HTTP/1.1 500 Internal Server Error");
        echo json_encode(array("success" => false, "message" => "An error occurred while submitting your emergency. Please try again later."));
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    header("HTTP/1.1 405 Method Not Allowed");
    echo json_encode(array("success" => false, "message" => "Method not allowed"));
}

?>
