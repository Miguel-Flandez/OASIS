<?php
session_start();

// Check if user is logged in and is an admin
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
    <title>Employee Accounts</title>
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
            <input type="text" id="searchInput" placeholder="Search by any detail (e.g., username, name, email)">
        </div>
        
        <table id="accounts-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Account Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="accountsTableBody">
                <!-- Populated by PHP -->
                <?php
                $conn = new mysqli($host, $username, $password, $database);
                $query = "SELECT username, name, email, contact, type, id FROM accounts WHERE type = 'Employee'";
                $result = $conn->query($query);
                $allRows = [];
                while ($row = $result->fetch_assoc()) {
                    $allRows[] = $row;
                    echo "<tr data-username='{$row['username']}'>";
                    echo "<td>{$row['username']}</td>";
                    echo "<td>{$row['name']}</td>";
                    echo "<td>{$row['email']}</td>";
                    echo "<td>{$row['contact']}</td>";
                    echo "<td>{$row['type']}</td>";
                    echo "<td>";
                    echo "<button class='action-btn edit-btn' data-username='{$row['username']}'>Edit</button>";
                    echo "<button class='action-btn delete-btn' data-username='{$row['username']}'>Delete</button>";
                    echo "</td>";
                    echo "</tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Modal -->
    <div id="editModal">
        <div id="editModalContent">
            <button id="closeModal">×</button>
            <h3>Edit Employee Account</h3>
            <form id="editAccountForm">
                <input type="hidden" id="editId" name="id">
                <div class="edit-form-row">
                    <label for="editUsername">Username:</label>
                    <input type="text" id="editUsername" name="username" readonly>
                </div>
                <div class="edit-form-row">
                    <label for="editName">Name:</label>
                    <input type="text" id="editName" name="name" required>
                </div>
                <div class="edit-form-row">
                    <label for="editEmail">Email:</label>
                    <input type="email" id="editEmail" name="email" required>
                </div>
                <div class="edit-form-row">
                    <label for="editContact">Contact:</label>
                    <input type="text" id="editContact" name="contact" required>
                </div>
                <div class="edit-form-row">
                    <label for="editType">Account Type:</label>
                    <input type="text" id="editType" name="type" value="Employee" readonly>
                </div>
                <div class="edit-form-row">
                    <label for="editPassword">Password:</label>
                    <input type="password" id="editPassword" name="password">
                </div>
                <button type="submit" id="saveChanges">Save Changes</button>
            </form>
        </div>
    </div>

    <script src="../js/common-functions.js"></script>
    <script src="../js/routing-admin.js"></script>
    <script>
        let allAccounts = <?php echo json_encode($allRows); ?>; // Store all employee accounts for filtering

        document.addEventListener("DOMContentLoaded", function () {
            renderAccountsTable(allAccounts);

            const searchInput = document.getElementById('searchInput');
            searchInput.addEventListener('input', function () {
                const searchTerm = this.value.toLowerCase();
                const filteredAccounts = allAccounts.filter(account => {
                    return (
                        (account.username && account.username.toLowerCase().includes(searchTerm)) ||
                        (account.name && account.name.toLowerCase().includes(searchTerm)) ||
                        (account.email && account.email.toLowerCase().includes(searchTerm)) ||
                        (account.contact && account.contact.toLowerCase().includes(searchTerm))
                    );
                });
                renderAccountsTable(filteredAccounts);
            });

            addButtonListeners();

            document.getElementById('closeModal').addEventListener('click', function () {
                document.getElementById('editModal').style.display = 'none';
            });

            window.addEventListener('click', function (e) {
                const editModal = document.getElementById('editModal');
                if (e.target === editModal) editModal.style.display = 'none';
            });
        });

        function renderAccountsTable(accounts) {
            const tableBody = document.getElementById("accountsTableBody");
            tableBody.innerHTML = "";

            accounts.forEach(account => {
                const row = `
                    <tr data-username="${account.username}">
                        <td>${account.username || ''}</td>
                        <td>${account.name || ''}</td>
                        <td>${account.email || ''}</td>
                        <td>${account.contact || ''}</td>
                        <td>${account.type || ''}</td>
                        <td>
                            <button class="action-btn edit-btn" data-username="${account.username}">Edit</button>
                            <button class="action-btn delete-btn" data-username="${account.username}">Delete</button>
                        </td>
                    </tr>
                `;
                tableBody.innerHTML += row;
            });

            addButtonListeners();
        }

        function addButtonListeners() {
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const username = this.getAttribute('data-username');
                    const account = allAccounts.find(a => a.username === username);
                    if (account) {
                        document.getElementById('editId').value = account.id;
                        document.getElementById('editUsername').value = account.username || '';
                        document.getElementById('editName').value = account.name || '';
                        document.getElementById('editEmail').value = account.email || '';
                        document.getElementById('editContact').value = account.contact || '';
                        document.getElementById('editType').value = 'Employee';
                        document.getElementById('editPassword').value = '';
                        document.getElementById('editModal').style.display = 'block';
                    }
                });
            });

            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const username = this.getAttribute('data-username');
                    if (confirm(`Are you sure you want to delete the account for ${username}?`)) {
                        fetch('delete_employee_account.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `username=${encodeURIComponent(username)}`
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                alert('Employee account deleted successfully!');
                                allAccounts = allAccounts.filter(a => a.username !== username);
                                renderAccountsTable(allAccounts);
                            } else {
                                alert('Error deleting account: ' + result.error);
                            }
                        })
                        .catch(error => console.error('Error deleting account:', error));
                    }
                });
            });
        }

        document.getElementById('editAccountForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const id = document.getElementById('editId').value;
            const username = document.getElementById('editUsername').value;
            const name = document.getElementById('editName').value;
            const email = document.getElementById('editEmail').value;
            const contact = document.getElementById('editContact').value;
            const type = 'Employee'; // Force type to remain Employee
            const password = document.getElementById('editPassword').value;

            const data = {
                id: id,
                username: username,
                name: name,
                email: email,
                contact: contact,
                type: type,
                password: password ? password : undefined
            };

            fetch('update_employee_account.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.json();
            })
            .then(result => {
                if (result.success) {
                    alert('Employee account updated successfully!');
                    document.getElementById('editModal').style.display = 'none';
                    const updatedAccountIndex = allAccounts.findIndex(a => a.id === id);
                    if (updatedAccountIndex !== -1) {
                        allAccounts[updatedAccountIndex] = {
                            ...allAccounts[updatedAccountIndex],
                            name: name,
                            email: email,
                            contact: contact,
                            type: type
                        };
                    }
                    renderAccountsTable(allAccounts);
                } else {
                    alert('Error updating account: ' + (result.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error updating account:', error);
                alert('An error occurred while updating the account: ' + error.message);
            });
        });
    </script>
</body>
</html>