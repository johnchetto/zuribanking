<?php
session_start();
require_once __DIR__ . '/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['email'];

// Fetch user info from database
$stmt = $conn->prepare("SELECT first_name, last_name, account_number, balance FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $user = [
        'full_name' => $row['first_name'] . ' ' . $row['last_name'],
        'account_number' => $row['account_number'], // changed here
        'balance' => $row['balance']
    ];
} else {
    // Placeholder if no record found
    $user = [
        'full_name' => 'Test User',
        'account_number' => 'AC10000001', // updated placeholder
        'balance' => 0
    ];
}

$stmt->close();
?>


<!-- Include sidebar after fetching user -->
<?php include __DIR__ . '/sidebar_nav.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Account Balance Overview for Zuri Online Banking.">
<link rel="stylesheet" href="CSS_styling/balance.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<title>Account Balance - Zuri Bank</title>
</head>
<body>

<header>
                <nav aria-label="Main Dashboard Navigation">
                    <h1> </h1>
                    <!-- MOBILE MENU BUTTON -->
            <button class="mobile-menu-btn" aria-label="Open Menu">
                <i class="fa fa-bars"></i>
            </button>

            <!-- NAV MENU -->
            <ul class="sidebar-menu">
                <li><a href="dashboard_customer.php">Dashboard</a></li>
                <li><a href="balance_customer.php">Balance</a></li>
                <li><a href="transfer_customer.php">Transfer</a></li>
                <li><a href="Transaction_customer.php">Transactions</a></li>
                <li><a href="profile_customer.php">Profile</a></li>
                <li><a href="customer_support.php">Need Support</a></li>
                <li><a href="deposit_customer.php">Deposit</a></li>
                <li><a href="logout.php">Logout</a></li>
                <br>
                <?php include('notification_component.php'); ?>
            </ul>
    </nav>
</header>

<main>
    <section class="page-header">
        <h2>Account Balance Overview</h2>
        <p>View your current account details and available funds.</p>
    </section>

    <article class="balance-details-card" aria-labelledby="balance-summary-heading">
        <header>
            <h3 id="balance-summary-heading">Account Summary</h3>
            <p>Welcome, <strong id="customer-name"><?php echo htmlspecialchars($user['full_name']); ?></strong></p>
        </header>

        <section class="account-info">
            <dl>
                <dt>Account Number:</dt>
                <dd id="account-number"><?php echo htmlspecialchars($user['account_number']); ?></dd>

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

<?php $conn->close(); ?>
<script>
    // Function to fetch balance from the server
function fetchBalance() {
    // Make an AJAX request to get_balance.php
    fetch('get_balance.php')
        .then(response => response.json()) // Parse JSON response
        .then(data => {
            // Update the balance in the page
            document.getElementById('current-balance').innerText = `KES ${data.balance.toFixed(2)}`;
            // Update the last updated time
            document.getElementById('last-updated-time').innerText = data.last_updated;
        })
        .catch(err => console.error('Error fetching balance:', err)); // Handle errors
}

// Call fetchBalance automatically every 5 seconds
setInterval(fetchBalance, 5000);

// Fetch balance immediately when page loads
fetchBalance();

    const btn = document.querySelector(".mobile-menu-btn");
    const menu = document.querySelector(".sidebar-menu");

    btn.addEventListener("click", () => {
        menu.classList.toggle("open");
    });


</script>
</body>
</html>
