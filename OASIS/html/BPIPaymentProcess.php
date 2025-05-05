<?php
session_start();

// Predefined simulation values
$PREDEFINED_ACCOUNT = "XXXXXXX1234";  // Simulated savings account number
$PREDEFINED_OTP = "123456";          // Simulated 6-digit OTP
$INITIAL_BALANCE = 10000.00;         // Sample initial balance

// Initialize session variables if not set
if (!isset($_SESSION['bpi_step'])) {
    // Get payment amount from URL parameter
    $payment_amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0.00;
    
    if ($payment_amount <= 0) {
        // Redirect back if no valid amount is provided
        header("Location: student-home.php");
        exit;
    }

    $_SESSION['bpi_step'] = 'choose_account';
    $_SESSION['account_number'] = $PREDEFINED_ACCOUNT;
    $_SESSION['otp'] = $PREDEFINED_OTP;
    $_SESSION['balance'] = $INITIAL_BALANCE;
    $_SESSION['payment_amount'] = $payment_amount;
    $_SESSION['student_number'] = $_GET['student'] ?? '';
    $_SESSION['selected_fees'] = is_array($_GET['fees']) ? implode(',', $_GET['fees']) : ($_GET['fees'] ?? '');
    $_SESSION['transaction_id'] = uniqid('BPI_'); // Generate a unique transaction ID
    $_SESSION['payment_date'] = 'March 18, 2025'; // Hardcoded date as per HTML
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_step = $_GET['step'] ?? '';

    switch ($current_step) {
        case 'otp':
            // Move to OTP verification step (no validation needed for account selection in simulation)
            $_SESSION['bpi_step'] = 'otp';
            break;

        case 'confirm':
            // Verify OTP (simulation)
            $submitted_otp = isset($_POST['otp']) ? implode('', $_POST['otp']) : '';
            if ($submitted_otp === $PREDEFINED_OTP) {
                $_SESSION['bpi_step'] = 'confirm';
            }
            break;

        case 'process':
            // Process payment and redirect
            $selected_fees = [];
            if (!empty($_SESSION['selected_fees'])) {
                $selected_fees_string = is_string($_SESSION['selected_fees']) ? $_SESSION['selected_fees'] : implode(',', (array)$_SESSION['selected_fees']);
                $selected_fees = explode(',', $selected_fees_string);
                $selected_fees = array_filter($selected_fees); // Remove empty entries
            }

            // Here you could add code to update the database with paid fees
            // For simulation, we'll just clear and redirect
            // Note: In a real implementation, you'd update the database here

            // Clear payment-related session variables
            unset($_SESSION['bpi_step']);
            unset($_SESSION['payment_amount']);
            unset($_SESSION['selected_fees']);
            unset($_SESSION['transaction_id']);
            unset($_SESSION['payment_date']);
            header("Location: student-home.php?student=" . urlencode($_SESSION['student_number']));
            exit;
            
    }
}

// Render the appropriate page based on the current step
$last_four = ($_SESSION['bpi_step'] === 'confirm') ? substr($_SESSION['account_number'], -4) : ''; // Last 4 digits of account number for confirmation step

// Output HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/BPI.css">
    <title>BPI</title>
</head>
<body>
    <div id="login-container">
        <img src="../assets/images/BPI Logo.png" alt="BPI" id="BPI-logo">
        <?php if ($_SESSION['bpi_step'] === 'choose_account'): ?>
            <fieldset id="choose-account">
                <legend><h1>Choose your account</h1></legend>
                <p>Please select an account below where you want to deduct the payment.</p>
            </fieldset>
            <div id="savings">
                <div>
                    <h3>SAVINGS ACCOUNT (SAVINGS ACCOUNT)</h3>
                    <p id="mobile"><?php echo htmlspecialchars($_SESSION['account_number']); ?> random number</p>
                </div>
            </div>
            <form action="BPIPaymentProcess.php?step=otp" method="POST">
                <button id="submit" type="submit">Continue</button>
            </form>
        <?php elseif ($_SESSION['bpi_step'] === 'otp'): ?>
            <h2 id="header">OTP Verification</h2>
            <p id="instruction">Enter the One-Time PIN sent to your registered mobile number</p>
            <form action="BPIPaymentProcess.php?step=confirm" method="POST">
                <div id="otps">
                    <?php
                    $otp_digits = str_split($_SESSION['otp']);
                    foreach ($otp_digits as $digit) {
                        echo '<input type="text" id="otp" name="otp[]" value="' . htmlspecialchars($digit) . '" maxlength="1" readonly>';
                    }
                    ?>
                </div>
                <h3 id="resend">Resend OTP</h3>
                <button id="submit" type="submit">Submit</button>
            </form>
        <?php elseif ($_SESSION['bpi_step'] === 'confirm'): ?>
            <div id="transaction-field">
                <div id="top">
                    <p>Transaction</p>
                    <p>Payment Date</p>
                </div>
                <div id="middle">
                    <p><?php echo htmlspecialchars($_SESSION['transaction_id']); ?></p>
                    <p><?php echo htmlspecialchars($_SESSION['payment_date']); ?></p>
                </div>
                <div id="bottom">
                    <p>Payment Method</p>
                    <div id="method">
                        <p>***** ***<?php echo htmlspecialchars($last_four); ?></p>
                    </div>
                </div>
            </div>
            <div id="total">
                <h3>Total Amount</h3>
                <h1 id="final-price"><?php echo number_format($_SESSION['payment_amount'], 2); ?></h1>
            </div>
            <form action="BPIPaymentProcess.php?step=process" method="POST">
                <button id="pay" type="submit">Pay PHP <?php echo number_format($_SESSION['payment_amount'], 2); ?></button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>