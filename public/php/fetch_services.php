<?php
include_once "db.php";

$sql = "SELECT service_id, title FROM services";
$result = $conn->query($sql);


$services_options = '';
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services_options .= '<option value="' . $row['service_id'] . '">' . htmlspecialchars($row['title']) . '</option>';
    }
} else {
    $services_options = '<option value="">No Services Found</option>';
}

$conn->close();
echo $services_options;

?>
