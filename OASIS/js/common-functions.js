// C:\xampp\htdocs\OASIS\OASIS\js\common-functions.js

document.addEventListener('DOMContentLoaded', () => {
    const toStudent = document.getElementById('to-student');
    const toEmployee = document.getElementById('to-employee');

    if (toStudent) {
        toStudent.onclick = function() {
            window.location.href = 'student-home.html';
        };
    }

    if (toEmployee) {
        toEmployee.onclick = function() {
            window.location.href = 'employee-home.html';
        };
    }
});