<?php
session_start();
require_once __DIR__ . "/db_connect.php";

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) exit();

// Update unread notifications to read
$sql = "UPDATE notifications 
        SET is_read = 1 
        WHERE (role_target='customer' OR role_target='both') 
          AND is_read = 0";

$stmt = $conn->prepare($sql);
$stmt->execute();
$stmt->close();
