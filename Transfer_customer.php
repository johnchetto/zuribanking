<?php
require_once 'db_connect.php';
require_once 'send_notification.php';
require_once 'log_action.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['email'];

// Get sender info
$stmt = $conn->prepare("SELECT id, balance, account_number, first_name, last_name FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found.");
}

$user_id = $user['id'];
$current_balance = $user['balance'];
$sender_account = $user['account_number'];
$customer_name = $user['first_name'] . ' ' . $user['last_name'];

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $recipient_account = trim($_POST["recipient_account"]);
    $amount = floatval($_POST["amount"]);

    if ($amount <= 0) {
        $message = "<p class='error'>Invalid amount entered.</p>";
    } else {

        // Find recipient
        $stmt = $conn->prepare("SELECT id, balance, first_name, last_name, account_number FROM users WHERE account_number = ?");
        $stmt->bind_param("s", $recipient_account);
        $stmt->execute();
        $recipient = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$recipient) {
            $message = "<p class='error'>Recipient account not found.</p>";
        } elseif ($current_balance < $amount) {
            $message = "<p class='error'>Insufficient balance to complete this transfer.</p>";
        } else {

            $conn->begin_transaction();

            try {
                // Deduct from sender
                $new_sender_balance = $current_balance - $amount;
                $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
                $stmt->bind_param("di", $new_sender_balance, $user_id);
                $stmt->execute();

                // Add to recipient
                $new_recipient_balance = $recipient['balance'] + $amount;
                $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
                $stmt->bind_param("di", $new_recipient_balance, $recipient['id']);
                $stmt->execute();

                // Generate UUIDs + reference
                $uuid_sender = bin2hex(random_bytes(16));
                $uuid_recipient = bin2hex(random_bytes(16));
                $reference = "TRX-" . strtoupper(bin2hex(random_bytes(8)));

                // Insert sender transaction
                $stmt = $conn->prepare("INSERT INTO transactions 
                    (uuid, user_id, description, amount, type, recipient_account, sender_account, status, balance_after, reference, recipient_name, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

                $description_sender = "Transfer to " . $recipient['first_name'] . ' ' . $recipient['last_name'];
                $type_sender = "debit";
                $status = "completed";
                $recipient_name_sender = $recipient['first_name'] . ' ' . $recipient['last_name'];

                $stmt->bind_param(
                    "sisdsssdsss",
                    $uuid_sender,
                    $user_id,
                    $description_sender,
                    $amount,
                    $type_sender,
                    $recipient_account,
                    $sender_account,
                    $status,
                    $new_sender_balance,
                    $reference,
                    $recipient_name_sender
                );
                $stmt->execute();

                // Insert recipient transaction
                $stmt = $conn->prepare("INSERT INTO transactions 
                    (uuid, user_id, description, amount, type, recipient_account, sender_account, status, balance_after, reference, recipient_name, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

                $description_recipient = "Received from " . $customer_name;
                $type_recipient = "credit";
                $recipient_name_recipient = "From " . $customer_name;

                $stmt->bind_param(
                    "sisdsssdsss",
                    $uuid_recipient,
                    $recipient['id'],
                    $description_recipient,
                    $amount,
                    $type_recipient,
                    $recipient_account,
                    $sender_account,
                    $status,
                    $new_recipient_balance,
                    $reference,
                    $recipient_name_recipient
                );
                $stmt->execute();

                $conn->commit();

                // Update balance display
                $current_balance = $new_sender_balance;

                // Success message
                $message = "<p class='success'>Money transferred successfully! Ksh " .
                    number_format($amount, 2) . " sent to " . htmlspecialchars($recipient_name_sender) . ".</p>";

                // Log the action
                $log_desc = "Transferred KES $amount to " .
                    $recipient['first_name'] . ' ' . $recipient['last_name'] . " (Acc: $recipient_account)";
                logAction($conn, $user_id, 'customer', 'Transfer', $log_desc);

                // Admin notification
                $title = "Transaction Recorded";
                $n_msg = "$customer_name performed a transfer of KES $amount.";
                sendNotification($conn, $title, $n_msg, 'transaction', 'admin', $user_id);

            } catch (Exception $e) {
                $conn->rollback();
                $message = "<p class='error'>Transfer failed: " . $e->getMessage() . "</p>";
            }
        }
    }
}
?>

<?php include 'sidebar_nav.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Funds - Zuri Bank</title>

    <link rel="stylesheet" href="CSS_styling/Transfer.css">
    <link rel="stylesheet" href="CSS_styling/side_bar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<header>
    <nav>
        <h1>Zuri Bank</h1>
          <ul>
        <li><a href="dashboard_customer.php">Dashboard</a></li>
        <li><a href="balance_customer.php">Balance</a></li>
        <li><a href="Transfer_customer.php">Transfer</a></li>
        <li><a href="transaction_customer.php">Transactions</a></li>
        <li><a href="profile_customer.php">Profile</a></li>
        <li><a href="customer_support.php">Need Support</a></li>
        <li><a href="deposit_customer.php">Deposit</a></li>
        <li><a href="deposit_api.php">Sandbox Deposit</a></li>
        <li><a href="logout.php">Logout</a></li>
        <?php include('notification_component.php'); ?>
    </ul>
    </nav>
</header>

<main>
    <section class="page-header">
        <h2>Transfer Funds</h2>
        <p>Send money securely to another Zuri Bank account.</p>
    </section>

    <section class="transfer-form-section">
        <form method="POST" class="transfer-form">
            <div class="form-group">
                <label for="recipient_account">Recipient Account Number</label>
                <input type="text" id="recipient_account" name="recipient_account" required>
            </div>

            <div class="form-group">
                <label for="amount">Amount (Ksh)</label>
                <input type="number" id="amount" name="amount" required>
            </div>

            <button type="submit">Send Money</button>
        </form>

        <div class="transfer-feedback">
            <?= $message ?>
        </div>
    </section>

    <section class="account-balance">
        <h3>Available Balance: <span>Ksh <?= number_format($current_balance, 2) ?></span></h3>
    </section>
</main>

<footer>
    <p>&copy; 2025 Zuri Online Banking Management System</p>
</footer>
<script>
const menuToggle = document.getElementById('menu-toggle');
const navLinks = document.getElementById('nav-links');

menuToggle.addEventListener('click', () => {
    navLinks.classList.toggle('active');
});
</script>

</body>
</html>
