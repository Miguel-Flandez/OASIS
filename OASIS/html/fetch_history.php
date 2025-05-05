<?php
session_start();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=utf-8');

require_once '../includes/db_connection.php';

if ($conn->connect_error) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

$studentnumber = isset($_GET['studentnumber']) ? $_GET['studentnumber'] : null;

if ($studentnumber) {
    $query = "SELECT receiptnumber, transactiondate, amountpaid, fees FROM history WHERE studentnumber = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $studentnumber);
} else {
    $query = "SELECT receiptnumber, transactiondate, amountpaid, fees FROM history";
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$result = $stmt->get_result();
$history = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
}

$stmt->close();
$conn->close();

echo json_encode($history);
?>

