<?php
session_start();

// Predefined simulation values
$PREDEFINED_MOBILE = "9123456789";  // 10 digits after +63
$PREDEFINED_AUTH_CODE = "123456";   // 6 digits
$PREDEFINED_MPIN = "1234";         // 4 digits
$INITIAL_BALANCE = 10000.00;       // Sample initial balance

// Initialize session variables if not set
if (!isset($_SESSION['step'])) {
    // Get payment amount from URL parameter
    $payment_amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0.00;
    
    if ($payment_amount <= 0) {
        // Redirect back if no valid amount is provided
        header("Location: student-home.php");
        exit;
    }

    $_SESSION['step'] = 'login';
    $_SESSION['mobile_number'] = $PREDEFINED_MOBILE;
    $_SESSION['auth_code'] = $PREDEFINED_AUTH_CODE;
    $_SESSION['mpin'] = $PREDEFINED_MPIN;
    $_SESSION['balance'] = $INITIAL_BALANCE;
    $_SESSION['payment_amount'] = $payment_amount;
    $_SESSION['student_number'] = $_GET['student'] ?? '';
    // Ensure selected_fees is a string
    $_SESSION['selected_fees'] = is_array($_GET['fees']) ? implode(',', $_GET['fees']) : ($_GET['fees'] ?? '');
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_step = $_GET['step'] ?? '';

    switch ($current_step) {
        case 'code':
            // Verify mobile number (simulation)
            if (isset($_POST['mobile_number']) && $_POST['mobile_number'] === $PREDEFINED_MOBILE) {
                $_SESSION['step'] = 'code';
            }
            break;

        case 'pin':
            // Verify auth code (simulation)
            if (isset($_POST['code']) && implode('', $_POST['code']) === $PREDEFINED_AUTH_CODE) {
                $_SESSION['step'] = 'pin';
            }
            break;

        case 'pay':
            // Verify MPIN (simulation)
            if (isset($_POST['mpin']) && implode('', $_POST['mpin']) === $PREDEFINED_MPIN) {
                $_SESSION['step'] = 'pay';
            }
            break;

        case 'process':
            // Process payment and redirect
            if (isset($_POST['updated_balance'])) {
                $_SESSION['balance'] = floatval($_POST['updated_balance']);
                
                // Ensure selected_fees is a string before exploding
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
                unset($_SESSION['step']);
                unset($_SESSION['payment_amount']);
                unset($_SESSION['selected_fees']);
                header("Location: student-home.php?student=" . urlencode($_SESSION['student_number']));
                exit;
            }
            break;
    }
}

