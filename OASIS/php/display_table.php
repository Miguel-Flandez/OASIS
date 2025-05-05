<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "password";
$dbname = "sys";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM accounts"; // Replace with your table name
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $cell) {
            echo "<td>" . htmlspecialchars($cell) . "</td>";
        }
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9'>No results found</td></tr>";
}

$conn->close();
?>
