<?php
session_start();
require_once 'log_action.php'; // include your logAction function
require_once 'db_connect.php';  // include your database connection

// Check if user is logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
    $user_id = $_SESSION['user_id'];
    $user_type = $_SESSION['user_type'];

    // Log the logout action
    logAction($conn, $user_id, $user_type, 'Logout', 'User logged out successfully');
}

// Destroy all session data
session_unset();  // Unset all session variables
session_destroy(); // Destroy the session

// Redirect to login page
header("Location: login.php");
exit();
?>
