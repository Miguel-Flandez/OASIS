<?php
session_start();
include 'includes/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM users WHERE username='$username' AND password=MD5('$password')";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $_SESSION['username'] = $username;
        header("Location: dashboard.php");
    } else {
        echo "<script>alert('Invalid username or password.'); window.location.href='index.html';</script>";
    }
}
$conn->close();
?>
