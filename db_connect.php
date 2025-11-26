<?php
if ($_SERVER['SERVER_NAME'] == 'localhost') {
    // Local XAMPP settings
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "bull_db";
} else {
    // InfinityFree LIVE settings
    $servername = "sql301.infinityfree.com"; // MySQL Hostname
    $username = "if0_40529650";              // MySQL Username
    $password = "vmExSN4mvii1"; // MySQL Password
    $dbname = "if0_40529650_bull";             // MySQL Database Name
}
/*  connection*/
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
