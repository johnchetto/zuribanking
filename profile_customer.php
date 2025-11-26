<?php
require_once 'db_connect.php';
session_start();

// Messages
$success_message = '';
$error_message = '';

// --- 1. Check login ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ********* FIXED: New image folder inside ZURI *********
$target_dir = "profile_pictures/";

// Ensure folder exists
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// --- 2. Fetch user info ---
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) die("User not found.");

$user['full_name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));

// --- 3. Handle profile update ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $phone = preg_replace('/[^0-9+]/', '', trim($_POST['phone']));
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $profile_image = $user['profile_image'] ?? '';

    if (!$email || !$phone) {
        $error_message = "Email and phone number cannot be empty or invalid.";
    } else {

        // --- Handle image upload ---
        if (isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] === UPLOAD_ERR_OK) {

            $file_tmp  = $_FILES["profile_image"]["tmp_name"];
            $extension = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
            $allowed = ["jpg","jpeg","png","gif"];

            if (!in_array($extension, $allowed)) {
                $error_message = "Invalid image type.";
            } else {

                $file_name = "user_" . $user_id . "." . $extension;
                $target_file = $target_dir . $file_name;

                if (move_uploaded_file($file_tmp, $target_file)) {

                    // Delete old image
                    if (!empty($user['profile_image']) && $user['profile_image'] !== $file_name) {
                        $old = $target_dir . $user['profile_image'];
                        if (file_exists($old)) @unlink($old);
                    }

                    $profile_image = $file_name;

                } else {
                    $error_message = "Failed to upload image.";
                }
            }
        }

        // --- Handle password ---
        $update_password = false;

        if ($new_password !== '') {
            if ($new_password !== $confirm_password) {
                $error_message = "New passwords do not match.";
            } else {

                $check = $conn->prepare("SELECT password FROM users WHERE id=?");
                $check->bind_param("i", $user_id);
                $check->execute();
                $row = $check->get_result()->fetch_assoc();
                $check->close();

                if (!$row || !password_verify($current_password, $row['password'])) {
                    $error_message = "Current password incorrect.";
                } else {
                    $update_password = true;
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                }
            }
        }

        // --- Update DB ---
        if ($error_message === '') {

            if ($update_password) {
                $update = $conn->prepare("UPDATE users 
                    SET email=?, phone_number=?, password=?, profile_image=? 
                    WHERE id=?");
                $update->bind_param("ssssi", $email, $phone, $hashed_password, $profile_image, $user_id);

            } else {
                $update = $conn->prepare("UPDATE users 
                    SET email=?, phone_number=?, profile_image=? 
                    WHERE id=?");
                $update->bind_param("sssi", $email, $phone, $profile_image, $user_id);
            }

            if ($update->execute()) {
                $success_message = "Profile updated successfully!";
            } else {
                $error_message = "Database update failed.";
            }
            $update->close();

            // Refresh user info
            $stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $user['full_name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        }
    }
}
?>


<?php include 'sidebar_nav.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Zuri Bank | Profile</title>
<link rel="stylesheet" href="CSS_styling/profile.css">
<link rel="stylesheet" href="CSS_styling/side_bar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header>
<nav>
<h1>Zuri Bank</h1>
<ul>
<li><a href="dashboard_customer.php">Dashboard</a></li>
<li><a href="balance_customer.php">Balance</a></li>
<li><a href="transfer_customer.php">Transfer</a></li>
<li><a href="Transaction_customer.php">Transactions</a></li>
<li><a href="profile_customer.php">Profile</a></li>
<li><a href="customer_support.php">Need Support</a></li>
<li><a href="deposit_customer.php">Deposit</a></li>
<li><a href="deposit_api.php">Sandbox Deposit</a></li>
<li><a href="logout.php">Logout</a></li>
<?php if(file_exists('notification_component.php')) include('notification_component.php'); ?>
</ul>
</nav>
</header>

<main>
<section class="profile-section">
<h2>User Profile</h2>

<?php if($success_message) echo "<p class='success'>{$success_message}</p>"; ?>
<?php if($error_message) echo "<p class='error'>{$error_message}</p>"; ?>

<div class="profile-container">
<div class="profile-image">
<?php
$img_src = !empty($user['profile_image']) && file_exists($user['profile_image'])
           ? $user['profile_image']
           : 'img/default_avatar.png';
?>
<img id="previewImage" src="<?php echo $img_src; ?>" alt="Profile Picture">
<span onclick="document.getElementById('profile_image').click();"><i class="fa fa-camera"></i></span>
<input type="file" name="profile_image" id="profile_image" accept="image/*" style="display:none;">
</div>

<div class="profile-info">
<h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
<p><strong>Account:</strong> <?php echo htmlspecialchars($user['account_number'] ?? 'N/A'); ?>
(<?php echo htmlspecialchars($user['account_type'] ?? 'Standard'); ?>)</p>
<p><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></p>
<p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone_number'] ?? 'N/A'); ?></p>
<p><strong>Joined:</strong> <?php echo htmlspecialchars($user['date_joined'] ?? 'N/A'); ?></p>
</div>
</div>

<hr>

<form action="" method="POST" enctype="multipart/form-data">
<h3>Update Profile</h3>
<label>Email:</label>
<input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
<label>Phone:</label>
<input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>" required>
<label>Change Profile Picture:</label>
<input type="file" name="profile_image" id="profile_image_form" accept="image/*">

<h4>Change Password (optional)</h4>
<input type="password" name="current_password" placeholder="Current Password">
<input type="password" name="new_password" placeholder="New Password">
<input type="password" name="confirm_password" placeholder="Confirm New Password">

<button type="submit">Save Changes</button>
</form>
</section>
</main>

<footer>
<p>&copy; 2025 Zuri Online Banking Management System</p>
</footer>

<script>
// Live preview for profile image without affecting database until form submit
document.getElementById('profile_image').addEventListener('change', function(e){
    const file = e.target.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = function(ev){
            document.getElementById('previewImage').src = ev.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>
