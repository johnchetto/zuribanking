<?php
session_start();
require_once "db_connect.php";

// Check if OTP session exists
if (
    !isset($_SESSION['otp']) ||
    !isset($_SESSION['otp_time']) ||
    !isset($_SESSION['pending_login_email'])
) {
    echo "<script>alert('Session expired. Please log in again.'); window.location.href='login.php';</script>";
    exit;
}

$otp_error = '';
$otp_value = $_SESSION['otp']; // For testing (remove later)

// ✅ Handle resend OTP
if (isset($_POST['resend_otp'])) {
    $_SESSION['otp'] = rand(100000, 999999);
    $_SESSION['otp_time'] = time();
    $otp_value = $_SESSION['otp'];
}

// ✅ Handle OTP verification
if (isset($_POST['verify_otp'])) {
    $entered_otp = trim($_POST['otp']);
    $current_time = time();

    // Check OTP expiry (10 minutes = 600 seconds)
    if ($current_time - $_SESSION['otp_time'] > 600) {
        session_unset();
        session_destroy();
        echo "<script>alert('OTP expired. Please log in again.'); window.location.href='login.php';</script>";
        exit;
    }

    if ($entered_otp === (string)$_SESSION['otp']) {
        $email = $_SESSION['pending_login_email'];
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['account_no'] = $user['account_no'];
            $_SESSION['balance'] = $user['balance'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];

            unset($_SESSION['otp'], $_SESSION['otp_time'], $_SESSION['pending_login_email']);

            header("Location: dashboard_customer.php");
            exit;
        } else {
            echo "<script>alert('User not found. Please log in again.'); window.location.href='login.php';</script>";
            exit;
        }
    } else {
        $otp_error = "Invalid OTP. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OTP Verification | Zuri Online Banking</title>
<link rel="stylesheet" href="CSS_styling/otp.css">               
</head>

<body>
<div class="otp-container">
  <h1>Welcome to Zuri Online Banking</h1>
  <p>Please enter the OTP sent to your registered email.</p>

  <!-- For testing only (remove in production) -->
  <div style="background:#eef; padding:5px; border-radius:5px; margin-bottom:10px;">
    <strong>Test OTP:</strong> <?php echo $otp_value; ?>
  </div>

  <form method="POST">
    <input type="text" name="otp" id="otpInput" maxlength="6" placeholder="Enter 6-digit OTP" required>
    <div class="error"><?php echo $otp_error; ?></div>
    <p id="timer"></p>
    <button type="submit" name="verify_otp" id="verifyBtn" disabled>Verify OTP</button>
    <button type="submit" name="resend_otp">Resend OTP</button>
  </form>

  <p style="margin-top:15px;"><a href="login.php">← Back to Login</a></p>
</div>

<footer>&copy; <?php echo date("Y"); ?> Zuri Online Banking System. All Rights Reserved.</footer>

<script>
// Focus input on load
document.getElementById("otpInput").focus();

// Allow only numbers
const otpInput = document.getElementById("otpInput");
const verifyBtn = document.getElementById("verifyBtn");

otpInput.addEventListener("input", function() {
  this.value = this.value.replace(/\D/g, '');
  verifyBtn.disabled = (this.value.length !== 6);
});

// Countdown timer (10 minutes)
let timeLeft = 600;
const timerDisplay = document.getElementById("timer");

function updateTimer() {
  const minutes = Math.floor(timeLeft / 60);
  const seconds = timeLeft % 60;
  timerDisplay.textContent = `OTP expires in ${minutes}:${seconds.toString().padStart(2, '0')}`;

  if (timeLeft > 0) {
    timeLeft--;
    setTimeout(updateTimer, 1000);
  } else {
    timerDisplay.textContent = "OTP expired! Please request a new one.";
    verifyBtn.disabled = true;
  }
}
updateTimer();
</script>
</body>
</html>
