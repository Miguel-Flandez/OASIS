<?php
session_start();
require_once 'includes/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])) {
    $username = $_SESSION['reset_username'] ?? '';
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Check if passwords match
    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check password complexity (at least 1 uppercase and 1 number)
        if (!preg_match('/^(?=.*[A-Z])(?=.*\d)/', $new_password)) {
            $error = "Password must contain at least one uppercase letter and one number.";
        } else {
            // Check if new password matches old password
            $stmt = $conn->prepare("SELECT password FROM accounts WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            if ($row && password_verify($new_password, $row['password'])) {
                $error = "New password cannot be the same as the old password.";
            } else {
                // Hash the new password and update
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE accounts SET password = ?, otp = NULL, otp_expiry = NULL WHERE username = ?");
                $stmt->bind_param("ss", $hashed_password, $username);
                $stmt->execute();
                $stmt->close();

                unset($_SESSION['reset_username']);
                header("Location: index.html");
                exit;
            }
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - OASIS</title>
    <link rel="stylesheet" href="css/forgot_password.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dosis:wght@200..800&family=Fjalla+One&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oswald:wght@200..700&family=Playwrite+IN:wght@100..400&family=Poiret+One&family=Roboto+Slab:wght@100..900&family=Roboto:ital,wght@0,100..900;1,100..900&family=Smooch+Sans:wght@100..900&display=swap" rel="stylesheet">
    <style>
        body {
        background-image: url('assets/images/background.png'); 
        background-size: cover; 
        background-position: center; 
        background-repeat: no-repeat; 
        height: 100vh; 
        margin: 0; 
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div id="left">
            <img src="assets/images/oakwood.jpg" alt="Oakwood" id="logo">
        </div>
        <div id="right">
            <h2>Reset Password</h2>
            <?php if (isset($error)) echo "<p style='color:red;'>{$error}</p>"; ?>
            <form method="POST" action="reset_password.php">
                <input type="password" name="new_password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit" name="reset_password">Reset Password</button>
            </form>
        </div>
    </div>
</body>
</html>