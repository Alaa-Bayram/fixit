<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function sendEmail($to, $fname, $lname, $subject, $message) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = SMTP::DEBUG_OFF; // Change to DEBUG_SERVER for troubleshooting
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'fixitapp.team@gmail.com';
        $mail->Password   = 'eybh rmjq nvbm mmub';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->Priority   = 3; // Normal priority

        // Sender and recipient settings
        $mail->setFrom('fixitapp.team@gmail.com', 'FixIt App');
        $mail->addReplyTo('support@fixitapp.com', 'FixIt Support');
        $mail->addAddress($to, $fname . ' ' . $lname);

        // Content settings
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        
        // Enhanced HTML template
        $mail->Body = buildEmailTemplate($fname, $lname, $message);
        $mail->AltBody = generateTextVersion($fname, $lname, $message);

        // Important headers
        $mail->addCustomHeader('List-Unsubscribe', '<mailto:unsubscribe@fixitapp.com>, <https://fixitapp.com/unsubscribe>');
        $mail->addCustomHeader('Precedence', 'bulk');
        $mail->addCustomHeader('X-Auto-Response-Suppress', 'OOF, AutoReply');
        $mail->addCustomHeader('X-Mailer', 'PHP/' . phpversion());

        if (!$mail->send()) {
            error_log('Email send failed to: ' . $to . ' - Error: ' . $mail->ErrorInfo);
            return false;
        }
        
        return true;
    } catch (Exception $e) {
        error_log('Mailer Exception to ' . $to . ': ' . $e->getMessage());
        return false;
    }
}

function buildEmailTemplate($fname, $lname, $message) {
    $currentYear = date('Y');
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FixIt App Notification</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .content { padding: 20px 0; }
        .footer { font-size: 0.8em; color: #7f8c8d; border-top: 1px solid #eee; padding-top: 15px; margin-top: 20px; }
        a { color: #3498db; text-decoration: none; }
        .button { display: inline-block; padding: 10px 20px; background-color: #3498db; color: white; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h2>FixIt App</h2>
    </div>
    
    <div class="content">
        <p>Dear {$fname} {$lname},</p>
        
        <div>{$message}</div>
        
        <p>Thank you for using our service!</p>
    </div>
    
    <div class="footer">
        <p>&copy; {$currentYear} FixIt App. All rights reserved.</p>
        <p>
            <a href="https://fixitapp.com/privacy">Privacy Policy</a> | 
            <a href="https://fixitapp.com/unsubscribe">Unsubscribe</a>
        </p>
        <p>Our mailing address is: 123 Business Street, City, Country</p>
    </div>
</body>
</html>
HTML;
}

function generateTextVersion($fname, $lname, $message) {
    $currentYear = date('Y');
    return <<<TEXT
Dear {$fname} {$lname},

{$message}

Thank you for using our service!

---
© {$currentYear} FixIt App. All rights reserved.
Privacy Policy: https://fixitapp.com/privacy
Unsubscribe: https://fixitapp.com/unsubscribe
Our mailing address is: 123 Business Street, City, Country
TEXT;
}
?>