<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        if (password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true); // Security: regenerate session ID
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];

            // Successful login → redirect
            header('Location: Admin_dashboard.php');
            exit();
        } else {
            // Wrong password
            $_SESSION['error'] = "Invalid password!";
            header('Location: Admin_login.php');
            exit();
        }
    } else {
        // Admin not found
        $_SESSION['error'] = "Admin not found!";
        header('Location:Admin_login.php');
        exit();
    }
} else {
    // Invalid request method
    $_SESSION['error'] = "Invalid request method.";
    header('Location:Admin_login.php');
    exit();
}
?>
