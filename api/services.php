<?php

include_once "../db.php";

$sql = "SELECT * FROM services";
$stmt = $conn->query($sql);

if ($stmt) {
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($services);
} else {
    echo json_encode(array('error' => 'Failed to fetch services'));
}

$conn = null; // Close PDO connection
?>
