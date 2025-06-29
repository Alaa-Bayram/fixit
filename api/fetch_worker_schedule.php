<?php
session_start();
if (!isset($_SESSION['unique_id'])) {  // Change from user_id to unique_id
    http_response_code(401);
    die('Worker not logged in');
}
$worker_id = $_SESSION['unique_id'];  // Use the same session variable name
require '../db.php'; // Ensure you have this file to manage your DB connection

if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized status code
    die('Worker not logged in');
}

$worker_id = $_SESSION['user_id'];

if (!isset($_GET['date'])) {
    http_response_code(400); // Bad Request status code
    die('Date parameter is missing');
}

$date = $_GET['date']; // Expecting 'Y-m-d' format

try {
    $appointmentsQuery = $conn->prepare("
        SELECT 
            'appointment' AS type,
            a.appointment_id AS id,
            u.fname AS client_fname,
            u.lname AS client_lname,
            u.email AS client_email,
            u.phone AS client_phone,
            u.address AS client_address,
            a.date,
            a.time,
            a.status,
            a.is_done,
            a.cost
        FROM appointment a
        JOIN users u ON a.client_id = u.user_id
        WHERE a.worker_id = :worker_id AND a.date = :date AND a.status='accepted'
    ");
    
    $appointmentsQuery->execute(['worker_id' => $worker_id, 'date' => $date]);

    $emergenciesQuery = $conn->prepare("
    SELECT 
        'emergency' AS type,
        e.id,
        e.client_id,
        e.service_id,
        e.title,
        e.description,
        e.address,
        e.phone,
        e.region,
        e.image,
        e.created_at,
        e.status,
        e.is_done,
        e.cost,
        u.fname AS client_fname,
        u.lname AS client_lname,
        o.available_time AS time,
        o.date AS date
    FROM emergencies e
    JOIN offers o ON e.id = o.emergency_id
    JOIN users u ON e.client_id = u.user_id
    WHERE o.worker_id = :worker_id 
      AND DATE(o.date) = :date
      AND e.accepted_offer_id IS NOT NULL
");
    
    $emergenciesQuery->execute(['worker_id' => $worker_id, 'date' => $date]);

    $schedule = [];

    while ($row = $appointmentsQuery->fetch(PDO::FETCH_ASSOC)) {
        $schedule[] = $row;
    }

    while ($row = $emergenciesQuery->fetch(PDO::FETCH_ASSOC)) {
        $schedule[] = $row;
    }

    echo json_encode($schedule);

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error status code
    echo "Error: " . $e->getMessage();
}

$conn = null;
?>