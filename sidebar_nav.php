<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    echo "<script>alert('Please log in first.'); window.location.href='login.php';</script>";
    exit;
}

$user_email = $_SESSION['email'];

// Fetch user details
$stmt = $conn->prepare("SELECT first_name, last_name, account_number, balance, id FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// Combine first and last name
$user = [
    'full_name' => $user_data['first_name'] . ' ' . $user_data['last_name'],
    'account_number' => $user_data['account_number'],
    'balance' => $user_data['balance'],
    'id' => $user_data['id']
];

// Store user ID in session
$_SESSION['user_id'] = $user['id'];
$stmt->close();
?>

<aside id="sidebar">
    <ul>
        <li><a href="dashboard_customer.php">Dashboard</a></li>
        <li class="dropdown">
            <a href="javascript:void(0)" class="dropbtn">Services <i class="fa fa-caret-down"></i></a>
            <ul class="dropdown-content">
                <li><a href="transfer_customer.php">Transfer</a></li>
                <li><a href="deposit_customer.php">Deposit</a></li>
                <li><a href="balance_customer.php">Balance</a></li>
            </ul>
        </li>
        <li><a href="profile_customer.php">Profile</a></li>
        <li><a href="customer_support.php">Support</a></li>
    </ul>

    <a href="logout.php" class="logout-btn">Logout</a>
</aside>

<!-- Sidebar toggle button -->
<button id="sidebarToggle"><i class="fa fa-bars"></i></button>

<script>
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('sidebarToggle');

toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('show');
});

// Dropdown toggle
document.querySelectorAll('.dropbtn').forEach(btn => {
    btn.addEventListener('click', () => {
        btn.parentElement.classList.toggle('active');
    });
});
</script>
