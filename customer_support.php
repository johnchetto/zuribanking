<?php
session_start();
require_once "db_connect.php"; // Database connection

// Get logged-in customer ID
$customer_id = $_SESSION['user_id'] ?? null;
if (!$customer_id) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = trim($_POST["first_name"] ?? "");
    $last_name  = trim($_POST["last_name"] ?? "");
    $email      = trim($_POST["email"] ?? "");
    $subject    = trim($_POST["subject"] ?? "");
    $message    = trim($_POST["message"] ?? "");

    if (empty($first_name) || empty($last_name) || empty($email) || empty($subject) || empty($message)) {
        echo "<script>alert('All fields are required!');</script>";
    } else {
        // Updated table name
        $stmt = $conn->prepare("INSERT INTO customer_support_new 
            (customer_id, first_name, last_name, email, subject, message, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isssss", $customer_id, $first_name, $last_name, $email, $subject, $message);
        if ($stmt->execute()) {
            echo "<script>alert('Your message has been sent successfully! Our support team will reach out soon.');</script>";
        } else {
            echo "<script>alert('Error submitting your request. Please try again later.');</script>";
        }
        $stmt->close();
    }
}

// Fetch all complaints of this customer with admin responses
$stmt = $conn->prepare("SELECT subject, message, admin_response, status, created_at, responded_at 
                        FROM customer_support_new 
                        WHERE customer_id = ? 
                        ORDER BY created_at DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$complaints = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<?php include 'sidebar_nav.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Support - Zuri Bank</title>
<link rel="stylesheet" href="CSS_styling/customer_support.css">
<link rel="stylesheet" href="CSS_styling/side_bar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
         

<header>
    <nav aria-label="Customer Navigation">
        <h1>Zuri Bank - Customer Portal</h1>
        <ul>
            <li><a href="dashboard_customer.php">Dashboard</a></li>
            <li><a href="balance_customer.php">Balance</a></li>
            <li><a href="transfer_customer.php">Transfer</a></li>
            <li><a href="Transaction_customer.php">Transactions</a></li>
            <li><a href="profile_customer.php">Profile</a></li>
            <li><a href="customer_support.php" class="active">Need Support</a></li>
            <li><a href="deposit_customer.php">Deposit</a></li>
            <li><a href="deposit_api.php">sanbox deposit</a></li>
            <li><a href="logout.php">Logout</a></li>
     
        </ul>
    </nav>

</header>

<main>
    <!-- Page Header -->
    <section class="page-header">
        <h2>Customer Support & Help Center</h2>
        <p>Submit a complaint or view responses from our support team.</p>
    </section>

    <!-- Support Form -->
    <section class="support-form-container">
        <h3>Submit a New Complaint</h3>
        <form method="POST" action="">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" placeholder="Enter your first name" required>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" placeholder="Enter your last name" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your registered email" required>
            </div>

            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" placeholder="Enter your complaint" required>
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="6" placeholder="Describe your issue..." required></textarea>
            </div>

            <button type="submit">Submit Complaint</button>
        </form>
    </section>

    <!-- Customer Complaint List -->
    <section class="complaint-list">
        <h3>Your Complaints & Responses</h3>
        <?php if (count($complaints) === 0): ?>
            <p>No complaints submitted yet.</p>
        <?php else: ?>
            <?php foreach($complaints as $complaint): ?>
                <div class="complaint-item">
                    <p><strong>Subject:</strong> <?= htmlspecialchars($complaint['subject']) ?></p>
                    <p><strong>Message:</strong> <?= htmlspecialchars($complaint['message']) ?></p>
                    <?php if ($complaint['status'] === 'answered'): ?>
                        <p><strong>Admin Response:</strong> <?= htmlspecialchars($complaint['admin_response']) ?></p>
                        <p><em>Responded on: <?= $complaint['responded_at'] ?></em></p>
                    <?php else: ?>
                        <p><em>Status: Pending</em></p>
                    <?php endif; ?>
                    <hr>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <!-- Contact Info -->
    <section class="direct-contact">
        <h3>Other Ways to Reach Us </h3>
        <ul>
         <li><strong>Phone:</strong> <a href="tel:+254791575532">+254 791 575 532 (WhatsApp)</a></li>
       
        </ul>
    </section>
</main>

<footer>
    <p>&copy; 2025 Zuri Online Banking Management System | Customer Support</p>
</footer>
<script>
    // customer_support.js
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("supportForm");
  const responseText = document.getElementById("form-response");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const data = {
      fullname: form.fullname.value.trim(),
      email: form.email.value.trim(),
      subject: form.subject.value.trim(),
      message: form.message.value.trim(),
    };

    // Basic validation
    if (!data.fullname || !data.email || !data.subject || !data.message) {
      responseText.textContent = "Please fill in all fields.";
      responseText.style.color = "red";
      return;
    }

    try {
      const res = await fetch("/api/support", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
      });

      const result = await res.json();

      if (res.ok) {
        responseText.textContent = result.message;
        responseText.style.color = "green";
        form.reset();
      } else {
        responseText.textContent = result.message || "Submission failed. Try again.";
        responseText.style.color = "red";
      }
    } catch (err) {
      console.error("Error:", err);
      responseText.textContent = "An error occurred. Please try again later.";
      responseText.style.color = "red";
    }
  });
});

</script>
</body>
</html>
<?php
$conn->close();
?>
