<?php
$conn = new mysqli("localhost", "root", "", "oasis");
$studentnumber = $_POST['studentnumber'];
$stmt = $conn->prepare("SELECT parent_id FROM students WHERE studentnumber=?");
$stmt->bind_param("s", $studentnumber);
$stmt->execute();
$result = $stmt->get_result();
$parent_id = $result->fetch_assoc()['parent_id'];

$stmt = $conn->prepare("DELETE FROM accounts WHERE id=? AND type='Parent'");
$stmt->bind_param("i", $parent_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}
$conn->close();
?>