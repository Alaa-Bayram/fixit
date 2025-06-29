<?php
session_start();
include_once "db.php";

if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    header("Location: login.html");
    exit();
}

// Include PHPMailer and the necessary classes outside of any conditional blocks
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $offer_id = $_GET['offer_id'];
    $emergency_id = $_GET['emergency_id'];

    // Update emergency status
    $stmt = $conn->prepare("UPDATE emergencies SET status = 'accepted', accepted_offer_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $offer_id, $emergency_id);

    if ($stmt->execute()) {
        // Retrieve worker's email, fname, lname, and phone from offers table
        $stmt_offer = $conn->prepare("SELECT offers.*, users.email, users.fname, users.lname, users.phone
                                      FROM offers 
                                      JOIN users ON offers.worker_id = users.user_id 
                                      WHERE offers.id = ?");
        $stmt_offer->bind_param("i", $offer_id);
        $stmt_offer->execute();
        $result_offer = $stmt_offer->get_result();

        if ($result_offer->num_rows > 0) {
            $row_offer = $result_offer->fetch_assoc();
            $worker_email = $row_offer['email'];
            $worker_fname = $row_offer['fname'];
            $worker_lname = $row_offer['lname'];
            $worker_phone = $row_offer['phone'];

            // Send email to worker
            $client_fname = $_SESSION['fname'];
            $client_lname = $_SESSION['lname'];
            $client_address = $_SESSION['address'];
            $client_phone = $_SESSION['phone'];
            $client_email = $_SESSION['email'];

            $subject = "Offer Accepted on FixIt App";
            $message = "\n\nYour offer has been accepted by $client_fname $client_lname.<br> Please contact them at $client_phone or via email : $client_email , <br>or just you can contact them using our messaging feature available on the FixIt App.<br><br>
            They are waiting for you.
            <br><br>Please be punctual and arrive on time for the agreed work schedule.";


            // Create a new PHPMailer instance
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'fixitapp.team@gmail.com';
                $mail->Password   = 'eybh rmjq nvbm mmub';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                $mail->setFrom('fixitapp.team@gmail.com', 'FixIt App');
                $mail->addAddress($worker_email);

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = "Dear $worker_fname $worker_lname,<br><br>$message<br><br>Best regards,<br>FixIt Team";
                $mail->AltBody = "Dear $worker_fname $worker_lname,\n\n$message\n\nBest regards,\nFixIt Team";

                $mail->send();
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }

        // Redirect to client_emergencies.php after processing
        header("Location: ../client_emergencies.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
