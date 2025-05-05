<?php
session_start();

// Check admin access
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['account_type'] !== 'Admin') {
    header("Location: ../index.html");
    exit;
}

// Database connection
$host = "localhost";
$username = "root";
$password = ""; // Update if set in phpMyAdmin
$database = "oasis";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/oasis-accounts.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dosis:wght@200..800&family=Fjalla+One&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oswald:wght@200..700&family=Playwrite+IN:wght@100..400&family=Poiret+One&family=Roboto+Slab:wght@100..900&family=Roboto:ital,wght@0,100..900;1,100..900&family=Smooch+Sans:wght@100..900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/de323b5912.js" crossorigin="anonymous"></script>
    <title>OASIS Accounts</title>

    <!-- Add custom CSS to fix image size -->
    <style>
        .image-preview {
            max-width: 150px; /* Set a fixed maximum width */
            max-height: 150px; /* Set a fixed maximum height */
            width: auto; /* Allow width to scale proportionally */
            height: auto; /* Allow height to scale proportionally */
            object-fit: contain; /* Ensure the image scales without distortion */
            margin: 10px 0; /* Add some spacing around the image */
            border: 1px solid #ccc; /* Optional: Add a border for better visibility */
            border-radius: 5px; /* Optional: Add rounded corners */
        }

        .image-container {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 10px; /* Add spacing between the image and buttons */
        }

        .modal-content {
            max-width: 500px; /* Ensure the modal doesn't stretch too much */
            width: 90%; /* Responsive width */
            padding: 20px;
        }

        .form-row {
            margin-bottom: 15px; /* Add consistent spacing between form rows */
        }

        .delete-photo-btn {
            background-color: #ff4444; /* Red color for delete button */
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        .delete-photo-btn:hover {
            background-color: #cc0000; /* Darker red on hover */
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
        <div id="searchContainer">
            <label for="searchInput">Search</label>
            <input type="text" id="searchInput" placeholder="Search by any detail (e.g., 2020, name, level)">
        </div>
        <table id="accounts-table">
            <thead>
                <tr>
                    <th>Student No.</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Middle Name</th>
                    <th>Level</th>
                    <th>Payment Plan</th>
                    <th>Parent Name</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="accountsTableBody">
                <?php
                $conn = new mysqli($host, $username, $password, $database);
                $query = "SELECT s.studentnumber, s.firstname, s.lastname, s.middlename, s.level, s.paymentplan, a.name, a.email, a.contact, a.id as parent_id, s.image
                         FROM students s 
                         LEFT JOIN accounts a ON s.parent_id = a.id 
                         WHERE a.type = 'Parent'";
                $result = $conn->query($query);
                $allRows = [];
                while ($row = $result->fetch_assoc()) {
                    $allRows[] = $row;
                    echo "<tr data-studentnumber='{$row['studentnumber']}'>";
                    echo "<td>{$row['studentnumber']}</td>";
                    echo "<td>{$row['firstname']}</td>";
                    echo "<td>{$row['lastname']}</td>";
                    echo "<td>{$row['middlename']}</td>";
                    echo "<td>{$row['level']}</td>";
                    echo "<td>{$row['paymentplan']}</td>";
                    echo "<td>{$row['name']}</td>";
                    echo "<td>{$row['email']}</td>";
                    echo "<td>{$row['contact']}</td>";
                    echo "<td>
                            <button class='action-btn edit-btn' data-studentnumber='{$row['studentnumber']}'>Edit</button>
                            <button class='action-btn view-btn' data-studentnumber='{$row['studentnumber']}'>View</button>
                            <button class='action-btn delete-student-btn' data-studentnumber='{$row['studentnumber']}'>Delete Student</button>
                            <button class='action-btn delete-parent-btn' data-studentnumber='{$row['studentnumber']}'>Delete Parent</button>
                          </td>";
                    echo "</tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="document.getElementById('editModal').style.display='none'">×</button>
            <h3>Edit Student Details</h3>
            <form id="editForm" method="POST" action="update_account.php" enctype="multipart/form-data">
                <input type="hidden" id="editStudentNumber" name="studentnumber" required>
                <input type="hidden" id="deleteImage" name="delete_image" value="0">
                <input type="hidden" id="editParentId" name="parent_id">
                <div class="form-row">
                    <label>First Name:</label>
                    <input type="text" id="editFirstName" name="firstname" required>
                </div>
                <div class="form-row">
                    <label>Last Name:</label>
                    <input type="text" id="editLastName" name="lastname" required>
                </div>
                <div class="form-row">
                    <label>Middle Name:</label>
                    <input type="text" id="editMiddleName" name="middlename">
                </div>
                <div class="form-row">
                    <label>Level:</label>
                    <select id="editLevel" name="level" required>
                        <option value="Year 1">Year 1</option>
                        <option value="Junior Advanced Casa">Junior Advanced Casa</option>
                        <option value="Advanced Casa">Advanced Casa</option>
                    </select>
                </div>
                <div class="form-row">
                    <label>Parent Name:</label>
                    <input type="text" id="editParentName" name="name" required>
                </div>
                <div class="form-row">
                    <label>Email:</label>
                    <input type="email" id="editEmail" name="email" required>
                </div>
                <div class="form-row">
                    <label>Contact:</label>
                    <input type="text" id="editContact" name="contact" required>
                </div>
                <div class="form-row">
                    <label>Password:</label>
                    <input type="password" id="editPassword" name="password">
                </div>
                <div class="form-row">
                    <label>Student Image:</label>
                    <div class="image-container">
                        <img id="imagePreview" src="" alt="Student Image" class="image-preview" style="display: none;">
                        <input type="file" id="editStudentImage" name="student_image" accept="image/jpeg,image/png,image/gif">
                        <button type="button" id="deletePhotoBtn" class="action-btn delete-photo-btn" style="display: none;">Delete Photo</button>
                    </div>
                </div>
                <button type="submit" id="saveChanges">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- View History Modal -->
    <div id="viewHistoryModal" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="document.getElementById('viewHistoryModal').style.display='none'">×</button>
            <h3>Transaction History</h3>
            <table id="historyTable">
                <thead>
                    <tr>
                        <th>Receipt Number</th>
                        <th>Transaction Date</th>
                        <th>Amount Paid</th>
                        <th>Fees</th>
                    </tr>
                </thead>
                <tbody id="historyTableBody"></tbody>
            </table>
        </div>
    </div>
    
    <script src="../js/routing-admin.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const tableBody = document.getElementById('accountsTableBody');
            const allRows = Array.from(tableBody.getElementsByTagName('tr'));
            const editForm = document.getElementById('editForm');

            // Search functionality
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                allRows.forEach(row => {
                    const rowText = Array.from(row.getElementsByTagName('td'))
                        .map(td => td.textContent.toLowerCase())
                        .join(' ');
                    row.style.display = rowText.includes(searchTerm) ? '' : 'none';
                });
            });

            // Edit button functionality
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const studentnumber = this.dataset.studentnumber;
                    fetch(`fetch_account_details.php?studentnumber=${studentnumber}`)
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('editStudentNumber').value = data.studentnumber;
                            document.getElementById('editFirstName').value = data.firstname;
                            document.getElementById('editLastName').value = data.lastname;
                            document.getElementById('editMiddleName').value = data.middlename;
                            document.getElementById('editLevel').value = data.level;
                            document.getElementById('editParentName').value = data.name;
                            document.getElementById('editEmail').value = data.email;
                            document.getElementById('editContact').value = data.contact;
                            document.getElementById('editParentId').value = data.parent_id;

                            const imagePreview = document.getElementById('imagePreview');
                            const deletePhotoBtn = document.getElementById('deletePhotoBtn');
                            if (data.image) {
                                imagePreview.src = data.image;
                                imagePreview.style.display = 'block';
                                deletePhotoBtn.style.display = 'inline-block';
                            } else {
                                imagePreview.style.display = 'none';
                                deletePhotoBtn.style.display = 'none';
                            }

                            document.getElementById('editModal').style.display = 'block';
                        });
                });
            });

            // Handle form submission via AJAX
            editForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                const formData = new FormData(this); // Collect form data, including file uploads

                fetch('update_account.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Account updated successfully!');
                        location.reload(); // Refresh the page to reflect changes
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the account.');
                });
            });

            // Delete Photo button functionality
            document.getElementById('deletePhotoBtn').addEventListener('click', function() {
                const imagePreview = document.getElementById('imagePreview');
                const deleteImageInput = document.getElementById('deleteImage');
                imagePreview.style.display = 'none';
                this.style.display = 'none';
                deleteImageInput.value = '1'; // Indicate image should be deleted
            });

            // View history button functionality
            document.querySelectorAll('.view-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const studentnumber = this.dataset.studentnumber;
                    fetch(`fetch_history.php?studentnumber=${studentnumber}`)
                        .then(response => response.json())
                        .then(history => {
                            const tbody = document.getElementById('historyTableBody');
                            tbody.innerHTML = '';
                            history.forEach(item => {
                                tbody.innerHTML += `
                                    <tr>
                                        <td>${item.receiptnumber}</td>
                                        <td>${item.transactiondate}</td>
                                        <td>${item.amountpaid}</td>
                                        <td>${item.fees}</td>
                                    </tr>
                                `;
                            });
                            document.getElementById('viewHistoryModal').style.display = 'block';
                        });
                });
            });

            // Delete student button
            document.querySelectorAll('.delete-student-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const studentnumber = this.dataset.studentnumber;
                    if (confirm(`Delete student ${studentnumber}?`)) {
                        fetch('delete_student.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `studentnumber=${studentnumber}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('Error: ' + data.error);
                            }
                        });
                    }
                });
            });

            // Delete parent button
            document.querySelectorAll('.delete-parent-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const studentnumber = this.dataset.studentnumber;
                    if (confirm(`WARNING: Deleting the parent account for student ${studentnumber} will also delete ALL associated students and their transaction history, tuition, and miscellaneous fees. Are you sure?`)) {
                        fetch('delete_parent.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `studentnumber=${studentnumber}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Parent account and all associated data deleted successfully!');
                                location.reload();
                            } else {
                                alert('Error: ' + data.error);
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>