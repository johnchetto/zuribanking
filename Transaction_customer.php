<?php
// ------------------------
//  Session safe start
// ------------------------
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "db_connect.php"; // Make sure path is correct

// ------------------------
//  Check if user is logged in
// ------------------------
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ------------------------
//  Handle delete request
// ------------------------
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $del_stmt = $conn->prepare("DELETE FROM transactions WHERE id=? AND user_id=?");
    $del_stmt->bind_param("ii", $delete_id, $user_id);
    $del_stmt->execute();
    $del_stmt->close();

    $_SESSION['flash_message'] = "Transaction deleted successfully.";
    header("Location: transaction_customer.php");
    exit;
}

// ------------------------
// Fetch transactions for logged-in user
// ------------------------
$stmt = $conn->prepare("SELECT * FROM transactions WHERE user_id=? ORDER BY date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$transactions = $result->fetch_all(MYSQLI_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link rel="stylesheet" href="CSS_styling/Transaction.css">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<title>Transaction History - Zuri Bank</title>
<style>
.flash-message { background-color: #1abc9c; color: white; padding: 12px 20px; border-radius: 6px; margin-bottom: 20px; font-weight: 500; text-align: center; animation: fadeout 4s forwards; }
@keyframes fadeout { 0% { opacity: 1; } 80% { opacity: 1; } 100% { opacity: 0; display: none; } }
.status-success { color: green; font-weight: 500; }
.status-pending { color: orange; font-weight: 500; }
.status-failed { color: red; font-weight: 500; }
.delete-btn { color: red; text-decoration: none; font-weight: 500; }
.delete-btn:hover { text-decoration: underline; }
.credit { color: green; font-weight: 500; }
.debit { color: red; font-weight: 500; }
</style>
</head>
<body>

<header>
<header class="main-nav">
    <nav aria-label="Main Dashboard Navigation">

        <!-- LEFT AREA: Hamburger + Logo -->
        <div class="nav-left">
            <button class="nav-toggle" id="navToggle" aria-label="Open navigation">
                <span class="hamburger"></span>
            </button>

            <h1 class="brand-title">Zuri Bank</h1>
        </div>

        <!-- DESKTOP LINKS -->
        <ul class="nav-links">
            <li><a href="dashboard_customer.php">Dashboard</a></li>
            <li><a href="balance_customer.php">Balance</a></li>
            <li><a href="transfer_customer.php">Transfer</a></li>
            <li><a href="transaction_customer.php" >Transactions</a></li>
            <li><a href="profile_customer.php">Profile</a></li>
            <li><a href="customer_support.php">Need Support</a></li>
            <li><a href="deposit_customer.php">Deposit</a></li>
            <li><a href="deposit_api.php">Sandbox Deposit</a></li>
            <li><a href="logout.php">Logout</a></li>
            <?php include('notification_component.php'); ?>
        </ul>
    </nav>
</header>

<!-- MOBILE DRAWER -->
<aside class="nav-drawer" id="navDrawer">

    <div class="drawer-header">
        <span class="drawer-brand">Zuri Bank</span>
        <button class="nav-close" id="navClose">&times;</button>
    </div>

    <nav class="drawer-nav">
        <ul>
            <li><a href="dashboard_customer.php">Dashboard</a></li>
            <li><a href="balance_customer.php">Balance</a></li>
            <li><a href="transfer_customer.php">Transfer</a></li>
            <li><a href="Transaction_customer.php">Transactions</a></li>
            <li><a href="profile_customer.php">Profile</a></li>
            <hr>
            <li><a href="customer_support.php">Need Support</a></li>
            <li><a href="deposit_customer.php">Deposit</a></li>
            <li><a href="deposit_api.php">Sandbox Deposit</a></li>
            <hr>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</aside>

<!-- BACKDROP -->
<div class="nav-overlay" id="navOverlay" style="display:none;"></div>

</header>

<main>
<section class="page-header">
    <h2>Transaction History</h2>
    <p>Review all past transactions on your account.</p>
</section>

<?php if (!empty($_SESSION['flash_message'])): ?>
<div class="flash-message">
    <?= htmlspecialchars($_SESSION['flash_message']); ?>
</div>
<?php unset($_SESSION['flash_message']); endif; ?>

<section class="transaction-data">
<h3>All Transactions</h3>
<div class="transaction-table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Reference</th>
                <th>Amount (Ksh)</th>
                <th>Type</th>
                <th>Status</th>
                <th>Balance After</th>
                <th>Description</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($transactions)): ?>
            <?php foreach ($transactions as $row): ?>
                <?php
                    $reference = !empty($row['reference']) ? $row['reference'] : "TXN-".$row['uuid'];
                    $description = !empty($row['description']) ? $row['description'] : '-';
                    $type_display = ($row['type'] === 'credit') ? 'Deposit' : (!empty($row['recipient_account']) ? 'Transfer' : 'Withdrawal');
                    $sign = ($row['type'] === 'credit') ? '+' : '-';
                    $status_class = $row['status'] === 'completed' ? 'status-success' : ($row['status'] === 'pending' ? 'status-pending' : 'status-failed');
                    $amount_class = $row['type'] === 'credit' ? 'credit' : 'debit';
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= date("M d, Y g:i A", strtotime($row['date'])) ?></td>
                    <td><?= htmlspecialchars($reference) ?></td>
                    <td class="<?= $amount_class ?>" style="text-align:right;"><?= $sign . number_format($row['amount'],2) ?></td>
                    <td><?= $type_display ?></td>
                    <td class="<?= $status_class ?>"><?= ucfirst($row['status']) ?></td>
                    <td><?= number_format($row['balance_after'],2) ?></td>
                    <td><?= htmlspecialchars($description) ?></td>
                    <td>
                        <a href="transaction_customer.php?delete_id=<?= $row['id'] ?>" 
                           onclick="return confirm('Are you sure you want to delete this transaction?');" 
                           class="delete-btn">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="9" style="text-align:center;">No transactions found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</section>
</main>

<footer>
<p>&copy; 2025 Zuri Online Banking Management System</p>    
</footer>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const drawer = document.getElementById("navDrawer");
    const toggle = document.getElementById("navToggle");
    const closeBtn = document.getElementById("navClose");
    const overlay = document.getElementById("navOverlay");

    function openDrawer() {
        drawer.classList.add("open");
        overlay.style.display = "block";
    }

    function closeDrawer() {
        drawer.classList.remove("open");
        overlay.style.display = "none";
    }

    toggle.addEventListener("click", openDrawer);
    closeBtn.addEventListener("click", closeDrawer);
});
</script>
</body>
</html>
