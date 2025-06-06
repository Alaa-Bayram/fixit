<?php
header('Content-Type: application/json');
include_once "../db.php";

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        // Check if the required parameters are set
        if (!isset($_GET['worker_id']) || !isset($_GET['date'])) {
            error_log("Missing parameters. Received worker_id: " . (isset($_GET['worker_id']) ? $_GET['worker_id'] : "null") . ", date: " . (isset($_GET['date']) ? $_GET['date'] : "null"));
            throw new Exception("Missing required parameters.");
        }

        // Retrieve GET parameters
        $worker_id = $_GET['worker_id'];
        $date = $_GET['date'];
        error_log("Parameters received. worker_id: $worker_id, date: $date");

        // Fetch appointments for the given worker and date
        $query = "SELECT TIME_FORMAT(time, '%h:%i %p') AS formatted_time 
                  FROM appointment
                  WHERE worker_id = :worker_id AND date = :date AND is_done = 0 
                  ORDER BY time ASC";
        $stmt = $conn->prepare($query);

        $stmt->execute([
            ':worker_id' => $worker_id,
            ':date' => $date
        ]);

        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Fetched appointments: " . json_encode($appointments));

        // Extract the formatted times to a simple array
        $times = array_map(function($appointment) {
            return $appointment['formatted_time'];
        }, $appointments);

        // Prepare success response
        $response['success'] = true;
        $response['unavailable_times'] = $times;

    } catch (PDOException $e) {
        // Handle database error
        $response['success'] = false;
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log('Database error in fetch_unavailability.php: ' . $e->getMessage());
    } catch (Exception $e) {
        // Handle other exceptions
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        error_log('Exception in fetch_unavailability.php: ' . $e->getMessage());
    }
} else {
    // Invalid request method
    $response['success'] = false;
    $response['message'] = "Invalid request method.";
}

// Output JSON response
echo json_encode($response);

// Close PDO connection
$conn = null;
?>
