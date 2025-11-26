<?php
session_start();
require_once "db_connect.php"; // Database connection

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: Admin_login.php");
    exit();
}

// Handle admin response submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['complaint_id'], $_POST['admin_response'])) {
    $complaint_id = intval($_POST['complaint_id']);
    $admin_response = trim($_POST['admin_response']);

    if (!empty($admin_response)) {
        $stmt = $conn->prepare("
            UPDATE customer_support_new 
            SET admin_response = ?, status = 'answered', responded_at = NOW() 
            WHERE id = ?
        ");
        $stmt->bind_param("si", $admin_response, $complaint_id);
        $stmt->execute();
        $stmt->close();
        header("Location: Admin_support.php?response=success");
        exit();
    }
}

// Fetch all complaints
$query = "
    SELECT id, customer_id, first_name, last_name, email, subject, message, 
           admin_response, status, created_at, responded_at
    FROM customer_support_new 
    ORDER BY status ASC, created_at DESC
";
$result = $conn->query($query);
$complaints = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Customer Support</title>
<link rel="stylesheet" href="CSS_styling/side_bar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* General Body & Layout */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f6f8;
    color: #333;
    margin: 0;
    padding: 0;
}
header nav {
    background-color: #004080;
    color: #fff;
    padding: 1rem 2rem;
}
header nav h1 {
    margin: 0;
    font-size: 1.8rem;
    text-align: center;
}
header nav ul {
    list-style: none;
    padding: 0;
    margin: 0.5rem 0 0 0;
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    justify-content: center;
}
header nav ul li a {
    text-decoration: none;
    color: #fff;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    transition: background 0.3s;
}
header nav ul li a:hover,
header nav ul li a.active {
    background-color: #0066cc; 
}
/* Page Header */
.page-header {
    background-color: #e6f0ff;
    padding: 1rem 2rem;
    border-left: 5px solid #004080;
    margin-bottom: 1rem;
}
/* Complaint List */
.complaint-list {
    padding: 0 2rem;
}
.complaint-item {
    background-color: #fff;
    border-radius: 8px;
    padding: 1rem 1.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.complaint-item p {
    margin: 0.4rem 0;
}
.complaint-item hr {
    border: 0;
    border-top: 1px solid #ddd;
    margin: 1rem 0;
}
/* Admin Response Form */
.complaint-item .form-group {
    margin-bottom: 0.8rem;
}
.complaint-item textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ccc;
    border-radius: 5px;
    resize: vertical;
    font-family: inherit;
}
.complaint-item button {
    background-color: #004080;
    color: #fff;
    border: none;
    padding: 0.5rem 1.2rem;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}
.complaint-item button:hover {
    background-color: #0066cc;
}
/* Footer */
footer {
    background-color: #004080;
    color: #fff;
    text-align: center;
    padding: 1rem;
    margin-top: 2rem;
}
/* Responsive */
@media screen and (max-width: 768px) {
    header nav ul {
        flex-direction: column;
        align-items: center;
    }
    .page-header, .complaint-list {
        padding: 1rem;
    }
}
</style>
</head>
<body>
<header>
    <nav aria-label="Admin Navigation">
        <h1>Zuri Bank Admin Complaints</h1>
        <ul>
            <li><a href="Admin_dashboard.php">Dashboard</a></li>
            <li><a href="Approve_customer account.php">Account Approve</a></li>
            <li><a href="Admin_support.php">Customer Support</a></li>
            <li><a href="Admin Transaction_log.php">Transaction Logs</a></li>
            <li><a href="Admin transaction_attempt.php">Transaction Attempts</a></li>
            <li><a href="Admin report_generate.php">Reports</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<main>
    <section class="page-header">
        <h2>Customer Support & Complaints</h2>
        <p>View all customer complaints and respond directly.</p>
    </section>

    <section class="complaint-list">
        <?php if (empty($complaints)): ?>
            <p>No customer complaints yet.</p>
        <?php else: ?>
            <?php foreach ($complaints as $complaint): ?>
                <div class="complaint-item">
                    <p><strong>From:</strong> <?= htmlspecialchars($complaint['first_name'] . ' ' . $complaint['last_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($complaint['email']) ?></p>
                    <p><strong>Subject:</strong> <?= htmlspecialchars($complaint['subject']) ?></p>
                    <p><strong>Message:</strong> <?= nl2br(htmlspecialchars($complaint['message'])) ?></p>
                    <p><strong>Status:</strong> <?= ucfirst($complaint['status']) ?></p>
                    <p><strong>Submitted on:</strong> <?= htmlspecialchars($complaint['created_at']) ?></p>

                    <?php if ($complaint['status'] === 'answered'): ?>
                        <p><strong>Admin Response:</strong> <?= nl2br(htmlspecialchars($complaint['admin_response'])) ?></p>
                        <p><em>Responded on: <?= htmlspecialchars($complaint['responded_at'] ?? 'Not yet responded') ?></em></p>
                    <?php else: ?>
                        <!-- Admin reply form -->
                        <form method="POST" action="Admin_support.php">
                            <input type="hidden" name="complaint_id" value="<?= $complaint['id'] ?>">
                            <div class="form-group">
                                <label for="admin_response_<?= $complaint['id'] ?>">Reply to Customer</label>
                                <textarea name="admin_response" id="admin_response_<?= $complaint['id'] ?>" rows="4" placeholder="Write your response..." required></textarea>
                            </div>
                            <button type="submit">Send Response</button>
                        </form>
                    <?php endif; ?>
                    <hr>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<footer>
    <p>&copy; 2025 Zuri Online Banking Management System | Admin Support</p>
</footer>
</body>
</html>
