<?php
session_start();
require_once '../includes/db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['account_type'] !== 'Employee') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$student = $_POST['student'] ?? '';
$changes = json_decode($_POST['changes'] ?? '[]', true);

if (empty($student) || empty($changes)) {
    echo json_encode(['success' => false, 'error' => 'No student or changes provided']);
    exit;
}

$success = true;
$error = '';

foreach ($changes as $change) {
    $id = $change['id'];
    $table = $change['table'];
    $fee = $change['fee'] ?? '';
    $duedate = $change['duedate'] ?? '';
    $amount = $change['amount'] ?? '';

    if (!in_array($table, ['tuition', 'misc'])) {
        $success = false;
        $error = 'Invalid table specified';
        break;
    }

    if ($id === 'new' || strpos($id, 'new-') === 0) {
        // Insert new row
        $stmt = $conn->prepare("INSERT INTO $table (studentnumber, fee, duedate, amount, status) VALUES (?, ?, ?, ?, 'Unpaid')");
        $stmt->bind_param("ssss", $student, $fee, $duedate, $amount);
        if (!$stmt->execute()) {
            $success = false;
            $error = "Failed to insert new row: " . $conn->error;
            $stmt->close();
            break;
        }
        $stmt->close();
    } else {
        // Update existing row
        $stmt = $conn->prepare("UPDATE $table SET fee = ?, duedate = ?, amount = ? WHERE id = ? AND studentnumber = ?");
        $stmt->bind_param("sssis", $fee, $duedate, $amount, $id, $student);
        if (!$stmt->execute()) {
            $success = false;
            $error = "Failed to update row: " . $conn->error;
            $stmt->close();
            break;
        }
        $stmt->close();
    }
}

$conn->close();
echo json_encode(['success' => $success, 'error' => $error]);
?>