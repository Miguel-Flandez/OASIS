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

// Fetch Parent's ID and name from accounts table
$logged_in_username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT id, name FROM accounts WHERE username = ?");
$stmt->bind_param("s", $logged_in_username);
$stmt->execute();
$result = $stmt->get_result();
$parent = $result->fetch_assoc();
$parent_id = $parent['id'];
$parent_name = $parent['name'] ?? 'Anonymous';
$stmt->close();

// Fetch all students linked to this parent
$stmt = $conn->prepare("SELECT studentnumber FROM students WHERE parent_id = ?");
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Generate notifications
$notifications = [];
$current_date = new DateTime(); // Uses the server's current date/time based on the set timezone

foreach ($students as $student) {
    $studentnumber = $student['studentnumber'];

    // Fetch tuition fees
    $tuition_stmt = $conn->prepare("SELECT fee, duedate, amount, status FROM tuition WHERE studentnumber = ? AND status = 'Unpaid'");
    $tuition_stmt->bind_param("s", $studentnumber);
    $tuition_stmt->execute();
    $tuition_result = $tuition_stmt->get_result();
    $all_tuition_fees = $tuition_result->fetch_all(MYSQLI_ASSOC);
    $tuition_stmt->close();

    // Fetch misc fees
    $misc_stmt = $conn->prepare("SELECT fee, duedate, amount, status FROM misc WHERE studentnumber = ? AND status = 'Unpaid'");
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
}

// Handle password change
$password_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['old-password'])) {
    $old_password = $_POST['old-password'];
    $new_password = $_POST['new-password'];
    $re_new_password = $_POST['re-new-password'];

    // Verify old password
    $stmt = $conn->prepare("SELECT password FROM accounts WHERE username = ?");
    $stmt->bind_param("s", $logged_in_username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($old_password, $user['password'])) {
        if ($new_password === $re_new_password) {
            // Check password complexity: at least 1 uppercase letter and 1 number
            if (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
                $password_message = "New password must contain at least 1 uppercase letter and 1 number.";
            } else {
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE accounts SET password = ? WHERE username = ?");
                $update_stmt->bind_param("ss", $hashed_new_password, $logged_in_username);
                if ($update_stmt->execute()) {
                    $password_message = "Password updated successfully!";
                } else {
                    $password_message = "Failed to update password: " . $conn->error;
                }
                $update_stmt->close();
            }
        } else {
            $password_message = "New passwords do not match.";
        }
    } else {
        $password_message = "Old password is incorrect.";
    }
}

// Handle survey submission
$survey_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['rating'])) {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    if (!in_array($rating, ['1', '2', '3', '4', '5'])) {
        $survey_message = "Invalid rating selected.";
    } else {
        $stmt = $conn->prepare("INSERT INTO survey (rating, comment, name) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $rating, $comment, $parent_name);
        if ($stmt->execute()) {
            $survey_message = "Feedback submitted successfully!";
        } else {
            $survey_message = "Failed to submit feedback: " . $conn->error;
        }
        $stmt->close();
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
    <link rel="stylesheet" href="../css/userProfile.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dosis:wght@200..800&family=Fjalla+One&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oswald:wght@200..700&family=Playwrite+IN:wght@100..400&family=Poiret+One&family=Roboto+Slab:wght@100..900&family=Roboto:ital,wght@0,100..900;1,100..900&family=Smooch+Sans:wght@100..900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/de323b5912.js" crossorigin="anonymous"></script>
    <title>User Profile</title>
    
</head>
<body>
    <header>
        <div id="left">
            <img src="../assets/images/oakwood.jpg" alt="Oakwood" id="logo">
        </div>
        <h3 id="home-button" onclick="window.location.href='student-home.php'">Home</h3>
        <h3 id="profile-button">User Profile</h3>
        <div id="right">
            <i class="fa-solid fa-bell" id="notification-bell"></i>
            <button id="logout" onclick="if(confirm('Are you sure you want to log out?')) window.location.href='../logout.php';">Logout</button>
        </div>
    </header>

    <div id="student-information">
        <h3>Change Password</h3>
        <form method="POST" action="">
            <div id="info-list">
                <div id="info">
                    <p>Old Password: </p>
                    <input type="password" id="old-password" name="old-password" required>
                    <i class="fa-solid fa-key old"></i>
                </div>
                <div id="info">
                    <p>New Password: </p>
                    <input type="password" id="new-password" name="new-password" required>
                    <i class="fa-solid fa-key new"></i>
                </div>
                <div id="info">
                    <p>Re-type New Password: </p>
                    <input type="password" id="re-new-password" name="re-new-password" required>
                    <i class="fa-solid fa-key retype"></i>
                </div>
            </div>
            <button id="save" type="submit">Save</button>
        </form>
        <?php if (!empty($password_message)): ?>
            <p class="message <?php echo strpos($password_message, 'successfully') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($password_message); ?>
            </p>
        <?php endif; ?>

        <div id="survey-form">
            <h3>Feedback Survey</h3>
            <form method="POST" action="">
                <div class="rating">
                    <input type="radio" id="rating1" name="rating" value="1" required>
                    <label for="rating1">1</label>
                    <input type="radio" id="rating2" name="rating" value="2">
                    <label for="rating2">2</label>
                    <input type="radio" id="rating3" name="rating" value="3">
                    <label for="rating3">3</label>
                    <input type="radio" id="rating4" name="rating" value="4">
                    <label for="rating4">4</label>
                    <input type="radio" id="rating5" name="rating" value="5">
                    <label for="rating5">5</label>
                </div>
                <textarea id="comment" name="comment" placeholder="Enter your comments about the OASIS website..." required></textarea>
                <button id="submit-survey" type="submit">Submit Feedback</button>
            </form>
            <?php if (!empty($survey_message)): ?>
                <p class="message <?php echo strpos($survey_message, 'successfully') !== false ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($survey_message); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div id="notif-modal" class="hide">
        <div id="notifs">
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
    <script src="../js/password.js"></script>
    <script>
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
        });
    </script>
</body>
</html>