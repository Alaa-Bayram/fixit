<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check database connection
if (!isset($pdo)) {
    die("Database connection not established");
}

try {
    // Query to fetch the top 3 requested services by appointment count
    $top_services_query = "
        SELECT s.title, s.images, COUNT(a.appointment_id) as appointment_count, u.skills
        FROM appointment a
        LEFT JOIN users u ON a.worker_id = u.user_id
        LEFT JOIN services s ON u.service_id = s.service_id
        GROUP BY s.title, s.images, u.skills
        ORDER BY appointment_count DESC
        LIMIT 3
    ";

    $stmt = $pdo->query($top_services_query);
    $top_services = $stmt->fetchAll();

    // Fetch the count of services
    $count_query = "SELECT COUNT(*) as total_services FROM services";
    $stmt = $pdo->query($count_query);
    $row_services = $stmt->fetch();
    $total_services = $row_services['total_services'];

    // Show 3 services for preview
    $preview_query = "SELECT * FROM services ORDER BY service_id DESC LIMIT 3";
    $stmt = $pdo->query($preview_query);
    $serv = $stmt->fetchAll();

    // Show all services
    $all_query = "SELECT * FROM services ORDER BY service_id DESC";
    $stmt = $pdo->query($all_query);
    $all_services = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    // Initialize with empty arrays if queries fail
    $top_services = [];
    $total_services = 0;
    $serv = [];
    $all_services = [];
}
?>