// Render appropriate page based on current step
switch ($_SESSION['step']) {
    case 'login':
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="../css/Gcash.css">
            <title>Gcash</title>
        </head>
        <body>
            <img src="../assets/images/GCash-Logo.png" alt="gcash" id="gcash-logo">
            <div id="login-container">
                <div id="top">
                    <h2 id="merchant">Merchant</h2>
                    <h2 id="oasis">Oasis</h2>
                </div>
                <div id="middle">
                    <h2 id="due">Amount Due</h2>
                    <h2 id="price"><?php echo number_format($_SESSION['payment_amount'], 2); ?></h2>
                </div>
                <div id="bottom">
                    <h2 id="strong">Login to pay with GCash</h2>
                    <form id="login-form" action="gcash_payment.php?step=code" method="POST">
                        <fieldset id="auth">
                            <legend id="mobile">Mobile Number</legend>
                            <p id="left">+63</p>
                            <input type="text" id="number" name="mobile_number" value="<?php echo $_SESSION['mobile_number']; ?>" readonly>
                        </fieldset>
                        <button id="next" type="submit">NEXT</button>
                    </form>
                </div>
            </div>
            <footer>
                <p>Dont have a GCash account? <a href="">Register now</a></p>
            </footer>
        </body>
        </html>
        <?php
        break;

    case 'code':
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="../css/Gcash.css">
            <title>Gcash</title>
        </head>
        <body>
            <img src="../assets/images/GCash-Logo.png" alt="gcash" id="gcash-logo">
            <div id="login-container2">
                <div id="bottom">
                    <h2 id="strong">Login to pay with GCash</h2>
                    <p id="instruction">Enter the 6-digit authentication code sent to your registered mobile number</p>
                    <form id="code-form" action="gcash_payment.php?step=pin" method="POST">
                        <div id="underlines">
                            <?php
                            $code_digits = str_split($_SESSION['auth_code']);
                            foreach ($code_digits as $digit) {
                                echo '<input type="text" id="underline" name="code[]" value="' . $digit . '" maxlength="1" readonly>';
                            }
                            ?>
                        </div>
                        <div id="resendDiv">
                            <p id="instruction2">Didn't get the code?</p>
                            <p id="timer">Resend 300s</p>
                        </div>
                        <button id="next2" type="submit">NEXT</button>
                    </form>
                </div>
            </div>
        </body>
        </html>
        <?php
        break;

    case 'pin':
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="../css/Gcash.css">
            <title>Gcash</title>
        </head>
        <body>
            <img src="../assets/images/GCash-Logo.png" alt="gcash" id="gcash-logo">
            <div id="login-container2">
                <div id="bottom">
                    <h2 id="strong">Login to pay with GCash</h2>
                    <p id="instruction3">Enter your 4-digit MPIN</p>
                    <form id="pin-form" action="gcash_payment.php?step=pay" method="POST">
                        <div id="dots">
                            <?php
                            $mpin_digits = str_split($_SESSION['mpin']);
                            foreach ($mpin_digits as $digit) {
                                echo '<input type="text" id="dot" name="mpin[]" value="' . $digit . '" maxlength="1" readonly>';
                            }
                            ?>
                        </div>
                        <button id="next3" type="submit">NEXT</button>
                    </form>
                </div>
            </div>
        </body>
        </html>
        <?php
        break;

    case 'pay':
        $updated_balance = $_SESSION['balance'] - $_SESSION['payment_amount'];
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="../css/Gcash.css">
            <title>Gcash</title>
        </head>
        <body>
            <img src="../assets/images/GCash-Logo.png" alt="gcash" id="gcash-logo">
            <div id="login-container3">
                <div id="header">OASIS</div>
                <fieldset id="fieldset-top">
                    <legend id="pay-with">PAY WITH</legend>
                    <div>
                        <h3>GCash</h3>
                        <div id="right2">
                            <div id="stack">
                                <h3>PHP</h3>
                                <h3>Available Balance: <?php echo number_format($_SESSION['balance'], 2); ?></h3>
                                <h3>Balance After Payment: <?php echo number_format($updated_balance, 2); ?></h3>
                            </div>
                            <input type="radio" id="radio" checked disabled>
                        </div>
                    </div>
                </fieldset>
                <fieldset id="fieldset-bottom">
                    <legend id="about-to-pay">YOU ARE ABOUT TO PAY</legend>
                    <div id="bottom-top">
                        <h3 id="amount">Amount</h3>
                        <h3 id="price2"><?php echo number_format($_SESSION['payment_amount'], 2); ?></h3>
                    </div>
                    <div id="bottom-bottom">
                        <h3 id="discount">Discount</h3>
                        <h3 id="voucher">No Available Voucher</h3>
                    </div>
                </fieldset>
                <div id="total">
                    <h3>Total</h3>
                    <h1 id="final-price"><?php echo number_format($_SESSION['payment_amount'], 2); ?></h1>
                </div>
                <p id="instruction3">Please review to ensure that the details are correct before you proceed</p>
                <form id="pay-form" action="gcash_payment.php?step=process" method="POST">
                    <input type="hidden" name="updated_balance" value="<?php echo $updated_balance; ?>">
                    <button id="pay-button" type="submit">Pay PHP <?php echo number_format($_SESSION['payment_amount'], 2); ?></button>
                </form>
            </div>
        </body>
        </html>
        <?php
        break;
}
?>