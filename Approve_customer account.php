<?php
require_once 'db_connect.php';

// Handle Approve / Reject actions
if (isset($_POST['action']) && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        $status = 'Active';
    } elseif ($action === 'reject') {
        $status = 'Rejected';
    } else {
        $status = 'Pending';
    }

    $stmt = $conn->prepare("UPDATE users SET account_status=?, approved_by='Admin', approved_on=NOW() WHERE id=?");
    $stmt->bind_param("si", $status, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch all accounts
$result = $conn->query("SELECT id, first_name, last_name, email, account_number, account_type, date_created, account_status, approved_by, approved_on 
                        FROM users ORDER BY date_created ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>All Customer Accounts - Admin Panel</title>
<style>
/* ===== Reset & Global ===== */
/* Force desktop view on all devices */
body, html {
    min-width: 1200px !important;
    overflow-x: auto !important;
}

.table-wrapper {
    overflow-x: auto !important;
}

@media (max-width: 768px) {
    nav ul {
        flex-direction: row !important;
        gap: 20px !important;
    }

    .main {
        padding: 100px 30px !important;
    }

    table {
        min-width: 1200px !important;
    }
}

html, body {
    margin: 0;
    padding: 0;
    height: 100%;
    font-family: "Poppins", sans-serif;
    color: #333;
    background-color: #fff;
    transition: background-color 0.3s, color 0.3s;
}

body {
    display: flex;
    flex-direction: column;
}

a { text-decoration: none; color: inherit; }

/* ===== Navbar ===== */
nav {
    background-color: #0f3460;
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 1000;
    padding: 12px 25px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);

    display: flex;
    justify-content: center;
}

nav ul {
    list-style: none;
    display: flex;
    align-items: center;
    gap: 20px;
    margin: 0;
}

nav ul li a {
    color: #fff;
    padding: 8px 16px;
    border-radius: 6px;
    transition: 0.3s ease;
    font-weight: 500;
}

nav ul li a:hover,
nav ul li a.active {
    background-color: #1abc9c;
}

nav ul li a.logout-btn:hover {
    background-color: #c0392b;
}

/* Theme Toggle */
.theme-toggle {
    cursor: pointer;
    font-size: 18px;
    color: #fff;
}

/* ===== Main Content ===== */
.main {
    flex: 1;
    padding: 100px 30px 30px;
    width: 100%;
    max-width: 1500px;
    margin: 0 auto;
}

h1 {
    color: #0f3460;
    margin-bottom: 10px;
}

/* ===== Search Bar ===== */
.search-bar {
    margin: 15px 0;
    text-align: right;
}

.search-bar input {
    padding: 8px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    width: 250px;
}

/* ===== Table ===== */
.table-wrapper {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    margin-top: 10px;
    min-width: 900px;
}

th, td {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    text-align: left;
    font-size: 14px;
}

th {
    background-color: #0f3460;
    color: white;
}

/* Row colors */
tr.pending { background-color: #fff3cd; }
tr.active { background-color: #d4edda; }
tr.rejected { background-color: #f8d7da; }

tr:hover { filter: brightness(0.95); }

/* ===== Buttons ===== */
.btn {
    padding: 6px 12px;
    margin: 2px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: 0.3s;
}

.approve { background-color: #1abc9c; color: #fff; }
.approve:hover { background-color: #16a085; }

.reject { background-color: #e74c3c; color: #fff; }
.reject:hover { background-color: #c0392b; }

/* ===== Footer ===== */
footer {
    text-align: center;
    padding: 15px;
    background-color: #0f3460;
    color: #fff;
    font-size: 14px;
    margin-top: auto;
}

/* ===== Dark Mode ===== */
body.dark {
    background-color: #121212;
    color: #eee;
}

body.dark nav { background-color: #1e1e1e; }
body.dark table { background-color: #1e1e1e; }
body.dark th { background-color: #2e2e2e; }

body.dark tr.pending { background-color: #6c6c2a; }
body.dark tr.active { background-color: #2a6c2a; }
body.dark tr.rejected { background-color: #6c2a2a; }

/* ===== MOBILE FIXED — MEDIA QUERIES ===== */
@media (max-width: 768px) {

    nav {
        padding: 10px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    nav ul {
        flex-direction: column;
        gap: 8px;
    }

    .main {
        padding: 140px 15px 30px;
    }

    .search-bar {
        text-align: left;
    }

    .search-bar input {
        width: 100%;
    }

    table {
        min-width: 700px; /* Prevent squeezing */
    }
}


</style>

</head>
<body>

<!-- Navbar -->
<nav>
    <ul>
        <li><a href="Admin_dashboard.php">Dashboard</a></li>
        <li><a href="Approve_customer account.php">Account Approve</a></li>
        <li><a href="Admin_support.php">Support</a></li>
        <li><a href="Admin Transaction_log.php">Transaction Logs</a></li>
        <li><a href="Admin transaction_attempt.php">Transaction Attempts</a></li>
        <li><a href="Admin report_generate.php">Reports</a></li>
        <li><a href="logout.php" class="logout-btn">Logout</a></li>
    </ul>
    <div class="theme-toggle" id="theme-toggle" title="Toggle Light/Dark Mode"> </div>
</nav>

<div class="main">
    <h1>All Customer Accounts</h1>
    <p>Pending users can be approved or rejected. Admin can see the status of all users.</p>

    <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Search by name, email, or account type...">
    </div>

    <div class="table-wrapper">
        <table id="userTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Account Number</th>
                    <th>Account Type</th>
                    <th>Date Created</th>
                    <th>Status</th>
                    <th>Approved By</th>
                    <th>Approved On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <?php 
                            $status = $row['account_status'];
                            $isPending = $status === 'Pending';
                            $rowClass = strtolower($status);
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['account_number']) ?></td>
                            <td><?= htmlspecialchars($row['account_type']) ?></td>
                            <td><?= htmlspecialchars($row['date_created']) ?></td>
                            <td><?= htmlspecialchars($status) ?></td>
                            <td><?= htmlspecialchars($row['approved_by'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['approved_on'] ?? '-') ?></td>
                            <td>
                                <form method="POST" style="display:inline;" onsubmit="return confirmAction('approve');">
                                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="action" value="approve" class="btn approve" <?= !$isPending ? 'disabled' : '' ?>>Approve</button>
                                </form>
                                <form method="POST" style="display:inline;" onsubmit="return confirmAction('reject');">
                                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="action" value="reject" class="btn reject" <?= !$isPending ? 'disabled' : '' ?>>Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="10" style="text-align:center;">No customer accounts found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<footer>
    &copy; <?= date('Y'); ?> Zuri Bank. All rights reserved.
</footer>

<script>
// Highlight Active Page
const currentPage = window.location.pathname.split("/").pop();
document.querySelectorAll("nav ul li a").forEach(link => {
    if (link.getAttribute("href") === currentPage) {
        link.classList.add("active");
    }
});

// Live Search Filter
document.getElementById("searchInput").addEventListener("keyup", function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll("#userTable tbody tr");
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
    });
});

// Confirmation Popup
function confirmAction(action) {
    return confirm(`Are you sure you want to ${action} this account?`);
}

// Dark/Light Mode Toggle
const toggle = document.getElementById('theme-toggle');
const body = document.body;
if (localStorage.getItem('theme') === 'dark') {
    body.classList.add('dark');
    toggle.textContent = '';
}
toggle.addEventListener('click', () => {
    body.classList.toggle('dark');
    const theme = body.classList.contains('dark') ? 'dark' : 'light';
    localStorage.setItem('theme', theme);
    toggle.textContent = '';
});
</script>

</body>
</html>
