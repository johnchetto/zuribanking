<?php
require_once 'db_connect.php';
session_start();

// Admin authentication
if (!isset($_SESSION['admin_id'])) {
    header("Location: Admin_login.php");
    exit();
}

// Fetch all transactions with user info
$query = "
SELECT t.id, t.date, t.user_id, u.first_name, u.last_name, u.account_number, t.recipient_account, t.type, t.amount, t.status
FROM transactions t
LEFT JOIN users u ON t.user_id = u.id
ORDER BY t.date DESC
";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="Admin page for monitoring all real-time transaction attempts in Zuri Online Banking.">
<title>Transaction Attempts - Admin Panel</title>

<style>
:root {
  --primary-color: #1abc9c;
  --danger-color: #e74c3c;
  --warning-bg: #fff3cd;
  --warning-text: #856404;
  --success-color: #16a085;
  --text-color: #333;
  --bg-light: #f4f6f8;
  --white: #ffffff;
  --border-color: #ddd;
  --font-family: "Segoe UI", Arial, sans-serif;
}

body { font-family: var(--font-family); background: var(--bg-light); color: var(--text-color); margin: 0; padding: 0; }
header { background: var(--primary-color); color: var(--white); text-align: center; padding: 1.5em 0; font-size: 1.4em; letter-spacing: 0.5px; }
nav { background: #2c3e50; color: var(--white); display: flex; align-items: center; justify-content: center; padding: 1em 2em; }
nav h1 { font-size: 1.3em; margin: 0; color: var(--white); }
nav ul { list-style: none; display: flex; gap: 1em; margin: 0; padding: 0; }
nav ul li a { text-decoration: none; color: var(--white); background: transparent; padding: 0.6em 1em; border-radius: 6px; transition: background 0.3s ease; font-weight: 500; }
main { max-width: 1200px; margin: 2em auto; background: var(--white); border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 2em; }
.page-header { text-align: center; border-bottom: 2px solid var(--primary-color); padding-bottom: 1em; margin-bottom: 2em; }
.page-header h2 { color: var(--primary-color); }
.table-container { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; text-align: left; border-radius: 8px; overflow: hidden; }
th, td { padding: 12px 15px; border-bottom: 1px solid var(--border-color); }
th { background: var(--primary-color); color: var(--white); text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.9em; }
tr:hover { background: #f1fdfb; }
.status-suspicious { color: red; font-weight: bold; }
.status-failed { color: #d35400; font-weight: 600; }
.status-completed, .status-successful { color: var(--success-color); font-weight: 600; }
.btn-view { background: var(--primary-color); color: var(--white); border: none; padding: 6px 12px; border-radius: 5px; cursor: pointer; transition: 0.3s ease; }
.btn-view:hover { background: #16a085; }
footer { text-align: center; padding: 1em 0; background: #2c3e50; color: var(--white); font-size: 0.9em; margin-top: 2em; }
/* ============================
   MOBILE FIXES (IMPORTANT)
   ============================ */
@media (max-width: 768px) {

  /* Fix giant header */
  header {
    font-size: 1.2em;
    padding: 1em 0;
  }

  /* Make nav clean on mobile */
  nav {
    flex-direction: column;
    padding: 0.8em 1em;
  }

  nav ul {
    flex-direction: column;
    width: 100%;
    padding: 0;
    margin-top: 10px;
  }

  nav ul li {
    width: 100%;
    text-align: center;
  }

  nav ul li a {
    display: block;
    width: 100%;
    padding: 10px 0;
    background: #34495e;
    border-radius: 4px;
  }

  /* Fix main content spacing */
  main {
    margin: 1em;
    padding: 1em;
  }

  /* Improve table readability */
  th, td {
    font-size: 0.8em;
    padding: 10px;
    white-space: nowrap;
  }

  table {
    font-size: 0.85em;
  }
}

}
</style>
</head>
<body>

<header>
  <h1>Transaction Attempts</h1>
</header>

<nav aria-label="Main Banking Navigation">
  <h1>Zuri Bank</h1>
  <ul>
    <li><a href="Admin_dashboard.php">Dashboard</a></li>
    <li><a href="Approve_customer account.php">Account Approve</a></li>
    <li><a href="Admin_support.php">Support</a></li>
    <li><a href="Admin Transaction_log.php">Transaction Logs</a></li>
    <li><a href="Admin transaction_attempt.php">Transaction Attempts</a></li>
    <li><a href="Admin report_generate.php">Reports</a></li>
    <li><a href="logout.php" class="logout-btn">Logout</a></li>
  </ul>
</nav>

<main>
  <section class="page-header">
    <h2>Monitor Transaction Attempts</h2>
    <p>Real-time log of all customer transaction activities.</p>
  </section>

  <section class="transaction-log-table">
    <header><h3>Recent Transaction Attempts</h3></header>
    <article class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Date/Time</th>
            <th>Customer Name</th>
            <th>Account</th>
            <th>Recipient Account</th>
            <th>Type</th>
            <th>Amount (KSh)</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
              <tr class="status-<?= strtolower($row['status']) ?>">
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['date']) ?></td>
                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                <td><?= htmlspecialchars($row['account_number']) ?></td>
                <td><?= htmlspecialchars($row['recipient_account']) ?></td>
                <td><?= htmlspecialchars($row['type']) ?></td>
                <td><?= number_format($row['amount'], 2) ?></td>
                <td><?= ucfirst($row['status']) ?></td>
                <td><button class="btn-view">View</button></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="9" style="text-align:center;">No transaction attempts recorded.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </article>
  </section>
</main>

<footer>
  <p>&copy; <?= date('Y'); ?> Zuri Online Banking Management System | Admin Transaction Monitoring.</p>
</footer>

</body>
</html>
