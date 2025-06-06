<?php 
session_start();
if(isset($_SESSION['unique_id'])){
    if(isset($_POST['incoming_id']) && isset($_POST['message'])){
        include_once "db.php";

        $outgoing_id = mysqli_real_escape_string($conn, $_SESSION['unique_id']);
        $incoming_id = mysqli_real_escape_string($conn, $_POST['incoming_id']);
        $message = mysqli_real_escape_string($conn, $_POST['message']);

        if(!empty($message)){
            $sql = mysqli_query($conn, "INSERT INTO messages (incoming_msg_id, outgoing_msg_id, msg)
                                        VALUES ('{$incoming_id}', '{$outgoing_id}', '{$message}')") or die(mysqli_error($conn));
        }
    } else {
        // Debugging missing POST data
        error_log("POST data missing. incoming_id: " . ($_POST['incoming_id'] ?? 'null') . " | message: " . ($_POST['message'] ?? 'null'));
        echo "Missing incoming_id or message.";
    }
} else {
    error_log("Session 'unique_id' not set.");
    header("location: ../login.html");
}
?>
