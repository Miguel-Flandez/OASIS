<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['account_type'] !== 'Employee') {
    header("Location: ../index.html");
    exit;
}

require_once '../includes/db_connection.php';

// Fetch all student numbers and levels from the students table for the dropdown
$students = [];
$levels = [];
$stmt = $conn->prepare("SELECT studentnumber, level FROM students");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $students[] = $row['studentnumber'];
    if (!in_array($row['level'], $levels)) {
        $levels[] = $row['level'];
    }
}
sort($levels); // Sort levels for better presentation (e.g., Year 1, Junior Advanced Casa, Advanced Casa)
$stmt->close();

// Handle selected student and fetch tuition/misc data
$selectedStudent = isset($_GET['student']) ? $_GET['student'] : (count($students) > 0 ? $students[0] : '');
$tuitionData = [];
$miscData = [];

if ($selectedStudent) {
    // Fetch tuition data
    $stmt = $conn->prepare("SELECT id, fee, duedate, amount FROM tuition WHERE studentnumber = ?");
    $stmt->bind_param("s", $selectedStudent);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $tuitionData[] = $row;
    }
    $stmt->close();

    // Fetch misc data
    $stmt = $conn->prepare("SELECT id, fee, duedate, amount FROM misc WHERE studentnumber = ?");
    $stmt->bind_param("s", $selectedStudent);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $miscData[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/employee-payment-center.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dosis:wght@200..800&family=Fjalla+One&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oswald:wght@200..700&family=Playwrite+IN:wght@100..400&family=Poiret+One&family=Roboto+Slab:wght@100..900&family=Roboto:ital,wght@0,100..900;1,100..900&family=Smooch+Sans:wght@100..900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/de323b5912.js" crossorigin="anonymous"></script>
    <title>Employee Payment Center</title>
    <style>
        /* Add styles for the Bulk Add Fee sections */
        .bulk-add-fee-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .bulk-add-fee-section label {
            font-weight: bold;
            margin-right: 10px;
        }

        .bulk-add-fee-section select, .bulk-add-fee-section input {
            padding: 5px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .bulk-add-fee-section button {
            padding: 5px 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .bulk-add-fee-section button:hover {
            background-color: #45a049;
        }

        .loading-spinner {
            display: none;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <header>
        <div id="left">
            <img src="../assets/images/oakwood.jpg" alt="Oakwood" id="logo">
        </div>
        <h3 id="employee-dashboard-button">Dashboard</h3>
        <h3 id="employee-oasis-accounts">Oasis Accounts</h3>
        <h3 id="employee-payment-center">Payment Center</h3>
        <div id="right">
            <button id="logout" onclick="if(confirm('Are you sure you want to log out?')) window.location.href='../logout.php';">Logout</button>
        </div>
    </header>

    <div id="payment-container">
        <!-- Bulk Add Fee (Tuition) Section -->
        <div class="bulk-add-fee-section">
            <h3>Add Bulk Fee (Tuition)</h3>
            <form class="bulk-add-fee-form" data-table="tuition">
                <label for="tuition-bulk-level">Level:</label>
                <select id="tuition-bulk-level" name="level" required>
                    <option value="">Select Level</option>
                    <?php foreach ($levels as $level): ?>
                        <option value="<?php echo htmlspecialchars($level); ?>">
                            <?php echo htmlspecialchars($level); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="tuition-bulk-fee">Fee:</label>
                <input type="text" id="tuition-bulk-fee" name="fee" placeholder="e.g., Tuition Fee Q2" required>
                <label for="tuition-bulk-duedate">Due Date:</label>
                <input type="date" id="tuition-bulk-duedate" name="duedate" required>
                <label for="tuition-bulk-amount">Amount:</label>
                <input type="number" id="tuition-bulk-amount" name="amount" step="0.01" placeholder="e.g., 5000.00" required>
                <button type="submit">Apply Fee</button>
                <span class="loading-spinner" data-table="tuition"><i class="fas fa-spinner fa-spin"></i></span>
            </form>
        </div>

        <!-- Bulk Add Fee (Miscellaneous) Section -->
        <div class="bulk-add-fee-section">
            <h3>Add Bulk Fee (Miscellaneous)</h3>
            <form class="bulk-add-fee-form" data-table="misc">
                <label for="misc-bulk-level">Level:</label>
                <select id="misc-bulk-level" name="level" required>
                    <option value="">Select Level</option>
                    <?php foreach ($levels as $level): ?>
                        <option value="<?php echo htmlspecialchars($level); ?>">
                            <?php echo htmlspecialchars($level); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="misc-bulk-fee">Fee:</label>
                <input type="text" id="misc-bulk-fee" name="fee" placeholder="e.g., Field Trip" required>
                <label for="misc-bulk-duedate">Due Date:</label>
                <input type="date" id="misc-bulk-duedate" name="duedate" required>
                <label for="misc-bulk-amount">Amount:</label>
                <input type="number" id="misc-bulk-amount" name="amount" step="0.01" placeholder="e.g., 500.00" required>
                <button type="submit">Apply Fee</button>
                <span class="loading-spinner" data-table="misc"><i class="fas fa-spinner fa-spin"></i></span>
            </form>
        </div>

        <div id="account-select">
            <label for="student-search">Select Student: </label>
            <input type="text" id="student-search" placeholder="Search student number...">
            <div id="student-options"></div>
        </div>

        <?php if ($selectedStudent): ?>
            <h3>Tuition Ledger for <?php echo htmlspecialchars($selectedStudent); ?></h3>
            <table class="ledger-table" id="tuition-table">
                <thead>
                    <tr>
                        <th>Fee</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="tuition-table-body">
                    <?php foreach ($tuitionData as $row): ?>
                        <tr>
                            <td><input type="text" value="<?php echo htmlspecialchars($row['fee']); ?>" data-id="<?php echo $row['id']; ?>" data-table="tuition" data-field="fee"></td>
                            <td><input type="date" value="<?php echo htmlspecialchars($row['duedate']); ?>" data-id="<?php echo $row['id']; ?>" data-table="tuition" data-field="duedate"></td>
                            <td><input type="number" step="0.01" value="<?php echo htmlspecialchars($row['amount']); ?>" data-id="<?php echo $row['id']; ?>" data-table="tuition" data-field="amount"></td>
                            <td><button class="delete-btn" data-id="<?php echo $row['id']; ?>" data-table="tuition">Delete</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button class="add-row-btn" data-table="tuition">Add Row to Tuition</button>

            <h3>Miscellaneous Ledger for <?php echo htmlspecialchars($selectedStudent); ?></h3>
            <table class="ledger-table" id="misc-table">
                <thead>
                    <tr>
                        <th>Fee</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="misc-table-body">
                    <?php foreach ($miscData as $row): ?>
                        <tr>
                            <td><input type="text" value="<?php echo htmlspecialchars($row['fee']); ?>" data-id="<?php echo $row['id']; ?>" data-table="misc" data-field="fee"></td>
                            <td><input type="date" value="<?php echo htmlspecialchars($row['duedate']); ?>" data-id="<?php echo $row['id']; ?>" data-table="misc" data-field="duedate"></td>
                            <td><input type="number" step="0.01" value="<?php echo htmlspecialchars($row['amount']); ?>" data-id="<?php echo $row['id']; ?>" data-table="misc" data-field="amount"></td>
                            <td><button class="delete-btn" data-id="<?php echo $row['id']; ?>" data-table="misc">Delete</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button class="add-row-btn" data-table="misc">Add Row to Miscellaneous</button>

            <div class="button-container">
                <button id="saveChangesBtn">Save Changes</button>
            </div>
        <?php endif; ?>
    </div>

    <script src="../js/common-functions.js"></script>
    <script src="../js/routing-employee.js"></script>
    <script>
        const students = <?php echo json_encode($students); ?>;
        let selectedStudent = '<?php echo $selectedStudent; ?>';
        const levels = <?php echo json_encode($levels); ?>;

        document.addEventListener("DOMContentLoaded", function () {
            const studentSearch = document.getElementById('student-search');
            const studentOptions = document.getElementById('student-options');

            // Populate initial value if a student is selected
            if (selectedStudent) {
                studentSearch.value = selectedStudent;
            }

            // Searchable dropdown functionality
            studentSearch.addEventListener('input', function () {
                const query = this.value.toLowerCase();
                studentOptions.innerHTML = '';
                if (query) {
                    const filteredStudents = students.filter(student => student.toLowerCase().includes(query));
                    filteredStudents.forEach(student => {
                        const option = document.createElement('div');
                        option.textContent = student;
                        option.addEventListener('click', () => {
                            studentSearch.value = student;
                            studentOptions.style.display = 'none';
                            window.location.href = `?student=${encodeURIComponent(student)}`;
                        });
                        studentOptions.appendChild(option);
                    });
                    studentOptions.style.display = filteredStudents.length ? 'block' : 'none';
                } else {
                    studentOptions.style.display = 'none';
                }
            });

            // Hide options when clicking outside
            document.addEventListener('click', function (e) {
                if (!studentSearch.contains(e.target) && !studentOptions.contains(e.target)) {
                    studentOptions.style.display = 'none';
                }
            });

            // Add row functionality with unique row ID
            let rowCounter = 0;
            document.querySelectorAll('.add-row-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const tableType = this.getAttribute('data-table');
                    const tableBody = document.getElementById(`${tableType}-table-body`);
                    const uniqueRowId = `new-${rowCounter++}`;
                    const newRow = document.createElement('tr');
                    newRow.setAttribute('data-row-id', uniqueRowId);
                    newRow.innerHTML = `
                        <td><input type="text" data-table="${tableType}" data-id="${uniqueRowId}" data-field="fee"></td>
                        <td><input type="date" data-table="${tableType}" data-id="${uniqueRowId}" data-field="duedate"></td>
                        <td><input type="number" step="0.01" data-table="${tableType}" data-id="${uniqueRowId}" data-field="amount"></td>
                        <td><button class="delete-btn" data-id="${uniqueRowId}" data-table="${tableType}" disabled>Delete</button></td>
                    `;
                    tableBody.appendChild(newRow);
                });
            });

            // Delete row functionality
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function () {
                    if (confirm('Are you sure you want to delete this row?')) {
                        const id = this.getAttribute('data-id');
                        const table = this.getAttribute('data-table');
                        if (id.startsWith('new-')) {
                            this.closest('tr').remove();
                            return;
                        }

                        fetch('delete_ledger_row.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `student=${encodeURIComponent(selectedStudent)}&table=${encodeURIComponent(table)}&id=${encodeURIComponent(id)}`
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                alert('Row deleted successfully!');
                                window.location.reload();
                            } else {
                                alert('Error deleting row: ' + result.error);
                            }
                        })
                        .catch(error => console.error('Error deleting row:', error));
                    }
                });
            });

            // Save changes functionality
            document.getElementById('saveChangesBtn').addEventListener('click', function () {
                const changesByRow = {};
                document.querySelectorAll('tr[data-row-id], tr input[data-id]:not([data-id="new"])').forEach(element => {
                    const row = element.tagName === 'TR' ? element : element.closest('tr');
                    const rowId = row.querySelector('input[data-id]')?.getAttribute('data-id');
                    if (!rowId) return;

                    if (!changesByRow[rowId]) {
                        changesByRow[rowId] = { table: null, fields: {} };
                    }

                    row.querySelectorAll('input[data-table]').forEach(input => {
                        const table = input.getAttribute('data-table');
                        const field = input.getAttribute('data-field');
                        const value = input.value.trim();
                        if (value) { // Only include non-empty values
                            changesByRow[rowId].table = table;
                            changesByRow[rowId].fields[field] = value;
                        }
                    });
                });

                const changes = Object.entries(changesByRow).map(([id, data]) => ({
                    id,
                    table: data.table,
                    ...data.fields
                }));

                if (changes.length === 0) {
                    alert('No changes to save.');
                    return;
                }

                fetch('update_ledger.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `student=${encodeURIComponent(selectedStudent)}&changes=${encodeURIComponent(JSON.stringify(changes))}`
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Changes saved successfully!');
                        window.location.reload();
                    } else {
                        alert('Error saving changes: ' + result.error);
                    }
                })
                .catch(error => {
                    console.error('Error saving changes:', error);
                    alert('An error occurred while saving changes. Check the console for details.');
                });
            });

            // Bulk Add Fee functionality for both forms
            document.querySelectorAll('.bulk-add-fee-form').forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const table = this.getAttribute('data-table');
                    const level = this.querySelector(`#${table}-bulk-level`).value;
                    const fee = this.querySelector(`#${table}-bulk-fee`).value;
                    const duedate = this.querySelector(`#${table}-bulk-duedate`).value;
                    const amount = this.querySelector(`#${table}-bulk-amount`).value;
                    const loadingSpinner = this.querySelector(`.loading-spinner[data-table="${table}"]`);

                    if (!level || !fee || !duedate || !amount) {
                        alert('Please fill in all fields.');
                        return;
                    }

                    loadingSpinner.style.display = 'inline-block';
                    fetch('bulk_add_fee.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `table=${encodeURIComponent(table)}&level=${encodeURIComponent(level)}&fee=${encodeURIComponent(fee)}&duedate=${encodeURIComponent(duedate)}&amount=${encodeURIComponent(amount)}`
                    })
                    .then(response => response.json())
                    .then(result => {
                        loadingSpinner.style.display = 'none';
                        if (result.success) {
                            alert(result.message);
                            form.reset(); // Clear the form
                            window.location.reload(); // Refresh to reflect changes
                        } else {
                            alert('Error: ' + result.error);
                        }
                    })
                    .catch(error => {
                        loadingSpinner.style.display = 'none';
                        console.error(`Error adding bulk fee to ${table}:`, error);
                        alert(`An error occurred while adding the bulk fee to ${table}.`);
                    });
                });
            });
        });
    </script>
</body>
</html>