

const employeeDashboardButton = document.getElementById('employee-dashboard-button');
const employeeStudentAccountsButton = document.getElementById('employee-student-accounts');
const employeeOasisAccountsButton = document.getElementById('employee-oasis-accounts');
const employeePaymentCenterButton = document.getElementById('employee-payment-center');
const employeePayorHistoryButton = document.getElementById('employee-payor-history');


employeeDashboardButton.onclick = function(){
    window.location.href = 'employee-home.html';
}
employeeStudentAccountsButton.onclick = function(){
    window.location.href = 'employee-student-accounts.html';
}
employeeOasisAccountsButton.onclick = function(){
    window.location.href = 'employee-oasis-accounts.html';
}
employeePaymentCenterButton.onclick = function(){
    window.location.href = 'employee-payment-center.html';
}
employeePayorHistoryButton.onclick = function(){
    window.location.href = 'employee-payor-history.html';   
}


const toStudent = document.getElementById('to-student');

toStudent.onclick = function(){
    window.location.href = 'student-home.html'
}

const toAdmin = document.getElementById('to-admin');

toAdmin.onclick = function(){
    window.location.href = 'admin-home.html'
}