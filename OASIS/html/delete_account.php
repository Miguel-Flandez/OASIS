<?php
header('Content-Type: application/json');

$host = "localhost";
$username = "root";
$password = "password";
$database = "oasis";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

$studentnumber = $_POST['studentnumber'];

$stmt = $conn->prepare("DELETE FROM accounts_user WHERE studentnumber = ?");
$stmt->bind_param("s", $studentnumber);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>