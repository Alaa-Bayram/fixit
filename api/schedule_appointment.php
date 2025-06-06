<?php
header('Content-Type: application/json');
include_once "../db.php";

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            throw new Exception("User not authenticated.");
        }

        // Set timezone to Beirut
        date_default_timezone_set('Asia/Beirut');

        // Retrieve POST data
        $client_id = $_SESSION['user_id']; // Assuming user_id is the client's ID
        $worker_id = $_POST['worker_id'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $appointment_datetime = "$date $time:00";

        // Get current date and time formatted for MySQL DATETIME type
        $current_datetime = date('Y-m-d H:i:s');

        // Check if appointment_datetime is in the past
        if ($appointment_datetime < $current_datetime) {
            $response['success'] = false;
            $response['message'] = "The selected appointment time is in the past. Please choose a future time.";
            echo json_encode($response);
            exit();
        }

        // Check for overlapping appointments within 2 hours
        $check_query = "SELECT * FROM appointment WHERE worker_id = :worker_id AND ABS(TIMESTAMPDIFF(MINUTE, CONCAT(date, ' ', time), :appointment_datetime)) < 120 AND is_done=0";
        $stmt = $conn->prepare($check_query);
        $stmt->execute([
            ':worker_id' => $worker_id,
            ':appointment_datetime' => $appointment_datetime
        ]);

        if ($stmt->rowCount() > 0) {
            // There is an overlapping appointment
            $response['success'] = false;
            $response['message'] = "An appointment is already booked within two hours of the selected time. Please choose a different time.";
            echo json_encode($response);
            exit();
        }

        // No overlapping appointments, proceed with insertion
        $sql = "INSERT INTO appointment (client_id, worker_id, date, time, request_date) 
                VALUES (:client_id, :worker_id, :date, :time, :request_date)";
        $stmt = $conn->prepare($sql);

        // Bind parameters and execute
        $stmt->execute([
            ':client_id' => $client_id,
            ':worker_id' => $worker_id,
            ':date' => $date,
            ':time' => $time,
            ':request_date' => $current_datetime // Use current date and time for request_date
        ]);

        // Prepare success response
        $response['success'] = true;
        $response['message'] = "Appointment scheduled successfully.";
    } catch (PDOException $e) {
        // Handle database error
        $response['success'] = false;
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log('Database error in schedule_appointment.php: ' . $e->getMessage());
    } catch (Exception $e) {
        // Handle other exceptions
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        error_log('Error in schedule_appointment.php: ' . $e->getMessage());
    }

    echo json_encode($response);
}
?>
