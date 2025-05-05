<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Content-Type: application/json; charset=utf-8');

require_once '../includes/db_connection.php';

// Fetch data from all tables
$data = [
    'accounts' => [],
    'students' => [],
    'tuition' => [],
    'misc' => [],
    'history' => [],
    'survey' => []
];

// Accounts (exclude sensitive fields like password)
$stmt = $conn->prepare("SELECT id, type, username, name FROM accounts");
$stmt->execute();
$data['accounts'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Students
$stmt = $conn->prepare("SELECT id, studentnumber, firstname, lastname, middlename, level, paymentplan, parent_id FROM students");
$stmt->execute();
$data['students'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Tuition
$stmt = $conn->prepare("SELECT id, fee, duedate, amount, status, studentnumber FROM tuition");
$stmt->execute();
$data['tuition'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Misc
$stmt = $conn->prepare("SELECT id, fee, duedate, amount, status, studentnumber FROM misc");
$stmt->execute();
$data['misc'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// History
$stmt = $conn->prepare("SELECT id, receiptnumber, transactiondate, amountpaid, fees, parent_id, studentnumber FROM history");
$stmt->execute();
$data['history'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Survey
$stmt = $conn->prepare("SELECT id, name, rating, comment FROM survey");
$stmt->execute();
$data['survey'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();

echo json_encode($data);
?>