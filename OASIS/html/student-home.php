<?php
session_start();

// Check if user is logged in and is a Parent
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['account_type'] !== 'Parent') {
    header("Location: ../index.html");
    exit;
}

// Include database connection
require_once '../includes/db_connection.php';

// Set the timezone (adjust to your region)
date_default_timezone_set('Asia/Manila'); // Example: Philippines timezone; change to your timezone

// Fetch Parent's ID from accounts table
$logged_in_username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT id FROM accounts WHERE username = ?");
$stmt->bind_param("s", $logged_in_username);
$stmt->execute();
$result = $stmt->get_result();
$parent = $result->fetch_assoc();
$parent_id = $parent['id'];
$stmt->close();

// Fetch all students linked to this parent, including the image column
$stmt = $conn->prepare("SELECT studentnumber, firstname, lastname, level, image FROM students WHERE parent_id = ?");
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Default to first student if available, or handle selected student
$selected_studentnumber = isset($_GET['student']) && in_array($_GET['student'], array_column($students, 'studentnumber')) 
    ? $_GET['student'] 
    : (!empty($students) ? $students[0]['studentnumber'] : null);

$student_data = null;
$balance = 0;
$tuition_fees = [];
$misc_fees = [];
$notifications = [];

// Get the current date dynamically
$current_date = new DateTime(); // Uses the server's current date/time based on the set timezone

// Fetch fees and generate notifications for all students
foreach ($students as $student) {
    $studentnumber = $student['studentnumber'];

    // Fetch tuition fees
    $tuition_stmt = $conn->prepare("SELECT id, fee, duedate, amount, status FROM tuition WHERE studentnumber = ? AND status = 'Unpaid'");
    $tuition_stmt->bind_param("s", $studentnumber);
    $tuition_stmt->execute();
    $tuition_result = $tuition_stmt->get_result();
    $all_tuition_fees = $tuition_result->fetch_all(MYSQLI_ASSOC);
    $tuition_stmt->close();

    // Fetch misc fees
    $misc_stmt = $conn->prepare("SELECT id, fee, duedate, amount, status FROM misc WHERE studentnumber = ? AND status = 'Unpaid'");
    $misc_stmt->bind_param("s", $studentnumber);
    $misc_stmt->execute();
    $misc_result = $misc_stmt->get_result();
    $all_misc_fees = $misc_result->fetch_all(MYSQLI_ASSOC);
    $misc_stmt->close();

    // Generate notifications
    foreach ($all_tuition_fees as $fee) {
        $due_date = new DateTime($fee['duedate']);
        $interval = $current_date->diff($due_date);
        $days_until_due = $interval->invert ? -$interval->days : $interval->days;

        if ($days_until_due <= 7 && $days_until_due >= 0) {
            $notifications[] = [
                'title' => "Reminder ($studentnumber)",
                'message' => "Tuition ({$fee['fee']}) is due on {$fee['duedate']}"
            ];
        } elseif ($days_until_due < 0) {
            $notifications[] = [
                'title' => "Overdue ($studentnumber)",
                'message' => "Tuition ({$fee['fee']}) payment is overdue since {$fee['duedate']}, please settle payment as soon as possible"
            ];
        }
    }

    foreach ($all_misc_fees as $fee) {
        $due_date = new DateTime($fee['duedate']);
        $interval = $current_date->diff($due_date);
        $days_until_due = $interval->invert ? -$interval->days : $interval->days;

        if ($days_until_due <= 7 && $days_until_due >= 0) {
            $notifications[] = [
                'title' => "Reminder ($studentnumber)",
                'message' => "Miscellaneous ({$fee['fee']}) is due on {$fee['duedate']}"
            ];
        } elseif ($days_until_due < 0) {
            $notifications[] = [
                'title' => "Overdue ($studentnumber)",
                'message' => "Miscellaneous ({$fee['fee']}) payment is overdue since {$fee['duedate']}, please settle payment as soon as possible"
            ];
        }
    }

    // Load selected student's fees for display
    if ($studentnumber === $selected_studentnumber) {
        $tuition_fees = $all_tuition_fees;
        $misc_fees = $all_misc_fees;
        $student_data = $student;
        $tuition_total = array_sum(array_column($tuition_fees, 'amount'));
        $misc_total = array_sum(array_column($misc_fees, 'amount'));
        $balance = $tuition_total + $misc_total;
    }
}

