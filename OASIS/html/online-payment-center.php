<?php
session_start();

// Check if user is logged in and is a Parent
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['account_type'] !== 'Parent') {
    header("Location: ../index.html");
    exit;
}

// Include database connection
require_once '../includes/db_connection.php';

// Fetch Parent's ID from accounts table
$logged_in_username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT id FROM accounts WHERE username = ?");
$stmt->bind_param("s", $logged_in_username);
$stmt->execute();
$result = $stmt->get_result();
$parent = $result->fetch_assoc();
$parent_id = $parent['id'];
$stmt->close();

// Fetch all students linked to this parent
$stmt = $conn->prepare("SELECT studentnumber, firstname, lastname FROM students WHERE parent_id = ?");
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Default to first student or selected student
$selected_studentnumber = isset($_GET['student']) && in_array($_GET['student'], array_column($students, 'studentnumber')) 
    ? $_GET['student'] 
    : (!empty($students) ? $students[0]['studentnumber'] : null);

$student_data = null;
if ($selected_studentnumber) {
    // Find selected student's data
    foreach ($students as $student) {
        if ($student['studentnumber'] === $selected_studentnumber) {
            $student_data = $student;
            break;
        }
    }

    // Fetch tuition fees
    $tuition_stmt = $conn->prepare("SELECT id, fee, duedate, amount, status FROM tuition WHERE studentnumber = ?");
    $tuition_stmt->bind_param("s", $selected_studentnumber);
    $tuition_stmt->execute();
    $tuition_result = $tuition_stmt->get_result();
    $tuition_fees = $tuition_result->fetch_all(MYSQLI_ASSOC);
    $tuition_stmt->close();

    // Fetch misc fees
    $misc_stmt = $conn->prepare("SELECT id, fee, duedate, amount, status FROM misc WHERE studentnumber = ?");
    $misc_stmt->bind_param("s", $selected_studentnumber);
    $misc_stmt->execute();
    $misc_result = $misc_stmt->get_result();
    $misc_fees = $misc_result->fetch_all(MYSQLI_ASSOC);
    $misc_stmt->close();
}

// Prepare display variables
$student_number = $student_data ? $student_data['studentnumber'] : 'N/A';
$full_name = $student_data ? "{$student_data['lastname']}, {$student_data['firstname']}" : 'N/A';

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/OPC.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dosis:wght@200..800&family=Fjalla+One&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oswald:wght@200..700&family=Playwrite+IN:wght@100..400&family=Poiret+One&family=Roboto+Slab:wght@100..900&family=Roboto:ital,wght@0,100..900;1,100..900&family=Smooch+Sans:wght@100..900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/de323b5912.js" crossorigin="anonymous"></script>
    <title>Online Payment Center</title>
</head>
<body>
    <header>
        <div id="left">
        <img src="../assets/images/newlogo.png" alt="Oakwood" id="logo">
        </div>
        <h3 id="home-button" onclick="window.location.href='student-home.php'">Home</h3>
        <h3 id="opc-button">Online Payment Center</h3>
        <h3 id="profile-button">User Profile</h3>
        <div id="right">
            <i class="fa-solid fa-bell"></i>
            <button id="logout" onclick="if(confirm('Are you sure you want to log out?')) window.location.href='../logout.php';">Logout</button>
        </div>
    </header>

    <div id="payor-infoAndfees">
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
                            $disabled = $fee['status'] === 'paid' ? 'disabled' : '';
                            echo '<tr>';
                            echo '<td><input type="checkbox" class="fee-checkbox" data-amount="' . htmlspecialchars($fee['amount']) . '" ' . $disabled . '></td>';
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
                            $disabled = $fee['status'] === 'paid' ? 'disabled' : '';
                            echo '<tr>';
                            echo '<td><input type="checkbox" class="fee-checkbox" data-amount="' . htmlspecialchars($fee['amount']) . '" ' . $disabled . '></td>';
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
        </div>
    </div>

    <div id="notif-modal" class="hide">
        <div id="notifs">
            <div id="notif"><h3>Reminder</h3><p>Magbayad ka na please</p></div>
            <div id="notif"><h3>Reminder</h3><p>Magbayad ka na sabi e</p></div>
            <div id="notif"><h3>Reminder</h3><p>Alam namin bahay niyo</p></div>
        </div>
    </div>

    <script src="../js/common-functions.js"></script>
    <script src="../js/routing-student.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const checkboxes = document.querySelectorAll('.fee-checkbox');
            const totalSpan = document.getElementById('total');

            function updateTotal() {
                let total = 0;
                checkboxes.forEach(checkbox => {
                    if (checkbox.checked && !checkbox.disabled) {
                        total += parseFloat(checkbox.getAttribute('data-amount'));
                    }
                });
                totalSpan.textContent = total.toFixed(2);
            }

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateTotal);
            });

            // Initial total calculation
            updateTotal();
        });
    </script>
</body>
</html>