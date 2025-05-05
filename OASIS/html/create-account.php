<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['account_type'] !== 'Admin') {
    header("Location: ../index.html");
    exit;
}

require_once '../includes/db_connection.php';

$message = "";

// Fetch all Parent accounts for the association dropdown
$parents = [];
$stmt = $conn->prepare("SELECT id, name FROM accounts WHERE type = 'Parent'");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $parents[] = $row;
}
$stmt->close();

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create_parent') {
            // Create Parent Account
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm-password'];
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $contact = trim($_POST['contact']);

            // Check if username already exists
            $stmt = $conn->prepare("SELECT id FROM accounts WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $message = "Error: Username '$username' already exists!";
                $stmt->close();
            } else {
                $stmt->close();
                if ($password !== $confirm_password) {
                    $message = "Passwords do not match!";
                } else {
                    // Hash the password before insertion
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO accounts (type, username, password, name, email, contact) VALUES ('Parent', ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssss", $username, $hashed_password, $name, $email, $contact);
                    if ($stmt->execute()) {
                        $message = "Parent account created successfully!";
                        // Refresh parents list after creation
                        $stmt->close();
                        $stmt = $conn->prepare("SELECT id, name FROM accounts WHERE type = 'Parent'");
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $parents = [];
                        while ($row = $result->fetch_assoc()) {
                            $parents[] = $row;
                        }
                    } else {
                        $message = "Error creating Parent account: " . $conn->error;
                    }
                    $stmt->close();
                }
            }
        } elseif ($_POST['action'] === 'link_student') {
            // Link Student to Parent
            $studentnumber = trim($_POST['studentnumber']);
            $firstname = trim($_POST['firstname']);
            $lastname = trim($_POST['lastname']);
            $middlename = trim($_POST['middlename'] ?? '');
            $level = $_POST['level'];
            $paymentplan = $_POST['paymentplan'];
            $parent_id = $_POST['parent_id'];
            $image_path = null;

            // Handle image upload
            if (isset($_FILES['student_image']) && $_FILES['student_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $file = $_FILES['student_image'];
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $max_size = 5 * 1024 * 1024; // 5MB

                // Validate file
                if (!in_array($file['type'], $allowed_types)) {
                    $message = "Error: Only JPG, PNG, and GIF files are allowed.";
                } elseif ($file['size'] > $max_size) {
                    $message = "Error: File size exceeds 5MB limit.";
                } else {
                    // Create uploads directory if it doesn't exist
                    $upload_dir = 'uploads/students/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    // Generate unique filename using student number
                    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $new_filename = $studentnumber . '.' . $file_extension;
                    $destination = $upload_dir . $new_filename;

                    // Move the uploaded file
                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        $image_path = $destination;
                    } else {
                        $message = "Error: Failed to upload the image.";
                    }
                }
            }

            // Proceed with student creation only if there are no errors with the image upload
            if (empty($message)) {
                // Validate student number format (must be exactly 10 characters, format YYYY-NNNNN)
                if (strlen($studentnumber) !== 10 || !preg_match('/^\d{4}-\d{5}$/', $studentnumber)) {
                    $message = "Invalid student number format. It must be exactly 10 characters in the format YYYY-NNNNN (e.g., 2020-10001).";
                } else {
                    // Check if studentnumber already exists
                    $stmt = $conn->prepare("SELECT studentnumber FROM students WHERE studentnumber = ?");
                    $stmt->bind_param("s", $studentnumber);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $message = "Error: Student number '$studentnumber' already exists!";
                        $stmt->close();
                    } else {
                        $stmt->close();
                        // Insert student record with image path
                        $stmt = $conn->prepare("INSERT INTO students (studentnumber, firstname, lastname, middlename, level, paymentplan, parent_id, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("ssssssis", $studentnumber, $firstname, $lastname, $middlename, $level, $paymentplan, $parent_id, $image_path);
                        try {
                            if ($stmt->execute()) {
                                $message = "Student linked to Parent successfully!";

                                // Generate tuition rows based on payment plan and level
                                $currentYear = date('Y'); // 2025
                                $nextYear = $currentYear + 1; // 2026
                                $status = 'Unpaid';

                                if ($paymentplan === 'Cash') {
                                    // No tuition rows for Cash plan
                                } elseif ($paymentplan === 'Monthly') {
                                    $amounts = [
                                        'Year 1' => 3000,
                                        'Junior Advanced Casa' => 3000,
                                        'Advanced Casa' => 3000
                                    ];
                                    $amount = $amounts[$level] ?? 0;

                                    $months = ['June', 'July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March'];
                                    $years = [$currentYear, $currentYear, $currentYear, $currentYear, $currentYear, $currentYear, $currentYear, $nextYear, $nextYear, $nextYear];
                                    $days = [10, 10, 10, 10, 10, 10, 10, 10, 10, 10];

                                    // Prepare the statement once outside the loop
                                    $stmt = $conn->prepare("INSERT INTO tuition (fee, duedate, amount, status, studentnumber) VALUES (?, ?, ?, ?, ?)");
                                    for ($i = 0; $i < 10; $i++) {
                                        $fee = "Tuition (" . $months[$i] . ")";
                                        $duedateStr = "$months[$i] $days[$i], $years[$i]";
                                        // Convert to YYYY-MM-DD format
                                        $duedate = date('Y-m-d', strtotime($duedateStr));
                                        $stmt->bind_param("ssdss", $fee, $duedate, $amount, $status, $studentnumber);
                                        $stmt->execute();
                                    }
                                    $stmt->close(); // Close after the loop
                                } elseif ($paymentplan === 'Quarterly') {
                                    $amounts = [
                                        'Year 1' => 9000,
                                        'Junior Advanced Casa' => 8000,
                                        'Advanced Casa' => 8000
                                    ];
                                    $amount = $amounts[$level] ?? 0;

                                    $quarters = ['2nd Quarter', '3rd Quarter', '4th Quarter'];
                                    $months = ['September', 'December', 'March'];
                                    $years = [$currentYear, $currentYear, $nextYear];

                                    // Prepare the statement once outside the loop
                                    $stmt = $conn->prepare("INSERT INTO tuition (fee, duedate, amount, status, studentnumber) VALUES (?, ?, ?, ?, ?)");
                                    for ($i = 0; $i < 3; $i++) {
                                        $fee = "Tuition (" . $quarters[$i] . ")";
                                        $duedateStr = "$months[$i] 10, $years[$i]";
                                        // Convert to YYYY-MM-DD format
                                        $duedate = date('Y-m-d', strtotime($duedateStr));
                                        $stmt->bind_param("ssdss", $fee, $duedate, $amount, $status, $studentnumber);
                                        $stmt->execute();
                                    }
                                    $stmt->close(); // Close after the loop
                                }
                            }
                        } catch (mysqli_sql_exception $e) {
                            $message = "Error: Student number '$studentnumber' already exists!";
                            $stmt->close();
                        }
                    }
                }
            }
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
    <title>Create Account</title>
    <style>
        #studentnumber-error {
            display: none;
            color: red;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
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
        <h3>Account Management</h3>
        <div class="action-buttons">
            <button class="action-btn" id="show-create-parent">Create Parent Account</button>
            <button class="action-btn" id="show-link-student">Link Student to Parent</button>
        </div>

        <div id="create-parent-form">
            <h4>Create Parent Account</h4>
            <form action="create-account.php" method="post">
                <input type="hidden" name="action" value="create_parent">
                <div class="form-row">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" placeholder="Enter Username" required>
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
                    <input type="text" id="name" name="name" placeholder="Enter Full Name" required>
                </div>
                <div class="form-row">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Enter Email" required>
                </div>
                <div class="form-row">
                    <label for="contact">Contact Number:</label>
                    <input type="text" id="contact" name="contact" placeholder="Enter Contact Number" required>
                </div>
                <button type="submit" class="submit-btn">Create Parent Account</button>
            </form>
        </div>

        <div id="link-student-form">
            <h4>Link Student to Parent</h4>
            <form action="create-account.php" method="post" enctype="multipart/form-data" onsubmit="return validateStudentNumber()">
                <input type="hidden" name="action" value="link_student">
                <div class="form-row">
                    <label for="studentnumber">Student Number:</label>
                    <input type="text" id="studentnumber" name="studentnumber" placeholder="Enter Student Number (e.g., 2020-10001)" required maxlength="10" pattern="\d{4}-\d{5}" title="Student number must be in the format YYYY-NNNNN (e.g., 2020-10001)">
                    <div id="studentnumber-error">Invalid student number format. It must be exactly 10 characters in the format YYYY-NNNNN (e.g., 2020-10001).</div>
                </div>
                <div class="form-row">
                    <label for="firstname">First Name:</label>
                    <input type="text" id="firstname" name="firstname" placeholder="Enter First Name" required>
                </div>
                <div class="form-row">
                    <label for="lastname">Last Name:</label>
                    <input type="text" id="lastname" name="lastname" placeholder="Enter Last Name" required>
                </div>
                <div class="form-row">
                    <label for="middlename">Middle Name:</label>
                    <input type="text" id="middlename" name="middlename" placeholder="Enter Middle Name (Optional)">
                </div>
                <div class="form-row">
                    <label for="level">Level:</label>
                    <select id="level" name="level" required>
                        <option value="">Select Level</option>
                        <option value="Year 1">Year 1</option>
                        <option value="Junior Advanced Casa">Junior Advanced Casa</option>
                        <option value="Advanced Casa">Advanced Casa</option>
                    </select>
                </div>
                <div class="form-row">
                    <label for="paymentplan">Payment Plan:</label>
                    <select id="paymentplan" name="paymentplan" required>
                        <option value="">Select Payment Plan</option>
                        <option value="Cash">Cash</option>
                        <option value="Monthly">Monthly</option>
                        <option value="Quarterly">Quarterly</option>
                    </select>
                </div>
                <div class="form-row">
                    <label for="parent-search">Associated Parent:</label>
                    <input type="text" id="parent-search" name="parent-search" placeholder="Search Parent Name..." required>
                    <input type="hidden" id="parent_id" name="parent_id" required>
                    <div id="parent-options"></div>
                </div>
                <div class="form-row">
                    <label for="student_image">Upload Student Image (Optional):</label>
                    <input type="file" id="student_image" name="student_image" accept="image/jpeg,image/png,image/gif">
                </div>
                <button type="submit" class="submit-btn">Link Student</button>
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
        const parents = <?php echo json_encode($parents); ?>;

        document.addEventListener("DOMContentLoaded", function () {
            const showCreateParentBtn = document.getElementById('show-create-parent');
            const showLinkStudentBtn = document.getElementById('show-link-student');
            const createParentForm = document.getElementById('create-parent-form');
            const linkStudentForm = document.getElementById('link-student-form');
            const parentSearch = document.getElementById('parent-search');
            const parentOptions = document.getElementById('parent-options');
            const parentIdInput = document.getElementById('parent_id');

            showCreateParentBtn.addEventListener('click', function () {
                createParentForm.style.display = 'block';
                linkStudentForm.style.display = 'none';
            });

            showLinkStudentBtn.addEventListener('click', function () {
                createParentForm.style.display = 'none';
                linkStudentForm.style.display = 'block';
            });

            // Searchable dropdown for parents
            parentSearch.addEventListener('input', function () {
                const query = this.value.toLowerCase();
                parentOptions.innerHTML = '';
                if (query) {
                    const filteredParents = parents.filter(parent => parent.name.toLowerCase().includes(query));
                    filteredParents.forEach(parent => {
                        const option = document.createElement('div');
                        option.textContent = parent.name;
                        option.addEventListener('click', () => {
                            parentSearch.value = parent.name;
                            parentIdInput.value = parent.id;
                            parentOptions.style.display = 'none';
                        });
                        parentOptions.appendChild(option);
                    });
                    parentOptions.style.display = filteredParents.length ? 'block' : 'none';
                } else {
                    parentOptions.style.display = 'none';
                }
            });

            // Hide options when clicking outside
            document.addEventListener('click', function (e) {
                if (!parentSearch.contains(e.target) && !parentOptions.contains(e.target)) {
                    parentOptions.style.display = 'none';
                }
            });
        });

        function validateStudentNumber() {
            const studentNumberInput = document.getElementById('studentnumber');
            const studentNumberError = document.getElementById('studentnumber-error');
            const studentNumber = studentNumberInput.value;

            const regex = /^\d{4}-\d{5}$/;
            if (studentNumber.length !== 10 || !regex.test(studentNumber)) {
                studentNumberError.style.display = 'block';
                return false;
            }
            studentNumberError.style.display = 'none';
            return true;
        }
    </script>
</body>
</html>