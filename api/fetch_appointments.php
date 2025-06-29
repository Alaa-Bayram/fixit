<?php
header('Content-Type: application/json');
session_start();
include_once "../db.php";

if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(array("message" => "Unauthorized"));
    exit();
}

$worker_id = $_SESSION['user_id'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to fetch appointments for the worker and client details
    $stmt = $conn->prepare("
        SELECT a.appointment_id, a.date, a.time, a.status, a.request_date, u.fname, u.lname, u.email, u.phone, u.address
        FROM appointment a
        JOIN users u ON a.client_id = u.user_id
        WHERE a.worker_id = :worker_id
        ORDER BY a.request_date DESC
    ");
    $stmt->bindParam(':worker_id', $worker_id);
    $stmt->execute();

    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($appointments);

} catch(PDOException $e) {
    http_response_code(500); // Server error
    echo json_encode(array("message" => "Failed to fetch appointments: " . $e->getMessage()));
}
?>
