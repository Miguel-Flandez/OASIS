<?php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$mail = new PHPMailer(true);

try {
    // Server settings for Gmail SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'punzalan.juliusremo.l@gmail.com'; // Your Gmail address
    $mail->Password = 'ijnn mrhg nlvb orve'; // Replace with your App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('punzalan.juliusremo.l@gmail.com', 'Test');
    $mail->addAddress('jrpunzalan05@gmail.com'); // Recipient email

    // Content
    $mail->Subject = 'Test Email';
    $mail->Body = 'This is a test email.';

    // Send email
    $mail->send();
    echo 'Message has been sent successfully!';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>