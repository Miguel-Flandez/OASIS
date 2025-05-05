<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['account_type'] !== 'Employee') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

require_once '../includes/db_connection.php';

$student = isset($_POST['student']) ? $_POST['student'] : '';
$table = isset($_POST['table']) ? $_POST['table'] : '';
$id = isset($_POST['id']) ? $_POST['id'] : '';

if (!$student || !$table || !$id || !in_array($table, ['tuition', 'misc'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM $table WHERE id = ? AND studentnumber = ?");
$stmt->bind_param("is", $id, $student);
$success = $stmt->execute();

$stmt->close();
$conn->close();

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to delete row']);
}
?>