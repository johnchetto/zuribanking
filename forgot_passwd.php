<?php
session_start();
require_once "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Match form input name
    $email = trim($_POST["user_email"]);

    if (empty($email)) {
        echo "<script>alert('Please enter your email address.'); window.history.back();</script>";
        exit;
    }

    // Ensure database connection is valid
    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    // Check if the user exists in the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if (!$stmt) {
        die("SQL prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Generate OTP and store it in session
        $otp = rand(100000, 999999);
        $_SESSION["reset_email"] = $email;
        $_SESSION["reset_otp"] = $otp;
        $_SESSION["otp_time"] = time();

        // In production: send OTP via email (for now show via alert)
        echo "<script>
            alert('Your password reset OTP is: $otp');
            window.location.href = 'reset_passwd.php';
        </script>";
        exit;
    } else {
        echo "<script>alert('No account found with that email!'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="description" content="Zuri Online  Banking Management System - Password Recovery Page" />
    <meta name="author" content="Macharia John Ndegwa" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="CSS_styling/forgot_passwd.css" />
     <script src="JAVASCRIPT_INTERACT/forgot_passwd.js" defer></script>
    <title>Forgot Password | Zuri Online Banking</title>
  </head>
  <body>
    <div class="forgot-container">
      <div class="forgot-image">
            <img src="img/UI_LOGIN-01.png" alt="Login Interface" />
      </div>

      <div class="forgot-form">
        <header>
          <h1>Forgot Password</h1>
          <p>Please enter your registered email address to reset your password.</p>
        </header>

        <main>
          <form action="forgot_passwd.php" method="POST" autocomplete="off">
            <div class="form-group">
              <label for="user_email">Email Address</label>
              <input
                type="text"
                id="user_email"
                name="user_email"
                placeholder="Enter your registered email address"
                required
              />
            </div>
            <button type="submit">Submit</button>
          </form>
        </main>

        <footer>
          <p><a href="login.php">Back to Login</a></p>
        </footer>
      </div>
    </div>
  </body>
</html>
