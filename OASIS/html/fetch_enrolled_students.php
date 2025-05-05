<?php
session_start();

// Check admin access
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !in_array($_SESSION['account_type'], ['Admin', 'Employee'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Database connection
require_once '../includes/db_connection.php';

// Fetch all students
$query = "SELECT studentnumber, firstname, lastname, level FROM students";
$result = $conn->query($query);
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$conn->close();
echo json_encode($data);
?>