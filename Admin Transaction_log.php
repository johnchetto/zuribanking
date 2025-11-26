<?php
require_once 'db_connect.php';
session_start();

//  Optional: Admin Authentication Check
if (!isset($_SESSION['admin_id'])) {
    header("Location: Admin_login.php");
    exit();
}

// 🔹 Handle filters if submitted
$filter_query = "SELECT * FROM transactions WHERE 1=1";
if (!empty($_GET['type'])) {
    $type = $_GET['type'];
    $filter_query .= " AND type='$type'";
}
if (!empty($_GET['status'])) {
    $status = $_GET['status'];
    $filter_query .= " AND status='$status'";
}
if (!empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $filter_query .= " AND (description LIKE '%$search%' OR recipient_account LIKE '%$search%' 
                          OR sender_account LIKE '%$search%' OR reference LIKE '%$search%')";
}
$filter_query .= " ORDER BY date DESC";
$result = $conn->query($filter_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Transaction Logs - Zuri Bank</title>

<style>
/* ===== Base Reset ===== */
body {
  margin: 0;
  font-family: "Poppins", sans-serif;
  background: #f4f6f9;
  color: #333;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

/* ===== Navbar ===== */
nav {
  background-color: #0f3460;
  padding: 15px 0;
  position: fixed;
  width: 100%;
  top: 0;
  z-index: 1000;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
nav ul {
  list-style: none;
  display: flex;
  justify-content: center;
  gap: 25px;
  margin: 0;
  padding: 0;
}
nav a {
  color: #fff;
  text-decoration: none;
  padding: 8px 16px;
  border-radius: 6px;
  transition: background 0.3s ease;
}
nav a:hover:not(.no-hover),
nav a.active:not(.no-hover) {
  background-color: #1abc9c;
}

/* ===== Main Content ===== */
main {
  flex: 1;
  margin-top: 100px;
  padding: 20px 30px;
}
h1 {
  color: #0f3460;
  margin-bottom: 10px;
}

/* ===== Filter Form ===== */
.filter-box {
  background: #fff;
  padding: 15px;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  align-items: center;
  justify-content: space-between;
}
.filter-box select, .filter-box input {
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 6px;
}
.filter-box button {
  padding: 8px 16px;
  background: #1abc9c;
  color: #fff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}
.filter-box button:hover { background: #16a085; }

/* ===== Table ===== */
.table-container {
  margin-top: 25px;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
  overflow-x: auto;
}
table {
  width: 100%;
  border-collapse: collapse;
  min-width: 800px; /* Makes table scrollable on mobile */
}
th, td {
  padding: 12px 15px;
  text-align: left;
  border-bottom: 1px solid #eee;
}
th {
  background: #0f3460;
  color: #fff;
  position: sticky;
  top: 0;
  z-index: 2;
}
tr:hover { background: #f1f1f1; }

.status {
  padding: 5px 10px;
  border-radius: 6px;
  font-weight: 600;
  text-transform: capitalize;
}
.status.completed { background: #1abc9c; color: white; }
.status.pending { background: #f39c12; color: white; }
.status.failed { background: #e74c3c; color: white; }

/* ===== Footer ===== */
footer {
  background: #0f3460;
  color: white;
  text-align: center;
  padding: 10px 0;
  margin-top: auto;
}

/* ===== Responsive Fix ===== */
@media (max-width: 768px) {

  nav ul {
    flex-direction: column;
    gap: 10px;
  }

  main {
    padding: 100px 15px 20px;
  }

  .filter-box {
    flex-direction: column;
    align-items: stretch;
  }

  .filter-box input,
  .filter-box select,
  .filter-box button {
    width: 100%;
  }

  table {
    min-width: 600px;
  }
}

</style>
</head>
<body>

<!-- 🔹 Navbar -->
<nav>
  <ul>
    <li><a href="Admin_dashboard.php">Dashboard</a></li>
    <li><a href="Approve_customer account.php">Account Approve</a></li>
    <li><a href="Admin_support.php">Support</a></li>
    <li><a href="Admin transaction_log.php" >Transaction Logs</a></li>
    <li><a href="Admin transaction_attempt.php">Transaction Attempts</a></li>
    <li><a href="Admin report_generate.php">Reports</a></li>
    <li><a href="logout.php">Logout</a></li>
  </ul>
</nav>

<!-- 🔹 Main Content -->
<main>
  <h1>Transaction Logs</h1>
  <p>Monitor all customer transaction activities across the system.</p>

  <!--  Filter Section -->
  <form class="filter-box" method="GET">
    <input type="text" name="search" placeholder="Search by reference, account..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    <select name="type">
      <option value="">All Types</option>
      <option value="credit" <?= (($_GET['type'] ?? '') == 'credit') ? 'selected' : '' ?>>Credit</option>
      <option value="debit" <?= (($_GET['type'] ?? '') == 'debit') ? 'selected' : '' ?>>Debit</option>
    </select>
    <select name="status">
      <option value="">All Status</option>
      <option value="pending" <?= (($_GET['status'] ?? '') == 'pending') ? 'selected' : '' ?>>Pending</option>
      <option value="completed" <?= (($_GET['status'] ?? '') == 'completed') ? 'selected' : '' ?>>Completed</option>
      <option value="failed" <?= (($_GET['status'] ?? '') == 'failed') ? 'selected' : '' ?>>Failed</option>
    </select>
    <button type="submit">Apply Filters</button>
  </form>

  <!--  Table Section -->
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Date</th>
          <th>Description</th>
          <th>Amount (KSh)</th>
          <th>Type</th>
          <th>Status</th>
          <th>Sender</th>
          <th>Recipient</th>
          <th>Reference</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= $row['date'] ?></td>
              <td><?= htmlspecialchars($row['description']) ?></td>
              <td><?= number_format($row['amount'], 2) ?></td>
              <td><?= ucfirst($row['type']) ?></td>
              <td><span class="status <?= $row['status'] ?>"><?= $row['status'] ?></span></td>
              <td><?= htmlspecialchars($row['sender_account']) ?></td>
              <td><?= htmlspecialchars($row['recipient_account']) ?></td>
              <td><?= htmlspecialchars($row['reference']) ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="9" style="text-align:center;">No transactions found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

<!-- 🔹 Footer -->
<footer>
  &copy; <?= date('Y'); ?> Zuri Online Banking Management System — Admin Portal.
</footer>

<!-- ===== JavaScript (Search Highlight, Active Nav) ===== -->
<script>
document.addEventListener("DOMContentLoaded", () => {
  const activePage = window.location.pathname.split("/").pop();
  document.querySelectorAll("nav a").forEach(link => {
    if (link.href.includes(activePage)) {
      link.classList.add("active");
    }
  });
});
</script>

</body>
</html>
