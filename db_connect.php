<?php
$servername = "sql100.infinityfree.com";
$username = "if0_40527894";
$password = "j1QRqytZ4nhUm";
$dbname = "if0_40527894_XXX"; // updated database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
/* creation of connection */
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
