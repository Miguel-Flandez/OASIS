<?php
session_start();

// Check if user is logged in and is a Parent
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['account_type'] !== 'Parent') {
    header("Location: ../index.html");
    exit;
}

$amount = $_SESSION['payment_amount'];
$student_number = $_SESSION['student_number'];
$balance = $_SESSION['balance'];

// Simulate payment validation
if ($amount > $balance) {
    echo "<script>alert('Insufficient balance! Payment of PHP " . number_format($amount, 2) . " failed.'); window.location.href = 'student-home.php';</script>";
} else {
    // Simulate successful payment
    $new_balance = $balance - $amount;
    echo "<script>alert('Payment of PHP " . number_format($amount, 2) . " successful! New balance: PHP " . number_format($new_balance, 2) . "'); window.location.href = 'student-home.php';</script>";
}

// Clear session variables
unset($_SESSION['payment_amount']);
unset($_SESSION['student_number']);
unset($_SESSION['mobile_number']);
unset($_SESSION['auth_code']);
unset($_SESSION['mpin']);
unset($_SESSION['balance']);
?>