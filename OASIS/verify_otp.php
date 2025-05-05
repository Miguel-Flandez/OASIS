<?php
session_start();
require_once 'includes/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify_otp'])) {
    $username = $_GET['username'] ?? '';
    $otp = trim($_POST['otp']);

    $stmt = $conn->prepare("SELECT otp, otp_expiry FROM accounts WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $stored_otp = $row['otp'];
        $otp_expiry = $row['otp_expiry'];

        if ($stored_otp === $otp && new DateTime() < new DateTime($otp_expiry)) {
            $_SESSION['reset_username'] = $username;
            header("Location: reset_password.php");
            exit;
        } else {
            $error = "Invalid or expired OTP.";
        }
    } else {
        $error = "User not found.";
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
    <title>Verify OTP - OASIS</title>
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
            <img src="assets/images/newlogo.png" alt="Oakwood" id="logo">
        </div>
        
        <div id="right">

            <h2>Verify OTP</h2>
            <?php if (isset($error)) echo "<p style='color:red;'>{$error}</p>"; ?>
            <form method="POST" action="verify_otp.php?username=<?php echo htmlspecialchars($_GET['username'] ?? ''); ?>">
                <input type="text" name="otp" placeholder="Enter OTP" required>
                <button type="submit" name="verify_otp">Verify OTP</button>
            </form>
        </div>
    </div>
</body>
</html>