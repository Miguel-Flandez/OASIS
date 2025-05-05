<?php
session_start();

// Simulated credentials and data
$valid_email = "user@example.com";
$valid_password = "password123";
$valid_otp = "123456";

// Get initial parameters from URL
if (!isset($_SESSION['amount']) && !isset($_SESSION['student'])) {
    $_SESSION['amount'] = isset($_GET['amount']) ? floatval($_GET['amount']) : 0; // This is the total amount
    $_SESSION['student'] = isset($_GET['student']) ? $_GET['student'] : "N/A";
}

$amount = $_SESSION['amount'];
$student = $_SESSION['student'];

// Handle different stages of the payment process
$stage = isset($_POST['stage']) ? $_POST['stage'] : 'login';

switch ($stage) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];
            if ($email === $valid_email && $password === $valid_password) {
                $_SESSION['otp'] = $valid_otp;
                $stage = 'otp';
            } else {
                $login_error = "Invalid email or password.";
            }
        }
        break;

    case 'otp':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $otp = $_POST['otp'];
            if ($otp === $_SESSION['otp']) {
                $stage = 'pay';
            } else {
                $otp_error = "Invalid OTP.";
            }
        }
        break;

    case 'pay':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $transaction_id = "TX" . rand(100000, 999999);
            $payment_date = date("F d, Y");
            $card_number = "**** **** **** " . rand(1000, 9999);
            $_SESSION['payment_success'] = true;
            $_SESSION['transaction_id'] = $transaction_id;
            $_SESSION['payment_date'] = $payment_date;
            $_SESSION['card_number'] = $card_number;
            echo "<script>alert('Payment of PHP " . number_format($amount, 2) . " successful! Transaction ID: $transaction_id'); window.location.href='student-home.php';</script>";
            exit;
        }
        break;

    default:
        $stage = 'login';
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/Paymaya.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dosis:wght@200..800&family=Fjalla+One&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oswald:wght@200..700&family=Playwrite+IN:wght@100..400&family=Poiret+One&family=Roboto+Slab:wght@100..900&family=Roboto:ital,wght@0,100..900;1,100..900&family=Smooch+Sans:wght@100..900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/de323b5912.js" crossorigin="anonymous"></script>
    <title>Paymaya Payment Process</title>
    <style>
        .container { text-align: center; margin-top: 50px; }
        .error { color: red; }
    </style>
</head>
<body>
    <header>
        <img src="../assets/images/PayMaya_Logo.png" alt="paymaya" id="paymaya-logo-out">
    </header>

    <div class="container">
        <?php if ($stage === 'login'): ?>
            <div id="login-container">
                <img src="../assets/images/PayMaya_Logo.png" alt="paymaya" id="paymaya-logo-in">
                <h3>Login to your Paymaya Account</h3>
                <?php if (isset($login_error)): ?>
                    <p class="error"><?php echo $login_error; ?></p>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="stage" value="login">
                    <fieldset id="email-field">
                        <legend>Email or Mobile No.</legend>
                        <input type="email" id="email" name="email" placeholder="user@example.com" required>
                    </fieldset>
                    <fieldset id="password-field">
                        <legend>Password</legend>
                        <input type="password" id="password" name="password" placeholder="password123" required>
                    </fieldset>
                    <button id="login" type="submit">Log In</button>
                </form>
                <p id="forgot-pass">Forgot your password?</p>
            </div>

        <?php elseif ($stage === 'otp'): ?>
            <div id="login-container2">
                <?php if (isset($otp_error)): ?>
                    <p class="error"><?php echo $otp_error; ?></p>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="stage" value="otp">
                    <fieldset id="OTP-field">
                        <legend>Please Enter the One-Time Password (OTP) we sent to +63 912 345 6789</legend>
                        <input type="text" id="otp" name="otp" placeholder="Enter OTP (123456)" required>
                    </fieldset>
                    <button id="proceed" type="submit">Proceed</button>
                </form>
                <p id="resend-pass">Resend Password</p>
            </div>

        <?php elseif ($stage === 'pay'): ?>
            <div id="login-container3">
                <div id="header">OASIS</div>
                <div id="transaction-field">
                    <div id="top">
                        <p>Transaction</p>
                        <p>Payment Date</p>
                    </div>
                    <div id="middle">
                        <p><?php echo "TX" . rand(100000, 999999); ?></p>
                        <p><?php echo date("F d, Y"); ?></p>
                    </div>
                    <div id="bottom">
                        <p>Payment Method</p>
                        <div id="method">
                            <img src="../assets/images/PayMaya_Logo_Pick.png" alt="paymaya" id="paymaya-pick">
                            <p><?php echo "**** **** **** " . rand(1000, 9999); ?></p>
                        </div>
                    </div>
                </div>
                <div id="total">
                    <h3>Total Amount</h3>
                    <h1 id="final-price">PHP <?php echo number_format($amount, 2); ?></h1>
                </div>
                <form method="POST">
                    <input type="hidden" name="stage" value="pay">
                    <button id="pay" type="submit">Pay PHP <?php echo number_format($amount, 2); ?></button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <footer></footer>
</body>
</html>