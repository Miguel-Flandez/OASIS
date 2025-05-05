<?php
header('Content-Type: application/json');

$host = "localhost";
$username = "root";
$password = "password"; // Update with your actual DB password
$database = "oasis";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

// Get form data
$firstName = $_POST['firstName'] ?? '';
$lastName = $_POST['lastName'] ?? '';
$middleName = $_POST['middleName'] ?? '';
$username = $_POST['username'] ?? '';
$accountName = $_POST['accountName'] ?? '';
$password = $_POST['password'] ?? '';

// Validate required fields
if (empty($firstName) || empty($lastName) || empty($username) || empty($accountName) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'All required fields must be filled.']);
    $conn->close();
    exit;
}

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Prepare and execute the insert query
$stmt = $conn->prepare("INSERT INTO accounts_employee (firstname, lastname, middlename, username, accountname, password) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $firstName, $lastName, $middleName, $username, $accountName, $hashedPassword);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to create employee account: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>