// Prepare display variables
$student_number = $student_data ? $student_data['studentnumber'] : 'N/A';
$full_name = $student_data ? "{$student_data['lastname']}, {$student_data['firstname']}" : 'N/A';
$level = $student_data ? $student_data['level'] : 'N/A';
$student_image = $student_data && $student_data['image'] ? $student_data['image'] : 'https://st3.depositphotos.com/6672868/13701/v/450/depositphotos_137014128-stock-illustration-user-profile-icon.jpg';

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/student-home.css">
    <link rel="stylesheet" href="../css/OPC.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dosis:wght@200..800&family=Fjalla+One&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oswald:wght@200..700&family=Playwrite+IN:wght@100..400&family=Poiret+One&family=Roboto+Slab:wght@100..900&family=Roboto:ital,wght@0,100..900;1,100..900&family=Smooch+Sans:wght@100..900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/de323b5912.js" crossorigin="anonymous"></script>
    <title>Home - Parent Dashboard</title>
</head>
<body>
    <header>
        <div id="left">
            <img src="../assets/images/oakwood.jpg" alt="Oakwood" id="logo"/>
        </div>
        <h3 id="home-button" class="active">Home</h3>
        <h3 id="profile-button">User Profile</h3>
        <div id="right">
            <i class="fa-solid fa-bell" id="notification-bell"></i>
            <button id="logout" onclick="if(confirm('Are you sure you want to log out?')) window.location.href='../logout.php';">Logout</button>
        </div>
    </header>

    <div id="homescreen">   
        <div id="sidebar">
            <div id="student-information">
                <div id="left-info">
                    <div id="student-dp-container">
                        <img src="<?php echo htmlspecialchars($student_image); ?>" 
                             alt="Student Image" 
                             id="student-dp"
                             onerror="this.onerror=null; this.src='../assets/images/placeholder.jpg';">
                    </div>
                    <h3>Student Info</h3>
                </div>
                <div id="info-list">
                    <div class="info-item">
                        <label>Student No:</label>
                        <select id="student-number" onchange="location.href='?student=' + this.value;">
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo htmlspecialchars($student['studentnumber']); ?>" 
                                        <?php echo $student['studentnumber'] === $selected_studentnumber ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($student['studentnumber']); ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if (empty($students)): ?>
                                <option value="">No students found</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="info-item">
                        <label>Name:</label>
                        <span><?php echo htmlspecialchars($full_name); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Level:</label>
                        <span><?php echo htmlspecialchars($level); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Balance:</label>
                        <span><?php echo number_format($balance, 2) . ' PHP'; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="OPC">
        <div id="payor-info">
            <h3>Payor Information</h3>
            <div id="forms">
                <div id="form-pair">
                    <label for="student-number">Student Number: </label>
                    <select id="student-number" onchange="location.href='?student=' + this.value;">
                        <?php foreach ($students as $student): ?>
                            <option value="<?php echo htmlspecialchars($student['studentnumber']); ?>" 
                                <?php echo $student['studentnumber'] === $selected_studentnumber ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($student['studentnumber']); ?>
                            </option>
                        <?php endforeach; ?>
                        <?php if (empty($students)): ?>
                            <option value="">No students found</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div id="form-pair">
                    <label for="name">Name: </label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($full_name); ?>" readonly>
                </div>
            </div>
        </div>

        <div id="tuition-fees">
            <h3>Tuition</h3>
            <table id="tuition-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Fee</th>
                        <th>Due Date</th>
                        <th>Amount (PHP)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($tuition_fees)) {
                        foreach ($tuition_fees as $fee) {
                            $disabled = $fee['status'] === 'Paid' ? 'disabled' : '';
                            echo '<tr>';
                            echo '<td><input type="checkbox" class="fee-checkbox" data-id="' . htmlspecialchars($fee['id']) . '" data-amount="' . htmlspecialchars($fee['amount']) . '" ' . $disabled . '></td>';
                            echo '<td>' . htmlspecialchars($fee['fee']) . '</td>';
                            echo '<td>' . htmlspecialchars($fee['duedate']) . '</td>';
                            echo '<td>' . number_format($fee['amount'], 2) . '</td>';
                            echo '<td>' . htmlspecialchars($fee['status']) . '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5">No tuition fees found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>

            <h3>Miscellaneous Fees</h3>
            <table id="misc-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Fee</th>
                        <th>Due Date</th>
                        <th>Amount (PHP)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($misc_fees)) {
                        foreach ($misc_fees as $fee) {
                            $disabled = $fee['status'] === 'Paid' ? 'disabled' : '';
                            echo '<tr>';
                            echo '<td><input type="checkbox" class="fee-checkbox" data-id="' . htmlspecialchars($fee['id']) . '" data-amount="' . htmlspecialchars($fee['amount']) . '" ' . $disabled . '></td>';
                            echo '<td>' . htmlspecialchars($fee['fee']) . '</td>';
                            echo '<td>' . htmlspecialchars($fee['duedate']) . '</td>';
                            echo '<td>' . number_format($fee['amount'], 2) . '</td>';
                            echo '<td>' . htmlspecialchars($fee['status']) . '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5">No miscellaneous fees found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>

            <div id="total-amount">
                <h3>Total Amount (PHP): <span id="total">0</span></h3>
            </div>

            <div id="payment-section">
                <select id="payment-option">
                    <option value="gcash">GCash</option>
                    <option value="paymaya">PayMaya</option>
                    <option value="bpi">BPI</option>
                </select>
                <button id="proceed-payment-btn" onclick="processPayment()">Proceed to Payment</button>
            </div>
        </div>
    </div>

    <div id="notif-modal" class="hide">
        <div id="notifs">
            <div id="notif" class="pt-4 changePassNotif">
                <h3>Password Change Recommended</h3>
                <p>For the security of your account, please change your password.
                <span class="redirect-button" style="text-decoration: underline;font-weight:bold;cursor:pointer;">Click here</span> to proceed.</p>
                <!-- create a condition if password is not the default password -->
            </div>
            <?php if (empty($notifications)): ?>
                <div id="notif"><h3>No Notifications</h3><p>There are no upcoming or overdue payments.</p></div>
            <?php else: ?>
                <?php foreach ($notifications as $notif): ?>
                    <div id="notif">
                        <h3><?php echo htmlspecialchars($notif['title']); ?></h3>
                        <p><?php echo htmlspecialchars($notif['message']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="../js/common-functions.js"></script>
    <script src="../js/routing-student.js"></script>
    <script src="../js/to-change-password.js"></script>
    <script>
        // Function to calculate and update the total amount
        function updateTotalAmount() {
            const checkboxes = document.querySelectorAll('.fee-checkbox');
            let total = 0;
            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    const amount = parseFloat(checkbox.getAttribute('data-amount')) || 0;
                    total += amount;
                }
            });
            document.getElementById('total').textContent = total.toFixed(2);
        }

        // Notification toggle
        document.addEventListener('DOMContentLoaded', function() {
            const bell = document.getElementById('notification-bell');
            const notifModal = document.getElementById('notif-modal');

            bell.addEventListener('click', function() {
                notifModal.style.display = notifModal.style.display === 'block' ? 'none' : 'block';
            });

            // Close notifications when clicking outside
            document.addEventListener('click', function(e) {
                if (!bell.contains(e.target) && !notifModal.contains(e.target)) {
                    notifModal.style.display = 'none';
                }
            });

            // Total amount calculation
            const checkboxes = document.querySelectorAll('.fee-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateTotalAmount);
            });
            updateTotalAmount();
        });

        // Process payment function
        function processPayment() {
            const total = parseFloat(document.getElementById('total').textContent);
            const paymentMethod = document.getElementById('payment-option').value;
            const studentNumber = document.getElementById('student-number').value;

            if (total <= 0) {
                alert('Please select at least one fee to pay.');
                return;
            }

            // Collect selected fee IDs
            const selectedFees = [];
            document.querySelectorAll('.fee-checkbox:checked').forEach(checkbox => {
                selectedFees.push(checkbox.getAttribute('data-id'));
            });

            const feesParam = encodeURIComponent(selectedFees.join(','));
            const urlBase = `?amount=${total}&student=${encodeURIComponent(studentNumber)}&fees=${feesParam}`;

            if (paymentMethod === 'gcash') {
                window.location.href = `gcash_payment.php${urlBase}`;
            } else if (paymentMethod === 'paymaya') {
                window.location.href = `process_paymentpaymaya.php${urlBase}`;
            } else if (paymentMethod === 'bpi') {
                window.location.href = `BPIPaymentProcess.php${urlBase}`;
            } else if (paymentMethod === 'gcashpaymongo') {
                window.location.href = `GCash_Paymongo.php${urlBase}`;
            }
        }
    </script>
</body>
</html>