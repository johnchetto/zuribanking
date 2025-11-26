<?php
session_start();
require_once 'db_connect.php';

// Function to generate a unique account number
function generateAccountNumber($conn) {
    do {
        $account_no = 'AC100' . rand(00000, 99999); // e.g., AC10012345
        // Check if it already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE account_number = ?");
        $stmt->bind_param("s", $account_no);
        $stmt->execute();
        $stmt->store_result();
    } while ($stmt->num_rows > 0); // Repeat if already exists
    $stmt->close();
    return $account_no;
}

// Initialize variables for messages
$errors = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone_number' => '',
    'password' => '',
    'confirm_password' => ''
];

// Initialize input values to keep them after submission
$input_values = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone_number' => ''
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Collect and sanitize inputs
    $input_values['first_name'] = trim($_POST["first_name"]);
    $input_values['last_name'] = trim($_POST["last_name"]);
    $input_values['email'] = strtolower(trim($_POST["email"]));
    $input_values['phone_number'] = preg_replace('/\D/', '', trim($_POST["phone_number"]));
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Combine names for notifications
    $new_user_name = $input_values['first_name'] . ' ' . $input_values['last_name'];

    // Validate fields individually
    if (empty($input_values['first_name'])) $errors['first_name'] = "First Name field is required.";
    if (empty($input_values['last_name'])) $errors['last_name'] = "Last Name field is required.";
    if (empty($input_values['email'])) {
        $errors['email'] = "Email field is required.";
    } elseif (!filter_var($input_values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format!";
    }
    if (empty($input_values['phone_number'])) $errors['phone_number'] = "Phone Number field is required.";
    if (empty($password)) {
        $errors['password'] = "Password field is required.";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters long.";
    }
    if ($password !== $confirm_password) $errors['confirm_password'] = "Passwords do not match!";

    // If no errors, proceed to DB insertion
    if (!array_filter($errors)) {

        // Use your db_connect.php connection ($conn)
        if ($conn->connect_error) {
            $_SESSION['error_message'] = "Database connection failed!";
        } else {
            // Check duplicate email
            $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check_email->bind_param("s", $input_values['email']);
            $check_email->execute();
            $check_email->store_result();
            if ($check_email->num_rows > 0) $errors['email'] = "Email is already registered!";

            // Check duplicate phone
            $check_phone = $conn->prepare("SELECT id FROM users WHERE phone_number = ?");
            $check_phone->bind_param("s", $input_values['phone_number']);
            $check_phone->execute();
            $check_phone->store_result();
            if ($check_phone->num_rows > 0) $errors['phone_number'] = "Phone Number is already registered!";

            // If still no errors, insert user
            if (!array_filter($errors)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $account_no = generateAccountNumber($conn); // generate unique account number

                $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone_number, password, account_number) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param(
                    "ssssss",
                    $input_values['first_name'],
                    $input_values['last_name'],
                    $input_values['email'],
                    $input_values['phone_number'],
                    $hashed_password,
                    $account_no
                );

                if ($stmt->execute()) {
                    // ✅ Store success message in session and redirect immediately
                    $_SESSION['success_message'] = "Account created successfully! Please log in.";
                    header("Location: login.php");
                    exit();
                } else {
                    $_SESSION['error_message'] = "Error while saving user. Please try again.";
                }
                $stmt->close();
            }

            $check_email->close();
            $check_phone->close();
        }

        // Optional: Notifications for admin
        $title = "New Customer Registration";
        $message = "A new customer ($new_user_name) has registered.";
        $role_target = "admin";
        $conn->query("INSERT INTO notifications (title, message, role_target) VALUES ('$title', '$message', '$role_target')");

        $conn->close();
    }
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="description" content="Zuri Online Banking Management System" />
<meta name="author" content="Macharia John Ndegwa" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="CSS_styling/signup.css"/>
<title>Sign Up | Zuri Online Banking Management System</title>

<style>
/* Message Styles */
.form-message {
    padding: 10px;
    margin-bottom: 15px;
    font-weight: bold;
    color: green;
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    border-radius: 5px;
}
/* Field error styles */
.error {
    color: red;
    font-size: 0.9em;
    margin-top: 3px;
    display: block;
}
</style>
</head>

<body>
<div class="signup-container">
    <div class="signup-image">
        <img src="img/UI_LOGIN-01.png" alt="Registration Interface" />
    </div>

    <div class="signup-form">
        <header>
            <h1>Account Registration</h1>
            <p>To sign up, kindly fill the form below.</p>

            <!-- Display error message from session -->
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="form-message" style="color:red;background-color:#f8d7da;border-color:#f5c6cb;">
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>
        </header>

        <form action="signup.php" method="POST" autocomplete="off">
            <div>
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo $input_values['first_name']; ?>" placeholder="Enter your first name" required />
                <span class="error"><?php echo $errors['first_name']; ?></span>
            </div>

            <div>
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo $input_values['last_name']; ?>" placeholder="Enter your last name" required />
                <span class="error"><?php echo $errors['last_name']; ?></span>
            </div>

            <div>
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo $input_values['email']; ?>" placeholder="Enter your email address" required />
                <span class="error"><?php echo $errors['email']; ?></span>
            </div>

            <div>
                <label for="phone_number">Phone Number</label>
                <input type="tel" id="phone_number" name="phone_number" value="<?php echo $input_values['phone_number']; ?>" placeholder="Enter your phone number" required />
                <span class="error"><?php echo $errors['phone_number']; ?></span>
            </div>

            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required />
                <span class="error"><?php echo $errors['password']; ?></span>
            </div>

            <div>
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required />
                <span class="error"><?php echo $errors['confirm_password']; ?></span>
            </div>
          
            <button type="submit">Register Account</button>
        </form>

        <footer>
            <p>Already have an account? <a href="login.php">Log In</a></p>
        </footer>
    </div>
</div>
</body>
</html>
