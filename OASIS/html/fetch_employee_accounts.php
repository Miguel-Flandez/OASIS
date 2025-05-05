<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "oasis");
$query = "SELECT username, name, email, contact, id FROM accounts WHERE type = 'Employee'";
$result = $conn->query($query);
$accounts = [];
while ($row = $result->fetch_assoc()) {
    $accounts[] = $row;
}
echo json_encode($accounts);
$conn->close();
?>