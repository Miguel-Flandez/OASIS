<?php
$conn = new mysqli("localhost", "root", "", "oasis");
$studentnumber = $_POST['studentnumber'];
$stmt = $conn->prepare("DELETE FROM students WHERE studentnumber=?");
$stmt->bind_param("s", $studentnumber);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}
$conn->close();
?>