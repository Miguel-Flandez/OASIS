<?php
require_once '../includes/db_connection.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Content-Type: application/json; charset=utf-8');

$stmt = $conn->prepare("SELECT id, fee, duedate, amount, status FROM misc");
$stmt->execute();
$result = $stmt->get_result();
$misc = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

echo json_encode($misc);
?>