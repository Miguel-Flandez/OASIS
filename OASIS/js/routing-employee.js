document.addEventListener("DOMContentLoaded", function () {
    const dashboardButton = document.getElementById("employee-dashboard-button");
    const oasisAccountsButton = document.getElementById("employee-oasis-accounts");
    const paymentCenterButton = document.getElementById("employee-payment-center");

    if (dashboardButton) {
        dashboardButton.onclick = function () {
            window.location.href = "employee-home.php";
        };
    }

    if (oasisAccountsButton) {
        oasisAccountsButton.onclick = function () {
            window.location.href = "employee-oasis-accounts.php";
        };
    }

    if (paymentCenterButton) {
        paymentCenterButton.onclick = function () {
            window.location.href = "employee-payment-center.php";
        };
    }
});