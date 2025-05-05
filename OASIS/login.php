<?php
session_start();

// Include database connection
require_once 'includes/db_connection.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_username = $_POST['username'];
    $input_password = $_POST['password'];

    // Sanitize input
    $input_username = trim($input_username);
    
    // Prepare SQL query to check credentials in the accounts table
    $stmt = $conn->prepare("SELECT type, password FROM accounts WHERE username = ?");
    $stmt->bind_param("s", $input_username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $stored_password = $row['password'];
        $account_type = $row['type'];

        // Verify password
        $password_match = false;
        
        // Check if password appears to be hashed (using password_verify for hashed passwords)
        if (password_get_info($stored_password)['algo'] !== null) {
            // Password is hashed
            $password_match = password_verify($input_password, $stored_password);
        } else {
            // Password is plain text
            $password_match = ($stored_password === $input_password);
        }

        if ($password_match) {
            // Password matches (hashed or unhashed)
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $input_username;
            $_SESSION['account_type'] = $account_type;

            // Redirect based on account type
            switch ($account_type) {
                case 'Parent':
                    header("Location: html/student-home.php");
                    break;
                case 'Employee':
                    header("Location: html/employee-home.php");
                    break;
                case 'Admin':
                    header("Location: html/admin-home.php");
                    break;
                default:
                    header("Location: index.html?error=invalid_type");
                    break;
            }
        } else {
            // Password doesn't match
            header("Location: index.html?error=invalid");
        }
    } else {
        // No user found
        header("Location: index.html?error=invalid");
    }

    $stmt->close();
    $conn->close();
    exit;
}

// If accessed directly, redirect to login page
header("Location: index.html");
$conn->close();
exit;
?>