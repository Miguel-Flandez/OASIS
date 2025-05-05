<?php
session_start();

// Check admin access
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['account_type'] !== 'Admin') {
    header("Location: ../index.html");
    exit;
}

require_once '../includes/db_connection.php';

$studentnumber = $_POST['studentnumber'];
$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];
$middlename = $_POST['middlename'] ?? '';
$level = $_POST['level'];
$name = $_POST['name'];
$email = $_POST['email'];
$contact = $_POST['contact'];
$parent_id = $_POST['parent_id'];
$delete_image = $_POST['delete_image'] === '1';
$new_image_path = null;

try {
    // Handle image deletion or upload
    if ($delete_image) {
        $stmt = $conn->prepare("SELECT image FROM students WHERE studentnumber = ?");
        $stmt->bind_param("s", $studentnumber);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $old_image = $row['image'];
            if ($old_image && file_exists($old_image)) {
                unlink($old_image); // Delete the old image file
            }
            $stmt = $conn->prepare("UPDATE students SET image = NULL WHERE studentnumber = ?");
            $stmt->bind_param("s", $studentnumber);
            $stmt->execute();
        }
    }

    // Handle new image upload
    if (isset($_FILES['student_image']) && $_FILES['student_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['student_image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowed_types)) {
            echo json_encode(['success' => false, 'error' => 'Only JPG, PNG, and GIF files are allowed.']);
            exit;
        } elseif ($file['size'] > $max_size) {
            echo json_encode(['success' => false, 'error' => 'File size exceeds 5MB limit.']);
            exit;
        } else {
            $upload_dir = 'uploads/students/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = $studentnumber . '.' . $file_extension;
            $destination = $upload_dir . $new_filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $new_image_path = $destination;

                // Delete old image if it exists
                $stmt = $conn->prepare("SELECT image FROM students WHERE studentnumber = ?");
                $stmt->bind_param("s", $studentnumber);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $old_image = $row['image'];
                    if ($old_image && file_exists($old_image) && $old_image !== $new_image_path) {
                        unlink($old_image);
                    }
                }

                $stmt = $conn->prepare("UPDATE students SET image = ? WHERE studentnumber = ?");
                $stmt->bind_param("ss", $new_image_path, $studentnumber);
                $stmt->execute();
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to upload the image.']);
                exit;
            }
        }
    }

    // Update student and parent details
    $stmt = $conn->prepare("UPDATE students s JOIN accounts a ON s.parent_id = a.id SET s.firstname = ?, s.lastname = ?, s.middlename = ?, s.level = ?, a.name = ?, a.email = ?, a.contact = ? WHERE s.studentnumber = ? AND a.id = ?");
    $stmt->bind_param("ssssssisi", $firstname, $lastname, $middlename, $level, $name, $email, $contact, $studentnumber, $parent_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update account: ' . $conn->error]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'An error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>