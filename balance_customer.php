<?php
session_start();
require_once __DIR__ . '/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['email'];

// Fetch user info
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

<!-- ✅ LOAD THE NAVIGATION BAR FROM ONE FILE -->
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
function fetchBalance() {
    fetch('get_balance.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('current-balance').innerText = `KES ${data.balance.toFixed(2)}`;
        })
        .catch(err => console.error('Error fetching balance:', err));
}

setInterval(fetchBalance, 5000);
fetchBalance();
</script>

</body>
</html>
