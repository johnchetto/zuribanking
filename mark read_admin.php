<?php
session_start();
require_once "../includes/db_connect.php";

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Mark admin notifications as read
if ($conn->query("UPDATE notifications SET is_read = 1 WHERE role_target IN ('admin','both')")) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

