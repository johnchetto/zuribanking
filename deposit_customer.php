<?php
require_once 'db_connect.php';
require_once 'send_notification.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$customer_id = $_SESSION['user_id'];
$customer_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $account_number = trim($_POST['account_number']);
    $phone = trim($_POST['phone']);
    $amount = floatval($_POST['amount']);

    if (empty($account_number) || empty($phone) || $amount <= 0) {
        echo "<script>alert('Please fill all fields correctly.');</script>";
        exit;
    }

    $stmt = $conn->prepare("SELECT id, balance FROM users WHERE account_number = ?");
    $stmt->bind_param("s", $account_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>alert('Account not found.');</script>";
        exit;
    }

    $user = $result->fetch_assoc();
    $user_id = $user['id'];
    $new_balance = $user['balance'] + $amount;

    $update = $conn->prepare("UPDATE users SET balance = ? WHERE account_number = ?");
    $update->bind_param("ds", $new_balance, $account_number);

    if ($update->execute()) {

        $uuid = bin2hex(random_bytes(16));
        $reference = "TRX-" . strtoupper(bin2hex(random_bytes(8)));
        $description = "Deposit by $customer_name";
        $type = "credit";
        $status = "completed";
        $recipient_name = $customer_name;

        $txn = $conn->prepare("INSERT INTO transactions 
            (uuid, user_id, description, amount, type, recipient_account, sender_account, status, balance_after, reference, recipient_name, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $txn->bind_param(
            "sisdsssdsss",
            $uuid,
            $user_id,
            $description,
            $amount,
            $type,
            $account_number,
            $phone,
            $status,
            $new_balance,
            $reference,
            $recipient_name
        );

        $txn->execute();

        sendNotification(
            $conn,
            "New Deposit Made",
            "$customer_name deposited KES $amount into account $account_number.",
            'deposit',
            'admin',
            $customer_id
        );

        echo "<script>alert('Deposit successful!');</script>";
    } else {
        echo "<script>alert('Error updating balance.');</script>";
    }

    $update->close();
    $stmt->close();
    $txn->close();

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit - Zuri Bank</title>

    <!-- Combined CSS -->
    <link rel="stylesheet" href="CSS_styling/deposit.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<!-- TOP NAVIGATION -->
<header class="header">
    <h1 class="logo">Zuri Bank</h1>

    <nav class="nav-links">
        <a href="dashboard_customer.php">Dashboard</a>
        <a href="balance_customer.php">Balance</a>
        <a href="transfer_customer.php">Transfer</a>
        <a href="Transaction_customer.php">Transactions</a>
        <a href="profile_customer.php">Profile</a>
        <a href="customer_support.php">Support</a>
        <a href="deposit_customer.php"> Deposit</a>
        <a href="deposit_api.php">Sandbox Deposit</a>
        <a href="logout.php">Logout</a>
    </nav>

    <!-- HAMBURGER FOR MOBILE -->
    <button class="menu-toggle" onclick="openSidebar()">☰</button>
</header>

<!-- MOBILE SIDEBAR -->
<aside id="sidebar">
    <a href="javascript:void(0)" class="closebtn" onclick="closeSidebar()">× Close</a>

    <a href="dashboard_customer.php">Dashboard</a>
    <a href="balance_customer.php">Balance</a>
    <a href="transfer_customer.php">Transfer</a>
    <a href="Transaction_customer.php">Transactions</a>
    <a href="profile_customer.php">Profile</a>
    <a href="customer_support.php">Support</a>
    <a href="deposit_customer.php">Deposit</a>
    <a href="deposit_api.php">Sandbox Deposit</a>
    <a href="logout.php" class="logout-btn">Logout</a>
</aside>

<!-- MAIN CONTENT -->
<div class="deposit-container">
    <h1 class="deposit-title">Deposit Funds</h1>

    <form method="POST" action="deposit_customer.php" class="deposit-form">
        <div class="form-group">
            <label for="account_number">Account Number</label>
            <input type="text" id="account_number" name="account_number" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" required>
        </div>

        <div class="form-group">
            <label for="amount">Amount (KES)</label>
            <input type="number" id="amount" name="amount" required>
        </div>

        <button type="submit" class="deposit-btn">Deposit</button>
    </form>

    <p class="back-link"><a href="balance_customer.php">← Back to Balance</a></p>
</div>

<!-- FOOTER -->
<div class="footer-center">
    <p><strong>Zuri Bank</strong> © <?php echo date('Y'); ?> All Rights Reserved.</p>
</div>

<script>
function openSidebar() {
    document.getElementById("sidebar").style.left = "0";
}

function closeSidebar() {
    document.getElementById("sidebar").style.left = "-260px";
}
</script>

</body>
</html>
