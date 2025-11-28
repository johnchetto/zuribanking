<?php
session_start();
require_once __DIR__ . '/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// Fetch user details
$stmt = $conn->prepare("SELECT first_name, last_name, account_number, balance FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $user = [
        'full_name' => $row['first_name'] . ' ' . $row['last_name'],
        'account_number' => $row['account_number'],
        'balance' => $row['balance']
    ];
} else {
    $user = [
        'full_name' => 'Test User',
        'account_number' => 'AC10000001',
        'balance' => 0
    ];
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Account Balance - Zuri Bank</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    /* ===== Global Styles ===== */
    body {
        margin: 0;
        padding: 0;
        font-family: 'Poppins', sans-serif;
        background: #F4F7FA;
    }

    /* ===== Top Navbar ===== */
    .top-nav {
        width: 100%;
        background: #0A2342;
        color: #fff;
        padding: 15px 30px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 999;
    }

    .top-nav .logo {
        font-size: 22px;
        font-weight: bold;
    }

    .top-nav ul {
        list-style: none;
        display: flex;
        gap: 25px;
        margin: 0;
    }

    .top-nav ul li a {
        color: white;
        text-decoration: none;
        font-size: 15px;
        transition: 0.3s;
    }

    .top-nav ul li a:hover {
        color: #4FC3F7;
    }

    /* Mobile Menu Button */
    .menu-btn {
        display: none;
        font-size: 25px;
        cursor: pointer;
    }

    /* Mobile Menu */
    @media(max-width: 768px) {
        .top-nav ul {
            display: none;
            flex-direction: column;
            background: #0A2342;
            width: 100%;
            padding: 20px 0;
            position: absolute;
            top: 60px;
            left: 0;
        }

        .top-nav ul.show {
            display: flex;
        }

        .menu-btn {
            display: block;
        }
    }

    /* ===== Page Content ===== */
    main {
        margin-top: 90px;
        padding: 20px;
    }

    .page-header h2 {
        color: #0A2342;
        margin-bottom: 5px;
    }

    .balance-details-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        max-width: 600px;
        margin: auto;
    }

    .balance-details-card header h3 {
        margin: 0 0 10px;
    }

    dl {
        margin: 0;
    }

    dt {
        font-weight: 600;
        margin-top: 10px;
    }

    dd {
        margin-left: 0;
        color: #2C3E50;
        font-size: 18px;
    }

    footer {
        text-align: center;
        margin-top: 40px;
        padding: 15px;
        color: #555;
    }
</style>
</head>

<body>

<!-- ===== TOP NAVBAR ===== -->
<nav class="top-nav">
    <span class="logo">Zuri Bank</span>

    <i class="fa fa-bars menu-btn" id="menuBtn"></i>

   <ul id="navLinks">
    <li><a href="dashboard_customer.php">Dashboard</a></li>
    <li><a href="balance_customer.php">Balance</a></li>
    <li><a href="transfer_customer.php">Transfer</a></li>
    <li><a href="Transaction_customer.php">Transactions</a></li>
    <li><a href="profile_customer.php">Profile</a></li>
    <li><a href="customer_support.php">Need Support</a></li>
    <li><a href="deposit_customer.php">Deposit</a></li>
    <li><a href="deposit_api.php">Sandbox Deposit</a></li>
    <li><a href="logout.php">Logout</a></li>
</ul>

</nav>

<!-- ===== MAIN CONTENT ===== -->
<main>
    <section class="page-header">
        <h2>Account Balance Overview</h2>
        <p>View your current account details and available funds.</p>
    </section>

    <article class="balance-details-card">
        <header>
            <h3>Account Summary</h3>
            <p>Welcome, <strong><?php echo htmlspecialchars($user['full_name']); ?></strong></p>
        </header>

        <section class="account-info">
            <dl>
                <dt>Account Number:</dt>
                <dd><?php echo htmlspecialchars($user['account_number']); ?></dd>

                <dt>Available Balance:</dt>
                <dd id="current-balance">KES <?php echo number_format($user['balance'], 2); ?></dd>

                <dt>Last Updated:</dt>
                <dd id="last-updated"><?php echo date("d M Y, h:i A"); ?></dd>
            </dl>
        </section>
    </article>
</main>

<footer>
    <p>&copy; 2025 Zuri Online Banking System</p>
</footer>

<script>
// ===== MOBILE MENU =====
const menuBtn = document.getElementById("menuBtn");
const navLinks = document.getElementById("navLinks");

menuBtn.addEventListener("click", () => {
    navLinks.classList.toggle("show");
});

// ===== AUTO-BALANCE REFRESH =====
function fetchBalance() {
    fetch('get_balance.php')
        .then(res => res.json())
        .then(data => {
            document.getElementById('current-balance').innerText =
                `KES ${parseFloat(data.balance).toLocaleString(undefined, {minimumFractionDigits: 2})}`;
        });
}

setInterval(fetchBalance, 5000);
fetchBalance();
</script>

</body>
</html>

