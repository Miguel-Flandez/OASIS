<?php
session_start();

// Check admin access
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !in_array($_SESSION['account_type'], ['Admin', 'Employee'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
// Database connection
require_once '../includes/db_connection.php';

// Fetch overdue fees
$query = "
    SELECT a.name AS parent_name, t.studentnumber, t.fee, t.duedate, t.amount
    FROM tuition t
    JOIN students s ON t.studentnumber = s.studentnumber
    JOIN accounts a ON s.parent_id = a.id
    WHERE t.status = 'Unpaid' AND t.duedate < CURDATE()
    UNION ALL
    SELECT a.name AS parent_name, m.studentnumber, m.fee, m.duedate, m.amount
    FROM misc m
    JOIN students s ON m.studentnumber = s.studentnumber
    JOIN accounts a ON s.parent_id = a.id
    WHERE m.status = 'Unpaid' AND m.duedate < CURDATE()
";
$result = $conn->query($query);
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$conn->close();
echo json_encode($data);
?>