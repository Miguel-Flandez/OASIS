<?php
header('Content-Type: application/json');
require_once '../includes/db_connection.php';

// Fetch payment history (only paid transactions)
$query = "
    SELECT id, receiptnumber, transactiondate, amountpaid, fees, parent_id, studentnumber 
    FROM history 
    WHERE amountpaid > 0 
    ORDER BY transactiondate ASC
";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$history = [];
while ($row = $result->fetch_assoc()) {
    $history[] = [
        'id' => $row['id'],
        'receiptnumber' => $row['receiptnumber'],
        'transactiondate' => $row['transactiondate'], // Keep full timestamp
        'amountpaid' => floatval($row['amountpaid']),
        'fees' => $row['fees'],
        'parent_id' => $row['parent_id'],
        'studentnumber' => $row['studentnumber']
    ];
}

$stmt->close();
$conn->close();

echo json_encode($history);
?>