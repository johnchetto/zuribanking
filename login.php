<?php
//
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once "db_connect.php"; // Make sure this connects to your database
require_once 'log_action.php';

// Initialize error messages
$errors = ['email' => '', 'password' => ''];
$email_value = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email_value = trim($_POST['Email']);
    $password = $_POST['password'];

    // Basic validation
    if (empty($email_value)) $errors['email'] = "Email field is required.";
    if (empty($password)) $errors['password'] = "Password field is required.";

    // Only proceed if there are no errors
    if (!array_filter($errors)) {

        // 1️⃣ Check if user exists in the admins table first
        $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email_value);
        $stmt->execute();
        $result_admin = $stmt->get_result();

        if ($result_admin->num_rows === 1) {
            $admin = $result_admin->fetch_assoc();

            // Verify admin password
            if (password_verify($password, $admin['password_hash'])) {
                // ✅ Admin login successful - bypass OTP
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['email'] = $admin['email'];
                $_SESSION['role'] = $admin['role'];
                $_SESSION['first_name'] = $admin['first_name'];
                $_SESSION['last_name'] = $admin['last_name'];

                // ---------------------------
                // Log Admin Login
                // ---------------------------
                logAction($conn, $admin['admin_id'], 'admin', 'Login', 'Admin logged in successfully.');

                // Redirect to Admin Dashboard
                header("Location: Admin_dashboard.php");
                exit;
            } else {
                $errors['password'] = "Incorrect password!";
            }

        } else {
            // 2️⃣ Not an admin, check users table
            $stmt_user = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt_user->bind_param("s", $email_value);
            $stmt_user->execute();
            $result_user = $stmt_user->get_result();

            if ($result_user->num_rows === 1) {
                $user = $result_user->fetch_assoc();

                // Verify customer password
                if (password_verify($password, $user['password'])) {
                    // ✅ Customer login: generate OTP
                    $_SESSION['otp'] = rand(100000, 999999);
                    $_SESSION['otp_time'] = time();
                    $_SESSION['pending_login_email'] = $user['email'];
                    $_SESSION['pending_user_firstname'] = $user['first_name'];
                    $_SESSION['pending_user_id'] = $user['id']; // Needed for dashboard

                    // --- ADDITION: also set session for user_id for transactions ---
                    $_SESSION['user_id'] = $user['id']; // THIS LINE ENSURES transactions can be fetched
                    $_SESSION['email'] = $user['email']; 
                    $_SESSION['first_name'] = $user['first_name'];

                    // ---------------------------
                    // Log Customer Login
                    // ---------------------------
                    logAction($conn, $user['id'], 'customer', 'Login', 'User logged in successfully.');

                    // Redirect to OTP page
                    header("Location: otp.php");
                    exit;
                } else {
                    $errors['password'] = "Incorrect password!";
                }
            } else {
                $errors['email'] = "No account found with that email!";
            }

            $stmt_user->close();
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="CSS_styling/login.css">
<title>Zuri Online Banking Login</title>
<style>
/* Inline error styling */
.error { color: red; font-size: 0.9em; margin-top: 3px; display: block; }
</style>
</head>
<body>
<div class="login-container">
    <div class="login-image">
        <img src="img/UI_LOGIN-01.png" alt="Login Interface">
    </div>
    <div class="login-form">
        <header>
            <h1>Zuri Online Banking Login</h1>
            <p>Please fill in your details to log in.</p>
        </header>
        <main>
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="Email">Email</label>
                    <input type="text" id="Email" name="Email" value="<?php echo htmlspecialchars($email_value); ?>" placeholder="Enter your Email">
                    <span class="error"><?php echo $errors['email']; ?></span>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your Password">
                    <span class="error"><?php echo $errors['password']; ?></span>
                </div>
                <a href="forgot_passwd.php">Forgot Password?</a>
                <br><br>
                <button type="submit">Sign In</button>
            </form>
        </main>
        <footer>
            <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
        </footer>
    </div>
</div>
</body>
</html>
