<?php
require_once "db_connect.php"; // adjust path if needed

/**
 * Function to send a notification to a user, admin, or both
 * 
 * @param mysqli $conn - Database connection
 * @param int|null $user_id - The specific user to receive the notification (NULL if admin or both)
 * @param string $title - Short title of the notification
 * @param string $message - Full message text
 * @param string $type - Type of notification (e.g., 'transaction', 'alert', 'deposit')
 * @param string $role_target - 'customer', 'admin', or 'both'
 * @param string $priority - 'low', 'normal', or 'high'
 * @param string $sender - Who triggered the notification (default 'System')
 */
function sendNotification($conn, $title, $message, $type = 'general', $role_target = 'customer', $user_id = null, $priority = 'normal', $sender = 'System')
{
    $stmt = $conn->prepare("
        INSERT INTO notifications (user_id, sender, title, message, type, role_target, priority, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    if ($stmt === false) {
        die("Database prepare failed: " . htmlspecialchars($conn->error));
    }

    // Bind parameters safely
    $stmt->bind_param("issssss", $user_id, $sender, $title, $message, $type, $role_target, $priority);
    $stmt->execute();
    $stmt->close();
}
?>

