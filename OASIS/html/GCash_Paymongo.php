<?php
session_start();

// Initialize session variables if not set
if (!isset($_SESSION['step'])) {
    $_SESSION['step'] = 'confirm'; // Initial step is confirmation
    $_SESSION['redirect_url'] = "https://pm.link/org-d1nYQ3WZUxiyXRD7GHnXZEVc/test/fsAeP59"; // Paymongo link
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_step = $_GET['step'] ?? '';

    switch ($current_step) {
        case 'redirect':
            // Process redirection and clear session
            $url = $_SESSION['redirect_url'];
            unset($_SESSION['step']);
            unset($_SESSION['redirect_url']);
            header("Location: $url");
            exit;
            
    }
}

// Render the appropriate page based on the current step
switch ($_SESSION['step']) {
    case 'confirm':
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>GCash Paymongo Redirect</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                    background-color: #f0f0f0;
                }
                #container {
                    text-align: center;
                    background: white;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                }
                button {
                    padding: 10px 20px;
                    background-color: #007bff;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                }
                button:hover {
                    background-color: #0056b3;
                }
            </style>
        </head>
        <body>
            <div id="container">
                <h2>GCash Paymongo Redirect</h2>
                <p>You will be redirected to a GCash payment link powered by Paymongo. Proceed?</p>
                <form action="GCash_Paymongo.php?step=redirect" method="POST">
                    <button type="submit">Proceed</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        break;
}
?>