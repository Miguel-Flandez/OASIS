<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/payor-history.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Dosis:wght@200..800&family=Fjalla+One&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oswald:wght@200..700&family=Playwrite+IN:wght@100..400&family=Poiret+One&family=Roboto+Slab:wght@100..900&family=Roboto:ital,wght@0,100..900;1,100..900&family=Smooch+Sans:wght@100..900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/de323b5912.js" crossorigin="anonymous"></script>
    <title>Payor History</title>
</head>
<body>
    <header>
        <div id="left">
        <img src="../assets/images/newlogo.png" alt="Oakwood" id="logo">
        </div>
        
        <h3 id="dashboard-button">Dashboard</h3>
        <h3 id="create-account">Create Account</h3>
        <h3 id="oasis-accounts">Oasis Accounts</h3>
        <h3 id="payor-history">Payor History</h3>

        <div id="right">
            <i class="fa-solid fa-bell"></i>
            <button id="logout">Logout  </button>
        </div>
        
    </header>

    <div id="accounts">

        <div id="search">
            <label for="search-student">Search Account: </label>
            <input type="text" id="search-student" name="search-student" placeholder="2021-12345">
        </div>

        <div id="payor-details">
            <h1>Payor Information</h1>

            <div id="payor-id">
                <p>Student Number: </p>
                <p id="id">2021-12345</p>
            </div>
            
            <div id="payor-name">
                <p>Payor Name: </p>
                <p id="name">Johnny Sins</p>
            </div>
            
        </div>
    

        <table id="student-accounts-table">
            <thead>
            <tr>
                <th>Receipt Number</th>
                <th>Date of Transaction</th>
                <th>Amount Paid</th>
                <th>Fee(s)</th>
                <th>Payment Status</th>
            </tr>
            </thead>
            <tbody>
            <div id="fee-input">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </div>
            </tbody>
        </table>
    </div>

    <div id="notif-modal" class="hide">
        <div id="notifs">
            <div id="notif">
                <h3>Reminder</h3>
                <p>Magbayad ka na please</p>
            </div>
            <div id="notif">
                <h3>Reminder</h3>
                <p>Magbayad ka na sabi e</p>
            </div>
            <div id="notif">
                <h3>Reminder</h3>
                <p>Alam namin bahay niyo</p>
            </div>
        </div>

    </div>

    <script src="../js/common-functions.js"></script>
    <script src="../js/routing-admin.js"></script>
</body>
</html>