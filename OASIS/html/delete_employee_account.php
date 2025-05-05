<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "oasis");
$username = $_POST['username'];
$stmt = $conn->prepare("DELETE FROM accounts WHERE username=? AND type='Employee'");
$stmt->bind_param("s", $username);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}
$conn->close();
?>