<?php
session_start();
require_once "../includes/db_connect.php";

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['notifications'=>[], 'unread'=>0]);
    exit;
}

$result = $conn->query("SELECT * FROM notifications 
                        WHERE role_target IN ('admin','both') 
                        ORDER BY created_at DESC LIMIT 10");

$notifications = [];
while($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

$count_unread = $conn->query("SELECT COUNT(*) AS unread 
                              FROM notifications 
                              WHERE role_target IN ('admin','both') AND is_read = 0");
$unread = $count_unread->fetch_assoc()['unread'];

echo json_encode(['notifications'=>$notifications,'unread'=>$unread]);
