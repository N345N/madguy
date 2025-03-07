<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'mail/Exception.php';
require 'mail/PHPMailer.php';
require 'mail/SMTP.php';

if (isset($_POST['reset'])) {
    $email = $_POST['email'];

    // Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                       // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'rickyvallenos40@gmail.com';            // SMTP username
        $mail->Password   = 'dddnxuavxkdwvsem';                     // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            // Enable implicit TLS encryption
        $mail->Port       = 465;                                    // TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        // Recipients
        $mail->setFrom('rickyvallenos40@gmail.com', 'Sexy Body Fitness Gym');
        $mail->addAddress($email);                                  // Add a recipient

        $code = substr(str_shuffle('1234567890QWERTYUIOPASDFGHJKLZXCVBNM'), 0, 10);

        // Content
        $mail->isHTML(true);                                        // Set email format to HTML
        $mail->Subject = 'Password Reset';
        $mail->Body = 'Use the following code: ' . $code . '. <br> Reset your Password in 1 day';
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $conn = new mySqli('localhost', 'root', '', 'sexybodyfitnessgym');

        if ($conn->connect_error) {
            die('Could not connect to the database.');
        }

        // Check if email exists in the database
        $verifyQuery = $conn->query("SELECT * FROM users WHERE email = '$email'");

        if ($verifyQuery->num_rows) {
            // Email exists, update code and send email
            $codeQuery = $conn->query("UPDATE users SET code = '$code' WHERE email = '$email'");
            $mail->send();
            // Redirect to verification.php with a success message
            header("Location: verification.php?message=Code has been sent, check your email.");
            exit();
        } else {
            // No email found in database
            header("Location: forgot_password.php?message=no_email");
            exit();
        }

        $conn->close();
    } catch (Exception $e) {
        // Redirect to verification.php with an error message
        header("Location: verification.php?message=Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        exit();
    }
} else {
    exit();
}
?>