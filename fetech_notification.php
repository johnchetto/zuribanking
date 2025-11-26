<?php
session_start();
require_once __DIR__ . "/db_connect.php";

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) exit();

// Fetch latest 5 notifications for customer or both
$sql = "SELECT * FROM notifications 
        WHERE role_target='customer' OR role_target='both'
        ORDER BY created_at DESC
        LIMIT 5";
$result = $conn->query($sql);

// Count unread notifications
$count_sql = "SELECT COUNT(*) AS unread 
              FROM notifications 
              WHERE (role_target='customer' OR role_target='both') AND is_read=0";
$unread_result = $conn->query($count_sql);
$unread = $unread_result->fetch_assoc()['unread'];

// Prepare notifications array
$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

// Return JSON
echo json_encode([
    'unread' => $unread,
    'notifications' => $notifications
]);
