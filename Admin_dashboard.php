<?php
// ------------------------
// 1️⃣ Session safe start
// ------------------------
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php'; // Your database connection

// ------------------------
// 2️⃣ Protect the dashboard
// ------------------------
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// ------------------------
// 3️⃣ Fetch summary statistics
// ------------------------

// Total Customers
$totalCustomersQuery = "SELECT COUNT(*) AS total FROM users WHERE user_type='customer'";
$totalCustomersResult = mysqli_query($conn, $totalCustomersQuery);
$totalCustomers = mysqli_fetch_assoc($totalCustomersResult)['total'] ?? 0;

// Pending Approvals
$pendingApprovalsQuery = "SELECT COUNT(*) AS pending FROM users WHERE user_type='customer' AND account_status='Pending'";
$pendingApprovalsResult = mysqli_query($conn, $pendingApprovalsQuery);
$pendingApprovals = mysqli_fetch_assoc($pendingApprovalsResult)['pending'] ?? 0;

// Total Transactions Today
$today = date('Y-m-d');
$totalTransactionsQuery = "SELECT COUNT(*) AS total FROM transactions WHERE DATE(date)='$today'";
$totalTransactionsResult = mysqli_query($conn, $totalTransactionsQuery);
$totalTransactions = mysqli_fetch_assoc($totalTransactionsResult)['total'] ?? 0;

// System Logs Count (optional, only if table exists)
$systemLogsQuery = "SELECT COUNT(*) AS total FROM system_logs";
$systemLogsResult = mysqli_query($conn, $systemLogsQuery);
$systemLogsCount = mysqli_fetch_assoc($systemLogsResult)['total'] ?? 0;

// ------------------------
// 4️⃣ Fetch recent customer logins (last 5) from users.last_login
// ------------------------
$recentLoginsQuery = "
    SELECT id AS user_id, first_name, last_name, last_login
    FROM users
    WHERE user_type='customer' AND last_login IS NOT NULL
    ORDER BY last_login DESC
    LIMIT 5
";
$recentLoginsResult = mysqli_query($conn, $recentLoginsQuery);

// ------------------------
// 5️⃣ Fetch last 5 transactions
// ------------------------
$recentTransactionsQuery = "
    SELECT id AS trx_id, user_id AS user_code, amount, status, date AS created_at
    FROM transactions
    ORDER BY date DESC
    LIMIT 5
