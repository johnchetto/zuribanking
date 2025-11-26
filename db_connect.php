<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bull_db"; // updated database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
