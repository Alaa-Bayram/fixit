<?php
session_start();
include_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("location: login.html");
    exit(); // Important to stop script execution after redirection
}

$user_id = $_SESSION['user_id'];
$unique_id = $_SESSION['unique_id'];
// Get the month and year from the query string or default to current month and year
$currentMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
$currentYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Fetch user data and appointment earnings for the selected month
$query = "SELECT u.fname, u.lname, u.phone, u.region, s.title, u.unique_id, 
                 IFNULL(SUM(a.cost), 0) AS total_appointment_earnings
          FROM users u
          LEFT JOIN services s ON u.service_id = s.service_id
          LEFT JOIN appointment a ON u.user_id = a.worker_id
                                    AND MONTH(a.date) = ?
                                    AND YEAR(a.date) = ?
          WHERE u.user_id = ? AND is_done='1'
          GROUP BY u.user_id";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die('Error in preparing query: ' . $conn->error);
}
$stmt->bind_param("ssi", $currentMonth, $currentYear, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$user = $result->fetch_assoc();
// Update the fallback array to include all fields
if (!$user) {
    // First get the basic user info regardless of appointments
    $userQuery = "SELECT u.fname, u.lname, u.phone, u.region, s.title, u.unique_id
                  FROM users u
                  LEFT JOIN services s ON u.service_id = s.service_id
                  WHERE u.user_id = ?";
    
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bind_param("i", $user_id);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $user = $userResult->fetch_assoc();
    
    if (!$user) {
        die("User not found");
    }
    
    // Add the earnings fields with default 0 values
    $user['total_appointment_earnings'] = 0;
}

// Fetch total emergency earnings for the selected month
$emergencyQuery = "SELECT IFNULL(SUM(e.cost), 0) AS total_emergency_earnings
                   FROM emergencies e
                   LEFT JOIN offers o ON e.id = o.emergency_id
                   WHERE o.worker_id = ? AND is_done='1'
                   AND MONTH(e.created_at) = ?
                   AND YEAR(e.created_at) = ?
                   AND e.status = 'accepted' AND is_done='1'";

$emergencyStmt = $conn->prepare($emergencyQuery);
if (!$emergencyStmt) {
    die('Error in preparing emergency earnings query: ' . $conn->error);
}
$emergencyStmt->bind_param("isi", $user_id, $currentMonth, $currentYear);
$emergencyStmt->execute();
$emergencyResult = $emergencyStmt->get_result();

$emergencyData = $emergencyResult->fetch_assoc();
$totalEmergencyEarnings = $emergencyData['total_emergency_earnings'];

$totalEarnings = $user['total_appointment_earnings'] + $totalEmergencyEarnings;

// Fetch pending requests count
$pendingQuery = "SELECT COUNT(*) as requests 
                 FROM appointment 
                 WHERE worker_id = ? 
                 AND status = 'pending'";

$pendingStmt = $conn->prepare($pendingQuery);
if (!$pendingStmt) {
    die('Error in preparing pending requests query: ' . $conn->error);
}
$pendingStmt->bind_param("i", $user_id);
$pendingStmt->execute();
$pendingResult = $pendingStmt->get_result();

if (!$pendingResult) {
    die('Error in executing pending requests query: ' . $conn->error);
}

$pendingData = $pendingResult->fetch_assoc();
$pendingRequests = $pendingData['requests'];

// Fetch the service title of the logged-in worker
$serviceTitleQuery = "SELECT s.title 
                      FROM users u
                      LEFT JOIN services s ON u.service_id = s.service_id
                      WHERE u.user_id = ?";

$serviceTitleStmt = $conn->prepare($serviceTitleQuery);
if (!$serviceTitleStmt) {
    die('Error in preparing service title query: ' . $conn->error);
}
$serviceTitleStmt->bind_param("i", $user_id);
$serviceTitleStmt->execute();
$serviceTitleResult = $serviceTitleStmt->get_result();

if (!$serviceTitleResult) {
    die('Error in executing service title query: ' . $conn->error);
}

$serviceTitleData = $serviceTitleResult->fetch_assoc();
$serviceTitle = $serviceTitleData['title'];

// Fetch pending emergency cases count with the service title condition
$emergencyPendingQuery = "SELECT COUNT(*) as nb_emergencies_pending 
                          FROM emergencies e
                          LEFT JOIN services s ON e.service_id = s.service_id
                          WHERE e.status = 'pending' 
                          AND s.title = ?";

$emergencyPendingStmt = $conn->prepare($emergencyPendingQuery);
if (!$emergencyPendingStmt) {
    die('Error in preparing pending emergencies query: ' . $conn->error);
}
$emergencyPendingStmt->bind_param("s", $serviceTitle);
$emergencyPendingStmt->execute();
$emergencyPendingResult = $emergencyPendingStmt->get_result();

if (!$emergencyPendingResult) {
    die('Error in executing pending emergencies query: ' . $conn->error);
}

$emergencyPendingData = $emergencyPendingResult->fetch_assoc();
$nb_emergencies_pending = $emergencyPendingData['nb_emergencies_pending'];

// Fetch next appointment including date and time
$nextAppointmentQuery = "SELECT MIN(CONCAT(date, ' ', time)) AS next_appointment
FROM appointment 
WHERE worker_id = ? 
AND is_done = 0 AND status='accepted'
AND CONCAT(date, ' ', time) >= NOW()";

$nextAppointmentStmt = $conn->prepare($nextAppointmentQuery);
if (!$nextAppointmentStmt) {
    die('Error in preparing next appointment query: ' . $conn->error);
}
$nextAppointmentStmt->bind_param("i", $user_id);
$nextAppointmentStmt->execute();
$nextAppointmentResult = $nextAppointmentStmt->get_result();

if (!$nextAppointmentResult) {
    die('Error in executing next appointment query: ' . $conn->error);
}

$nextAppointmentData = $nextAppointmentResult->fetch_assoc();
if ($nextAppointmentData && $nextAppointmentData['next_appointment']) {
    $nextAppointmentDateTime = strtotime($nextAppointmentData['next_appointment']);
    $nextAppointment = date('l j F Y', $nextAppointmentDateTime) . ' at ' . date('h:i A', $nextAppointmentDateTime);
} else {
    $nextAppointment = 'No upcoming appointments';
}
?>
