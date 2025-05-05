<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['account_type'] !== 'Admin') {
    header("Location: ../index.html");
    exit;
}

// Include database connection
require_once '../includes/db_connection.php';

// Fetch admin stats
function fetchAdminStats($conn) {
    $stats = [
        'outstanding_balance' => 0,
        'overdue_accounts' => 0,
        'total_students' => 0,
        'revenue' => 0
    ];

    $studentStmt = $conn->prepare("SELECT COUNT(*) as total FROM students");
    $studentStmt->execute();
    $stats['total_students'] = $studentStmt->get_result()->fetch_assoc()['total'] ?? 0;
    $studentStmt->close();

    $totalRevenueStmt = $conn->prepare("SELECT SUM(amountpaid) as total FROM history");
    $totalRevenueStmt->execute();
    $stats['revenue'] = $totalRevenueStmt->get_result()->fetch_assoc()['total'] ?? 0;
    $totalRevenueStmt->close();

    $overdueStmt = $conn->prepare("
        SELECT COUNT(*) as overdue FROM (
            SELECT duedate FROM tuition WHERE status = 'unpaid' AND duedate < CURDATE()
            UNION ALL
            SELECT duedate FROM misc WHERE status = 'unpaid' AND duedate < CURDATE()
        ) AS overdue_dates
    ");
    $overdueStmt->execute();
    $stats['overdue_accounts'] = $overdueStmt->get_result()->fetch_assoc()['overdue'] ?? 0;
    $overdueStmt->close();

    $outstandingStmt = $conn->prepare("
        SELECT SUM(amount) as total FROM (
            SELECT amount FROM tuition WHERE status = 'unpaid'
            UNION ALL
            SELECT amount FROM misc WHERE status = 'unpaid'
        ) AS unpaid
    ");
    $outstandingStmt->execute();
    $stats['outstanding_balance'] = $outstandingStmt->get_result()->fetch_assoc()['total'] ?? 0;
    $outstandingStmt->close();

    return $stats;
}

$stats = fetchAdminStats($conn);
$conn->close();

$admin_name = isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin Dashboard for OASIS - Manage payments, accounts, and more.">
    <title>Admin Dashboard - OASIS</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-gradient"></script>
    <script src="https://cdn.jsdelivr.net/npm/luxon@3/build/global/luxon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <link rel="stylesheet" href="../css/admin-home.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <header>
        <div id="left">
            <img src="../assets/images/oakwood.jpg" alt="Oakwood Logo" id="logo" onerror="this.src='https://via.placeholder.com/150?text=Logo';">
        </div>
        <h3 id="dashboard-button" class="active" tabindex="0" aria-current="page">Dashboard</h3>
        <h3 id="create-account" tabindex="0">Create Account</h3>
        <h3 id="create-employee" tabindex="0">Create Employee</h3>
        <h3 id="oasis-accounts" tabindex="0">OASIS Accounts</h3>
        <h3 id="employee-accounts" tabindex="0">Employee Accounts</h3>
        <div id="right">
            <button id="logout" onclick="if(confirm('Are you sure you want to log out?')) window.location.href='../logout.php';">Logout</button>
        </div>
    </header>

    <main class="p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Welcome, <?php echo $admin_name; ?>!</h1>
            <p class="text-gray-600">Here’s an overview of your dashboard.</p>
        </div>

        <section id="dashboard-top" aria-label="Dashboard Statistics">
            <div class="info-box one" role="region" aria-label="Outstanding Balance">
                <p>Outstanding Balance</p>
                <h2 id="outstanding-balances">₱<?php echo number_format($stats['outstanding_balance'], 2); ?></h2>
                <button class="overview-btn one" data-target="outstandingModal">Overview</button>
            </div>
            <div class="info-box two" role="region" aria-label="Overdue Accounts">
                <p>Overdue Fees</p>
                <h2 id="overdue-accounts"><?php echo $stats['overdue_accounts']; ?></h2>
                <button class="overview-btn two" data-target="overdueModal">Overview</button>
            </div>
            <div class="info-box three" role="region" aria-label="Total Enrolled Students">
                <p>Total Enrolled Students</p>
                <h2 id="total-students"><?php echo $stats['total_students']; ?></h2>
                <button class="overview-btn three" data-target="studentsModal">Overview</button>
            </div>
            <div class="info-box four" role="region" aria-label="Revenue">
                <p>Revenue</p>
                <h2 id="revenue">₱<?php echo number_format($stats['revenue'], 2); ?></h2>
                <button class="overview-btn four" data-target="revenueModal">Overview</button>
            </div>
        </section>

        <section id="graph-container" aria-label="Data Visualizations">
            <div class="chart-wrapper">
                <div class="chart-controls">
                    <button id="toggle-pie" class="chart-toggle active">Pie Chart</button>
                    <button id="toggle-bar" class="chart-toggle">Bar Chart</button>
                </div>
                <div id="distribution-no-data" style="display: none; text-align: center; color: blue;">No unpaid fees data available</div>
                <canvas id="paymentDistributionChart" aria-label="Payment Distribution Chart"></canvas>
            </div>
            <div class="chart-wrapper">
                <div id="trend-no-data" style="display: none; text-align: center; color: blue;">No payment trend data available</div>
                <canvas id="paymentTrendChart" aria-label="Payment Trend Over Time Chart"></canvas>
            </div>
        </section>

        <button id="export-excel" class="mt-2 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">Export to Excel</button>

        <!-- Outstanding Balance Modal -->
        <div id="outstandingModal" class="modal">
            <div class="modal-content">
                <button class="close-btn" onclick="document.getElementById('outstandingModal').style.display='none'">×</button>
                <h3>Outstanding Balance Overview</h3>
                <table class="overview-table">
                    <thead>
                        <tr>
                            <th>Student Number</th>
                            <th>Parent Name</th>
                            <th>Fee</th>
                            <th>Due Date</th>
                            <th>Amount (PHP)</th>
                        </tr>
                    </thead>
                    <tbody id="outstandingTableBody"></tbody>
                </table>
                <div id="outstanding-no-data" class="no-data" style="display: none;">No outstanding fees found.</div>
            </div>
        </div>

        <!-- Overdue Accounts Modal -->
        <div id="overdueModal" class="modal">
            <div class="modal-content">
                <button class="close-btn" onclick="document.getElementById('overdueModal').style.display='none'">×</button>
                <h3>Overdue Accounts Overview</h3>
                <table class="overview-table">
                    <thead>
                        <tr>
                            <th>Parent Account</th>
                            <th>Student Number</th>
                            <th>Fee</th>
                            <th>Due Date</th>
                            <th>Amount (PHP)</th>
                        </tr>
                    </thead>
                    <tbody id="overdueTableBody"></tbody>
                </table>
                <div id="overdue-no-data" class="no-data" style="display: none;">No overdue accounts found.</div>
            </div>
        </div>

        <!-- Total Enrolled Students Modal -->
        <div id="studentsModal" class="modal">
            <div class="modal-content">
                <button class="close-btn" onclick="document.getElementById('studentsModal').style.display='none'">×</button>
                <h3>Total Enrolled Students Overview</h3>
                <table class="overview-table">
                    <thead>
                        <tr>
                            <th>Student Number</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Level</th>
                        </tr>
                    </thead>
                    <tbody id="studentsTableBody"></tbody>
                </table>
                <div id="students-no-data" class="no-data" style="display: none;">No students found.</div>
            </div>
        </div>

        <!-- Revenue Modal -->
        <div id="revenueModal" class="modal">
            <div class="modal-content">
                <button class="close-btn" onclick="document.getElementById('revenueModal').style.display='none'">×</button>
                <h3>Revenue Overview</h3>
                <table class="overview-table">
                    <thead>
                        <tr>
                            <th>Receipt Number</th>
                            <th>Transaction Date</th>
                            <th>Amount Paid (PHP)</th>
                            <th>Fees</th>
                        </tr>
                    </thead>
                    <tbody id="revenueTableBody"></tbody>
                </table>
                <div id="revenue-no-data" class="no-data" style="display: none;">No revenue data found.</div>
            </div>
        </div>

        <button id="chat-toggle" aria-label="Open AI Chat">
            <span class="material-icons-outlined">chat</span>
        </button>

        <div id="chatbox-modal" class="chatbox hidden" role="dialog" aria-label="AI Chat Window">
            <div class="chat-header">
                <h4>AI Assistant</h4>
                <button id="chat-close" aria-label="Close Chat">
                    <span class="material-icons-outlined">close</span>
                </button>
            </div>
            <div class="chat-content"></div>
            <div class="chat-input">
                <input type="text" id="chat-message" placeholder="Ask about payments, students, etc..." aria-label="Type a message to the AI assistant" />
                <button id="send-message" aria-label="Send Message">
                    <span class="material-icons-outlined">send</span>
                </button>
            </div>
        </div>

        <div id="loading-overlay" class="hidden">
            <div class="spinner"></div>
        </div>
    </main>

    <script src="../js/common-functions.js"></script>
    <script src="../js/routing-admin.js"></script>
    <script src="../js/admin-dashboard.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Overview button functionality
            document.querySelectorAll('.overview-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const modalId = this.dataset.target;
                    const modal = document.getElementById(modalId);
                    let fetchUrl;
                    let tbodyId;
                    let noDataId;

                    // Determine which data to fetch based on modal
                    if (modalId === 'outstandingModal') {
                        fetchUrl = 'fetch_outstanding_balance.php';
                        tbodyId = 'outstandingTableBody';
                        noDataId = 'outstanding-no-data';
                    } else if (modalId === 'overdueModal') {
                        fetchUrl = 'fetch_overdue_accounts.php';
                        tbodyId = 'overdueTableBody';
                        noDataId = 'overdue-no-data';
                    } else if (modalId === 'studentsModal') {
                        fetchUrl = 'fetch_enrolled_students.php';
                        tbodyId = 'studentsTableBody';
                        noDataId = 'students-no-data';
                    } else if (modalId === 'revenueModal') {
                        fetchUrl = 'fetch_revenue.php';
                        tbodyId = 'revenueTableBody';
                        noDataId = 'revenue-no-data';
                    }

                    // Fetch and display data
                    fetch(fetchUrl)
                        .then(response => response.json())
                        .then(data => {
                            const tbody = document.getElementById(tbodyId);
                            const noDataDiv = document.getElementById(noDataId);
                            tbody.innerHTML = '';

                            if (data.length === 0) {
                                noDataDiv.style.display = 'block';
                            } else {
                                noDataDiv.style.display = 'none';
                                data.forEach(item => {
                                    let row = '<tr>';
                                    if (modalId === 'outstandingModal' || modalId === 'overdueModal') {
                                        row += `
                                            <td>${item.studentnumber}</td>
                                            <td>${modalId === 'overdueModal' ? item.parent_name : item.parent_name}</td>
                                            <td>${item.fee}</td>
                                            <td>${item.duedate}</td>
                                            <td>${parseFloat(item.amount).toFixed(2)}</td>
                                        `;
                                    } else if (modalId === 'studentsModal') {
                                        row += `
                                            <td>${item.studentnumber}</td>
                                            <td>${item.firstname}</td>
                                            <td>${item.lastname}</td>
                                            <td>${item.level}</td>
                                        `;
                                    } else if (modalId === 'revenueModal') {
                                        row += `
                                            <td>${item.receiptnumber}</td>
                                            <td>${item.transactiondate}</td>
                                            <td>${parseFloat(item.amountpaid).toFixed(2)}</td>
                                            <td>${item.fees}</td>
                                        `;
                                    }
                                    row += '</tr>';
                                    tbody.innerHTML += row;
                                });
                            }
                            modal.style.display = 'block';
                        })
                        .catch(error => {
                            console.error('Error fetching data:', error);
                            alert('Failed to load data. Please try again.');
                        });
                });
            });

            // Close modals when clicking outside
            window.addEventListener('click', function(event) {
                const modals = document.querySelectorAll('.modal');
                modals.forEach(modal => {
                    if (event.target === modal) {
                        modal.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>