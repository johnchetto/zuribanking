<?php
session_start();
require_once 'db_connect.php';

// ✅ Check login session
if (!isset($_SESSION['email'])) {
    echo "<script>alert('Please log in first.'); window.location.href='login.php';</script>";
    exit;
}

// ✅ Load user data from session
$first_name = $_SESSION['first_name'] ?? 'Customer';
$last_name  = $_SESSION['last_name'] ?? '';
$account_no = $_SESSION['account_number'] ?? 'N/A';
$user_id    = $_SESSION['user_id'] ?? null;
$phone      = $_SESSION['phone'] ?? '';

$balance = 0;
$tx_result = null;

if ($user_id) {
    // Fetch balance directly from users table
    $bal_stmt = $conn->prepare("SELECT balance FROM users WHERE id=? LIMIT 1");
    $bal_stmt->bind_param("i", $user_id);
    $bal_stmt->execute();
    $bal_result = $bal_stmt->get_result();
    $bal_row = $bal_result->fetch_assoc();
    $balance = $bal_row['balance'] ?? 0;

    // Fetch 5 most recent transactions
    $tx_stmt = $conn->prepare("
        SELECT date, description, type, amount, status, recipient_account, sender_account
        FROM transactions
        WHERE user_id = ?
        ORDER BY date DESC
        LIMIT 5
    ");
    $tx_stmt->bind_param("i", $user_id);
    $tx_stmt->execute();
    $tx_result = $tx_stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zuri Bank | Dashboard</title>
<link rel="stylesheet" href="dashboard_b.css">
<style>
/* GLOBAL FIXES */
* { box-sizing: border-box; }
html, body { overflow-x: hidden; width: 100%; max-width: 100%; }

/* BODY */
body {
    font-family: "Poppins", sans-serif;
    background-color: #f7f8fc;
    margin: 0;
    padding: 0;
    color: #333;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    touch-action: manipulation; /* improves mobile taps */
}

/* TOP NAVBAR */
.topnav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background-color: #0f3460;
    color: #fff;
    padding: 15px 25px;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1001;
}
.nav-left { display: flex; align-items: center; gap: 10px; }
.topnav h2 { margin: 0; font-size: 22px; }
.nav-links { display: flex; justify-content: center; flex-grow: 1; gap: 25px; }
.nav-links a { color: #fff; text-decoration: none; font-weight: 500; transition: 0.3s; }
.nav-links a:hover { color: #1abc9c; }
.menu-toggle { font-size: 26px; cursor: pointer; color: #fff; border: none; background: none; display: none; }

/* SIDEBAR */
.sidebar {
    height: 100%;
    width: 0;
    position: fixed;
    top: 0;
    left: 0;
    background-color: #0f3460;
    overflow-x: hidden;
    transition: 0.4s;
    padding-top: 60px;
    z-index: 1002;
}
.sidebar a {
    display: block;
    width: 100%;
    padding: 15px 25px;
    text-decoration: none;
    color: #ddd;
    font-size: 18px;
    border-radius: 6px;
    transition: 0.3s;
}
.sidebar a:hover { background-color: #16213e; color: #fff; }
.sidebar .close-btn { position: absolute; top: 15px; right: 25px; font-size: 30px; color: #fff; cursor: pointer; }

/* MAIN CONTENT */
.main { margin-top: 90px; padding: 30px; flex-grow: 1; transition: margin-left 0.4s; }
.card { background: #fff; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); padding: 20px; margin-bottom: 20px; }
.welcome { font-size: 24px; font-weight: 600; color: #0f3460; }
.balance-box { display: flex; justify-content: space-between; align-items: center; font-size: 18px; }
.balance { font-size: 28px; font-weight: bold; color: #1abc9c; }

/* TABLE */
table { width: 100%; border-collapse: collapse; background-color: #fff; border-radius: 8px; overflow: hidden; }
table th, table td { padding: 12px 15px; border-bottom: 1px solid #eee; text-align: left; }
table th { background-color: #0f3460; color: #fff; }
table tr:hover { background-color: #f1f1f1; }
.status.success { color: green; }
.status.failed { color: red; }
.status.pending { color: orange; }

/* FOOTER */
footer { background-color: #0f3460; color: #fff; text-align: center; padding: 12px; font-size: 14px; margin-top: auto; width: 100%; }

/* RESPONSIVE */
@media (max-width: 900px) { .nav-links { display: none; } .menu-toggle { display: block; } }
@media (max-width: 480px) {
    .topnav { padding: 12px 15px; }
    .main { padding: 10px; }
    .card { padding: 15px; }
    .welcome { font-size: 18px; }
    .balance { font-size: 22px; }
    .balance-box { flex-direction: column; gap: 5px; text-align: center; }
    table { display: block; overflow-x: auto; width: 100%; }
}
@media (min-width: 768px) and (max-width: 1366px) {
    body { font-size: 18px; }
    .topnav h2 { font-size: 26px; }
    .nav-links a { font-size: 18px; }
    .card { padding: 25px; }
    .welcome { font-size: 26px; }
    .balance { font-size: 30px; }
    table th, table td { font-size: 18px; padding: 14px 18px; }
}
</style>

<body>

<!-- Top Navbar -->
<div class="topnav">
    <div class="nav-left">
        <button class="menu-toggle" onclick="openSidebar()">☰</button>
        <h2>Zuri Bank</h2>
    </div>
    <div class="nav-links">
        <a href="dashboard_customer.php">Dashboard</a>
        <a href="balance_customer.php">Balance</a>
        <a href="transfer_customer.php">Transfer</a>
        <a href="Transaction_customer.php">Transactions</a>
        <a href="profile_customer.php">Profile</a>
        <a href="customer_support.php">Support</a>
        <a href="deposit_customer.php">Deposit</a>
        <a href="deposit_api.php">Sandbox Deposit</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<!-- Sidebar -->
<div id="sidebar" class="sidebar">
    <span class="close-btn" onclick="closeSidebar()">&times;</span>
    <a href="dashboard_customer.php">Dashboard</a>
    <a href="balance_customer.php">Balance</a>
    <a href="transfer_customer.php">Transfer</a>
    <a href="Transaction_customer.php">Transactions</a>
    <a href="profile_customer.php">Profile</a>
    <a href="customer_support.php">Support</a>
    <a href="deposit_customer.php">Deposit</a>
    <a href="deposit_api.php">Sandbox Deposit</a>
    <a href="logout.php">Logout</a>
</div>

<!-- Main Content -->
<div class="main">
    <div class="card">
        <p class="welcome" id="user-greeting">Welcome back, 👋 <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></p>
        <p>Account No: <strong id="account-num"><?php echo htmlspecialchars($account_no); ?></strong></p>
        <div class="balance-box">
            <span>Account Balance</span>
            <span class="balance" id="current-balance">KES <?php echo number_format($balance, 2); ?></span>
        </div>
    </div>

    <div class="card transactions">
        <h3>Recent Transactions</h3>
        <table id="history">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Amount (KES)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($tx_result && $tx_result->num_rows > 0): ?>
                    <?php while ($tx = $tx_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($tx['date']); ?></td>
                            <td><?php 
                                echo htmlspecialchars($tx['description']); 
                                if($tx['type'] == 'debit' && !empty($tx['recipient_account'])){
                                    echo " → " . htmlspecialchars($tx['recipient_account']);
                                }
                                if($tx['type'] == 'credit' && !empty($tx['sender_account'])){
                                    echo " ← " . htmlspecialchars($tx['sender_account']);
                                }
                            ?></td>
                            <td><?php echo ucfirst($tx['type']); ?></td>
                            <td><?php echo number_format($tx['amount'], 2); ?></td>
                            <td class="status <?php echo strtolower($tx['status']); ?>">
                                <?php echo ucfirst($tx['status']); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;">No recent transactions</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<footer>
    © <?php echo date('Y'); ?> Zuri Bank. All Rights Reserved.
</footer>

<script>
// Sidebar toggle
const sidebar = document.getElementById("sidebar");
const menuToggle = document.querySelector(".menu-toggle");
const closeBtn = document.querySelector(".sidebar .close-btn");
function openSidebar() { sidebar.style.width = "230px"; }
function closeSidebar() { sidebar.style.width = "0"; }
menuToggle.addEventListener("click", openSidebar);
closeBtn.addEventListener("click", closeSidebar);
window.addEventListener("click", (e) => {
    if (window.innerWidth <= 900 && sidebar.style.width === "230px") {
        if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) sidebar.style.width = "0";
    }
});

// Logout
const logoutLink = document.querySelector("a[href='logout.php']");
if (logoutLink) {
    logoutLink.addEventListener("click", (e) => {
        e.preventDefault();
        sessionStorage.clear();
        alert("You have been logged out successfully.");
        window.location.href = "login.php";
    });
}
</script>

</body>
</html>
