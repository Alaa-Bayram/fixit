<?php
session_start();
include('../db.php');

// Read incoming JSON data
$data = json_decode(file_get_contents("php://input"));

if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($data->email) && !empty($data->password)) {
    $email = trim($data->email);
    $password = trim($data->password);

    // Prepare SQL query to select user by email
    $sql = "SELECT * FROM users WHERE email = :email AND access_status = 'approved'";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($password, $user['password'])) {
            // Secure session handling
            session_regenerate_id(true);

            // Set session variables
            $_SESSION['authenticated'] = true;
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['unique_id'] = $user['unique_id'];
            $_SESSION['fname'] = $user['fname'];
            $_SESSION['lname'] = $user['lname'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['usertype'] = $user['usertype'];
            $_SESSION['address'] = $user['address'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['status'] = 'Active now';
            $_SESSION['region'] = $user['region'];
            $_SESSION['skills'] = $user['skills'];
            $_SESSION['service_id'] = $user['service_id'];
            $_SESSION['img'] = 'images/' . basename($user['img']);

            // Update user status
            $updateSql = "UPDATE users SET status = 'Active now' WHERE unique_id = :unique_id";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bindParam(':unique_id', $user['unique_id']);
            $updateStmt->execute();

            // Return success
            http_response_code(200);
            echo json_encode([
                "message" => "Login successful!",
                "user_id" => $user['user_id'],
                "fname" => $user['fname'],
                "lname" => $user['lname'],
                "email" => $user['email'],
                "usertype" => $user['usertype'],
                "address" => $user['address'],
                "phone" => $user['phone'],
                "status" => 'Active now',
                "region" => $user['region'],
                "skills" => $user['skills'],
                "service_id" => $user['service_id'],
                "img" => 'images/' . basename($user['img'])
            ]);
        } else {
            // Wrong password
            http_response_code(401); // Unauthorized
            echo json_encode(["message" => "Invalid email or password."]);
        }
    } else {
        // No user found
        http_response_code(401); // Unauthorized
        echo json_encode(["message" => "Invalid email or password."]);
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "Invalid request."]);
}
?>
