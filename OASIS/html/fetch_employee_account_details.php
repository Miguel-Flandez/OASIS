<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "oasis");
$username = $_GET['username'];
$query = "SELECT username, name, email, contact, id FROM accounts WHERE username = ? AND type = 'Employee'";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
echo json_encode($result->fetch_assoc());
$conn->close();
?>