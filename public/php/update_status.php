<?php
include_once "db.php";
include_once "../send.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $appointment_id = $_POST['appointment_id'];
    $status = $_POST['status'];
    $client_email = $_POST['email']; // Correct key for email
    $client_fname = $_POST['fname']; // Correct key for first name
    $client_lname = $_POST['lname']; // Correct key for last name

    // Update appointment status
    $query = "UPDATE appointment SET status = ? WHERE appointment_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $appointment_id);

    if ($stmt->execute()) {
        // Get appointment details for date and time
        $select_query = "SELECT date, time FROM appointment WHERE appointment_id = ?";
        $select_stmt = $conn->prepare($select_query);
        $select_stmt->bind_param("i", $appointment_id);
        $select_stmt->execute();
        $result = $select_stmt->get_result();
        $row = $result->fetch_assoc();

        $date = date('l F j, Y', strtotime($row['date']));
        $time = date('h:i A', strtotime($row['time']));

        // Send email
        $subject = $status == 'accepted' ? 'Appointment Accepted' : 'Appointment Rejected';
        $message = $status == 'accepted' ? "Your appointment has been accepted on $date at $time." : "Your appointment has been rejected.";

        sendEmail($client_email, $client_fname, $client_lname, $subject, $message);
        
        header("Location: ../worker_dash.php");
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
