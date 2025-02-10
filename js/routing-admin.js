const dashboardButton = document.getElementById('dashboard-button');
const studentAccountsButton = document.getElementById('student-accounts');
const oasisAccountsButton = document.getElementById('oasis-accounts');
const payorHistoryButton = document.getElementById('payor-history');

dashboardButton.onclick = function() {
    window.location.href = 'admin-home.html';
}

studentAccountsButton.onclick = function() {
    window.location.href = 'student-accounts.html';
}

oasisAccountsButton.onclick = function() {
    window.location.href = 'oasis-accounts.html';
}

payorHistoryButton.onclick = function() {
    window.location.href = 'payor-history.html';
}



const toStudent = document.getElementById('to-student');

toStudent.onclick = function(){
    window.location.href = 'student-home.html'
}

const toEmployee = document.getElementById('to-employee');

toEmployee.onclick = function(){
    window.location.href = 'employee-home.html'
}

