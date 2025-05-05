<?php
// Suppress any warnings/notices that might corrupt JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "oasis");

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid request method or data']);
    exit;
}

$id = $data['id'] ?? null;
$username = $data['username'] ?? null;
$name = $data['name'] ?? null;
$email = $data['email'] ?? null;
$contact = $data['contact'] ?? null;
$password = $data['password'] ?? null;

if (!$id || !$username || !$name || !$email || !$contact) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("UPDATE accounts SET name=?, email=?, contact=? WHERE id=? AND type='Employee'");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param("sssi", $name, $email, $contact, $id);
    $stmt->execute();

    if ($password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE accounts SET password=? WHERE id=? AND type='Employee'");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("si", $hashed_password, $id);
        $stmt->execute();
    }

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>