";
$recentTransactionsResult = mysqli_query($conn, $recentTransactionsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Zuri Bank</title>
<style>
body { margin:0; font-family: "Segoe UI", Arial, sans-serif; background-color: #f7f9fb; color: #222; }
header { background-color: #003366; color: #fff; padding: 1.5rem; text-align: center; letter-spacing: 1px; }
nav { background-color: #004080; color: white; padding: 1rem; }
nav h2 { margin:0 0 1rem 0; text-align:center; font-size:1.5rem; }
nav ul { list-style: none; padding:0; display:flex; justify-content:center; flex-wrap:wrap; gap:1rem; }
nav ul li a { color:white; text-decoration:none; padding:0.5rem 1rem; background-color:#0059b3; border-radius:5px; transition:0.3s ease; }
nav ul li a:hover, nav ul li a.active { background-color:#0073e6; }
main { padding: 2rem; }
.admin-welcome { text-align: center; margin-bottom: 2rem; }
.summary-cards { display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1.5rem; margin-bottom:3rem; }
.stat-card { background-color:#ffffff; padding:1.5rem; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.1); text-align:center; }
.stat-card h4 { color:#003366; }
.stat-value { font-size:1.8rem; font-weight:bold; color:#0073e6; }
.recent-activities { background-color:#fff; padding:1.5rem; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
table { width:100%; border-collapse:collapse; text-align:center; margin-bottom:2rem; }
th, td { padding:0.8rem; border-bottom:1px solid #ddd; }
th { background-color:#f0f4f8; color:#003366; }
tr:hover { background-color:#eef6ff; }
footer { background-color:#003366; color:#fff; text-align:center; padding:1rem; position:relative; bottom:0; width:100%; margin-top:3rem; }

/* =========================
   RESPONSIVE DESIGN
========================= */
@media (max-width: 768px) {

  nav ul {
    flex-direction: column;
    gap: 0.5rem;
  }

  main {
    padding: 1rem;
  }

  .summary-cards {
    grid-template-columns: 1fr; /* Stack stats vertically */
  }

  table, th, td {
    font-size: 0.85rem;
  }

  .recent-activities {
    padding: 1rem;
  }
}
</style>

</head>
<body>

<header>
<h1>Zuri Online Banking Management System - Admin Dashboard</h1>
</header>

<nav aria-label="Main Banking Navigation">
<h2>Zuri Bank</h2>
<ul>
    <li><a href="Admin_dashboard.php">Dashboard</a></li>
    <li><a href="Approve_customer account.php">Account Approve</a></li>
    <li><a href="Admin_support.php">Support</a></li>
    <li><a href="Admin transaction_log.php" >Transaction Logs</a></li>
    <li><a href="Admin transaction_attempt.php">Transaction Attempts</a></li>
    <li><a href="Admin report_generate.php">Reports</a></li>
    <li><a href="logout.php">Logout</a></li>
  </ul>
<?php include('Admin_notification.php'); ?>
</nav>

<main>
<section class="admin-welcome">
<h2>Welcome, Admin!</h2>
<p>Access quick statistics and recent activities below.</p>
</section>

<section class="summary-cards">
<article class="stat-card">
    <h4>Total Customers</h4>
    <p class="stat-value"><?= $totalCustomers ?></p>
</article>
<article class="stat-card">
    <h4>Pending Approvals</h4>
    <p class="stat-value"><?= $pendingApprovals ?></p>
</article>
<article class="stat-card">
    <h4>Total Transactions (Today)</h4>
    <p class="stat-value"><?= $totalTransactions ?></p>
</article>
<article class="stat-card">
    <h4>System Logs Count</h4>
    <p class="stat-value"><?= $systemLogsCount ?></p>
</article>
</section>

<section class="recent-activities">
<h3>Recent Customer Logins</h3>
<table>
<thead>
<tr>
<th>User ID</th>
<th>Name</th>
<th>Last Login</th>
</tr>
</thead>
<tbody>
<?php
if ($recentLoginsResult && mysqli_num_rows($recentLoginsResult) > 0) {
    while ($login = mysqli_fetch_assoc($recentLoginsResult)) {
        $fullName = htmlspecialchars($login['first_name'] . ' ' . $login['last_name']);
        $lastLogin = date("M d, Y g:i A", strtotime($login['last_login']));
        echo "<tr>
                <td>{$login['user_id']}</td>
                <td>{$fullName}</td>
                <td>{$lastLogin}</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='3'>No recent logins found.</td></tr>";
}
?>
</tbody>
</table>

<h3>Last 5 Transactions</h3>
<table>
<thead>
<tr>
<th>ID</th>
<th>Amount</th>
<th>User</th>
<th>Status</th>
</tr>
</thead>
<tbody>
<?php
if ($recentTransactionsResult && mysqli_num_rows($recentTransactionsResult) > 0) {
    while ($trx = mysqli_fetch_assoc($recentTransactionsResult)) {
        echo "<tr>
                <td>{$trx['trx_id']}</td>
                <td>Ksh " . number_format($trx['amount'], 2) . "</td>
                <td>{$trx['user_code']}</td>
                <td>{$trx['status']}</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='4'>No recent transactions found.</td></tr>";
}
?>
</tbody>
</table>
</section>
</main>

<footer>
<p>&copy; 2025 Zuri Online Banking Management System | Admin Panel.</p>
</footer>

</body>
</html>
