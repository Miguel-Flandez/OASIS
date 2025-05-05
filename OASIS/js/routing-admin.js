// C:\xampp\htdocs\OASIS\OASIS\js\routing-admin.js

document.addEventListener('DOMContentLoaded', () => {
    const dashboardButton = document.getElementById('dashboard-button');
    const createAccountsButton = document.getElementById('create-account');
    const createEmployeeButton = document.getElementById('create-employee');
    const oasisAccountsButton = document.getElementById('oasis-accounts');
    const employeeAccountsButton = document.getElementById('employee-accounts');

    if (dashboardButton) {
        dashboardButton.onclick = function() {
            window.location.href = 'admin-home.php';
        };
    } else {
        console.warn('dashboardButton not found');
    }

    if (createAccountsButton) {
        createAccountsButton.onclick = function() {
            window.location.href = 'create-account.php';
        };
    } else {
        console.warn('createAccountsButton not found');
    }

    if (createEmployeeButton) {
        createEmployeeButton.onclick = function() {
            window.location.href = 'create-employee.php';
        };
    } else {
        console.warn('createEmployeeButton not found');
    }

    if (oasisAccountsButton) {
        oasisAccountsButton.onclick = function() {
            window.location.href = 'oasis-accounts.php';
        };
    } else {
        console.warn('oasisAccountsButton not found');
    }

    if (employeeAccountsButton) {
        employeeAccountsButton.onclick = function() {
            window.location.href = 'employee-accounts.php';
        };
    } else {
        console.warn('employeeAccountsButton not found');
    }
});