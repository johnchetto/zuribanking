<?php
/**
 * log_action.php
 * Logs actions for admins and customers
 */

function logAction($conn, $user_id, $user_type, $action_type, $description) {
    // If user is not an admin, set admin_id to NULL
    $admin_id = ($user_type === 'admin') ? $user_id : NULL;

    // Prepare the statement
    $stmt = $conn->prepare("INSERT INTO system_logs (admin_id, user_type, action_type, description) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("isss", $admin_id, $user_type, $action_type, $description);

    // Execute
    if ($stmt->execute()) {
        return true;
    } else {
        // Log or display error
        echo "Logging failed: " . $stmt->error;
        return false;
    }

    $stmt->close();
}
