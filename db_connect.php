<?php
if ($_SERVER['SERVER_NAME'] == 'localhost') {
    // Local XAMPP
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "zuri_db"; // your local DB
} else {
    // InfinityFree live server
    $servername = "sql100.infinityfree.com";
    $username = "if0_40527894";
    $password = "j1QRqytZ4nhUm";
    $dbname = "if0_40527894_XXX"; // InfinityFree DB
}

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
