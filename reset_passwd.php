<?php
session_start();
require_once "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize and collect inputs
    $entered_otp = preg_replace('/\D/', '', trim($_POST["otp"]));
    $new_password = trim($_POST["new_password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    // Check session variables exist
    if (!isset($_SESSION["reset_email"]) || !isset($_SESSION["reset_otp"]) || !isset($_SESSION["otp_time"])) {
        echo "<script>alert('Session expired. Please request a new OTP.'); window.location.href='forgot_passwd.php';</script>";
        exit;
    }

    $email = $_SESSION["reset_email"];
    $stored_otp = $_SESSION["reset_otp"];
    $otp_time = $_SESSION["otp_time"];
    $current_time = time();

    // Check if OTP expired (10 minutes)
    if ($current_time - $otp_time > 600) {
        unset($_SESSION["reset_otp"], $_SESSION["otp_time"]);
        echo "<script>alert('OTP expired! Please request a new one.'); window.location.href='forgot_passwd.php';</script>";
        exit;
    }

    // Verify OTP
    if ((int)$entered_otp !== (int)$stored_otp) {
        echo "<script>alert('Invalid OTP. Please try again.');</script>";
    } elseif ($new_password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        // Hash password securely
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password in database
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        if (!$stmt) {
            echo "<script>alert('Database error: " . $conn->error . "');</script>";
            exit;
        }
        $stmt->bind_param("ss", $hashed_password, $email);

        if ($stmt->execute()) {
            // Clear reset session data
            unset($_SESSION["reset_otp"], $_SESSION["otp_time"], $_SESSION["reset_email"]);
            echo "<script>alert('Password reset successful. You can now log in.'); window.location.href='login.php';</script>";
            exit;
        } else {
            echo "<script>alert('Error updating password. Please try again later.');</script>";
        }

        $stmt->close();
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="description" content="Zuri Online Banking - Password Reset Page" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Password Reset | Zuri Online Banking</title>
    <link rel="stylesheet" href="CSS_styling/reset_passwd.css">
</head>
<body>
    <div class="reset-wrapper">
        <div class="reset-card">
            <div class="reset-right">
                <h2>Reset Your Password</h2>
                <p>Please enter the OTP sent to your registered email and set a new password.</p>

                <form action="reset_passwd.php" method="POST" autocomplete="off">
                    <label for="otp">Enter OTP</label>
                    <input type="text" id="otp" name="otp" placeholder="Enter OTP" required />

                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required />

                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required />

                    <button type="submit">Reset Password</button>
                </form>

                <a href="login.php" class="back-link">← Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
