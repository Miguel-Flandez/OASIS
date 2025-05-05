<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Allow CORS for fetch requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Credentials: true');

// Check session
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['account_type'] !== 'Employee') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

require_once '../includes/db_connection.php';

// Check database connection
if ($conn->connect_error) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

$studentnumber = isset($_GET['studentnumber']) ? $_GET['studentnumber'] : '';

if (!$studentnumber) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        receiptnumber, 
        transactiondate AS dateoftransaction, 
        amountpaid, 
        fees AS fee
    FROM history 
    WHERE studentnumber = ?
");
$stmt->bind_param("s", $studentnumber);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    exit;
}

$history = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $history[] = [
            'receiptnumber' => $row['receiptnumber'],
            'dateoftransaction' => $row['dateoftransaction'],
            'amountpaid' => $row['amountpaid'],
            'fee' => $row['fee'],
            'paymentstatus' => 'N/A' // Placeholder since status column doesn't exist
        ];
    }
} else {
    error_log("No transaction history found for studentnumber: $studentnumber");
}

$conn->close();

// Return data as JSON
header('Content-Type: application/json');
echo json_encode($history);
?>