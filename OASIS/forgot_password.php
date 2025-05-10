<?php
session_start();
require_once 'includes/db_connection.php';
require_once 'config.php'; // Include the config file for SMTP credentials
require 'vendor/autoload.php'; // Use Composer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_otp'])) {
    $username = trim($_POST['username']);

    // Fetch email from accounts table
    $stmt = $conn->prepare("SELECT email FROM accounts WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $email = $row['email'];

        // Generate OTP
        $otp = sprintf("%06d", rand(0, 999999)); // 6-digit OTP
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes')); // OTP valid for 10 minutes

        // Update OTP and expiry in database
        $stmt = $conn->prepare("UPDATE accounts SET otp = ?, otp_expiry = ? WHERE username = ?");
        $stmt->bind_param("sss", $otp, $otp_expiry, $username);
        $stmt->execute();
        $stmt->close();

        // Send OTP via Gmail SMTP
        $mail = new PHPMailer(true);
        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME; // From config.php
            $mail->Password = SMTP_PASSWORD; // From config.php
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email details
            $mail->setFrom('punzalan.juliusremo.l@gmail.com', 'OASIS Support');
            $mail->addAddress($email);
            $mail->Subject = 'Your OASIS OTP for Password Reset';
            $mail->Body = "Your OTP for password reset is: {$otp}\nThis OTP is valid for 10 minutes.";

            $mail->send();
            header("Location: verify_otp.php?username=" . urlencode($username));
            exit;
        } catch (Exception $e) {
            $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "Username not found.";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - OASIS</title>
    <link rel="stylesheet" href="css/forgot_password.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dosis:wght@200..800&family=Fjalla+One&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oswald:wght@200..700&family=Playwrite+IN:wght@100..400&family=Poiret+One&family=Roboto+Slab:wght@100..900&family=Roboto:ital,wght@0,100..900;1,100..900&family=Smooch+Sans:wght@100..900&display=swap" rel="stylesheet">

</head>
<body style="background-image: url('assets/images/background.png');">
    <div class="forgot-container">
        <div id="left">
            <img src="assets/images/oakwood.jpg" alt="Oakwood" id="logo">
        </div>

        <div id="right">
            <h2>Change Password</h2>
            <?php if (isset($error)) echo "<p style='color:red;'>{$error}</p>"; ?>
            <form method="POST" action="forgot_password.php">
                <input class="input" type="text" name="username" placeholder="Enter Username" required>
                <button type="submit" name="send_otp">Send OTP</button>
            </form>
        </div>

    </div>
    <script src="js/noEmoji.js"></script>
</body>
</html>