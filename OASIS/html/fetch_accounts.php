<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !in_array($_SESSION['account_type'], ['Admin', 'Employee'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

require_once '../includes/db_connection.php';

if ($conn->connect_error) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

$query = "
    SELECT 
        s.studentnumber, 
        s.firstname, 
        s.lastname, 
        s.middlename, 
        s.level, 
        s.paymentplan, 
        a.username, 
        a.name AS accountname 
    FROM students s
    LEFT JOIN accounts a ON s.parent_id = a.id
";
$result = $conn->query($query);

if (!$result) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    exit;
}

$accounts = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $accounts[] = [
            'studentnumber' => $row['studentnumber'],
            'firstname' => $row['firstname'],
            'lastname' => $row['lastname'],
            'middlename' => $row['middlename'],
            'level' => $row['level'],
            'username' => $row['username'],
            'accountname' => $row['accountname'],
            'paymentplan' => $row['paymentplan']
        ];
    }
}

$conn->close();

echo json_encode($accounts);
?>