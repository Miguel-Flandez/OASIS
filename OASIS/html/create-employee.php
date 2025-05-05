<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['account_type'] !== 'Admin') {
    header("Location: ../index.html");
    exit;
}

// Include database connection
require_once '../includes/db_connection.php';

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    if ($password !== $confirm_password) {
        $message = "Passwords do not match!";
    } else {
        // Check if username already exists
        $check_stmt = $conn->prepare("SELECT username FROM accounts WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Error: Username '$username' already exists! Please choose a different username.";
            $check_stmt->close();
        } else {
            $check_stmt->close();
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = $conn->prepare("INSERT INTO accounts (username, name, email, contact, password, type) VALUES (?, ?, ?, ?, ?, 'Employee')");
            $insert_stmt->bind_param("sssss", $username, $name, $email, $contact, $hashed_password);

            if ($insert_stmt->execute()) {
                $message = "Employee account created successfully!";
                // Clear form data after success
                $_POST = array();
            } else {
                $message = "Error creating employee account: " . $conn->error;
            }
            $insert_stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/create-account.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dosis:wght@200..800&family=Fjalla+One&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oswald:wght@200..700&family=Playwrite+IN:wght@100..400&family=Poiret+One&family=Roboto+Slab:wght@100..900&family=Roboto:ital,wght@0,100..900;1,100..900&family=Smooch+Sans:wght@100..900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/de323b5912.js" crossorigin="anonymous"></script>
    <title>Create Employee</title>
</head>
<body>
    <header>
        <div id="left">
            <img src="../assets/images/oakwood.jpg" alt="Oakwood" id="logo">
        </div>
        <h3 id="dashboard-button">Dashboard</h3>
        <h3 id="create-account">Create Account</h3>
        <h3 id="create-employee">Create Employee</h3>
        <h3 id="oasis-accounts">OASIS Accounts</h3>
        <h3 id="employee-accounts">Employee Accounts</h3>
        <div id="right">
            <button id="logout" onclick="if(confirm('Are you sure you want to log out?')) window.location.href='../logout.php';">Logout</button>
        </div>
    </header>

    <div id="accounts">
        <h3>Employee Account Management</h3>
        <div class="action-buttons">
            <!-- Button removed since form is shown by default -->
        </div>

        <div id="create-employee-form">
            <h4>Create Employee Account</h4>
            <form action="create-employee.php" method="post">
                <div class="form-row">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" placeholder="Enter Username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                <div class="form-row">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter Password" required>
                </div>
                <div class="form-row">
                    <label for="confirm-password">Confirm Password:</label>
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm Password" required>
                </div>
                <div class="form-row">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" placeholder="Enter Full Name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>
                <div class="form-row">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Enter Email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                <div class="form-row">
                    <label for="contact">Contact Number:</label>
                    <input type="text" id="contact" name="contact" placeholder="Enter Contact Number" required value="<?php echo isset($_POST['contact']) ? htmlspecialchars($_POST['contact']) : ''; ?>">
                </div>
                <button type="submit" class="submit-btn">Create Employee Account</button>
            </form>
        </div>

        <?php if (!empty($message)): ?>
            <p class="message <?php echo strpos($message, 'Error') !== false ? 'error' : ''; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>
    </div>

    <script src="../js/common-functions.js"></script>
    <script src="../js/routing-admin.js"></script>
    <script>
        // No toggle functionality needed since form is shown by default
    </script>
</body>
</html>