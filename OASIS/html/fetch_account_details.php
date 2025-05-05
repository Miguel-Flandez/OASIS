<?php
session_start();

// Check admin access
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['account_type'] !== 'Admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../includes/db_connection.php';

$studentnumber = $_GET['studentnumber'];

$stmt = $conn->prepare("SELECT s.studentnumber, s.firstname, s.lastname, s.middlename, s.level, s.paymentplan, a.name, a.email, a.contact, a.id as parent_id, s.image
                       FROM students s 
                       LEFT JOIN accounts a ON s.parent_id = a.id 
                       WHERE s.studentnumber = ? AND a.type = 'Parent'");
$stmt->bind_param("s", $studentnumber);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode($data);
} else {
    echo json_encode(['error' => 'Student not found']);
}

$conn->close();
?>