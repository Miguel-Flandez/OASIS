<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['account_type'] !== 'Employee') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once '../includes/db_connection.php';

$table = $_POST['table'] ?? '';
$level = $_POST['level'] ?? '';
$fee = $_POST['fee'] ?? '';
$duedate = $_POST['duedate'] ?? '';
$amount = $_POST['amount'] ?? '';

if (empty($table) || !in_array($table, ['tuition', 'misc'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid table specified']);
    exit;
}

if (empty($level) || empty($fee) || empty($duedate) || empty($amount)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'All fields are required']);
    exit;
}

try {
    // Fetch all student numbers for the selected level
    $stmt = $conn->prepare("SELECT studentnumber FROM students WHERE level = ?");
    $stmt->bind_param("s", $level);
    $stmt->execute();
    $result = $stmt->get_result();
    $studentNumbers = $result->fetch_all(MYSQLI_ASSOC);

    if (empty($studentNumbers)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'No students found for the selected level']);
        exit;
    }

    // Prepare and execute insert statement for each student into the specified table
    $stmt = $conn->prepare("INSERT INTO $table (studentnumber, fee, duedate, amount, status) VALUES (?, ?, ?, ?, 'Unpaid')");
    $affectedCount = 0;
    foreach ($studentNumbers as $student) {
        $studentnumber = $student['studentnumber'];
        $stmt->bind_param("sssd", $studentnumber, $fee, $duedate, $amount);
        if ($stmt->execute()) {
            $affectedCount++;
        }
    }
    $stmt->close();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => "Fee '$fee' added to $affectedCount students in $level (Table: " . ucfirst($table) . ")"
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>