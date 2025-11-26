<?php
// deposit_sandbox.php
session_start();
require_once 'db_connect.php';

// ----------------------------------------------------
// 1. VALIDATE USER LOGIN
// ----------------------------------------------------
if (!isset($_SESSION['user_id'])) {
    die("Error: You must be logged in to deposit.");
}
$user_id = $_SESSION['user_id'];

// ----------------------------------------------------
// 2. SANDBOX CREDENTIALS
// ----------------------------------------------------
define('CONSUMER_KEY', 'Ma4QSXEpLlyNF8kZGkwmygrmKhmVKzA4iCABz7eM7nk9OjUQ');
define('CONSUMER_SECRET', 'GpwCejAuoGLeu5ao0QfVejtwhjZ0o2PQ3zeTfW7x8dZFZ47UyCmaRBvYwkw4yOPk');

$shortcode     = "600977"; // Your sandbox PayBill
$callback_url  = "https://teresia-uncorroboratory-georgiann.ngrok-free.dev/confirmation.php";

$message = "";

// ----------------------------------------------------
// 3. FETCH LOGGED-IN USER DETAILS
// ----------------------------------------------------
$stmt = $conn->prepare("SELECT phone_number, account_number, balance FROM users WHERE id=? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Error: User account not found.");
}

$user = $res->fetch_assoc();
$phone          = $user['phone_number'];
$account_number = $user['account_number'];
$balance        = $user['balance'];

// ----------------------------------------------------
// 4. FORMAT PHONE NUMBER TO MPESA FORMAT (2547XXXXXXXX)
// ----------------------------------------------------
if (preg_match('/^0\d{9}$/', $phone)) {
    $msisdn = '254' . substr($phone, 1);
} elseif (preg_match('/^254\d{9}$/', $phone)) {
    $msisdn = $phone;
} else {
    die("Error: Invalid phone number format ($phone)");
}

// ----------------------------------------------------
// 5. GENERATE ACCESS TOKEN
// ----------------------------------------------------
function generateAccessToken()
{
    $credentials = base64_encode(CONSUMER_KEY . ":" . CONSUMER_SECRET);

    $ch = curl_init("https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic $credentials"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        die("Token Generation Failed: " . curl_error($ch));
    }

    curl_close($ch);

    $data = json_decode($response, true);

    if (!isset($data['access_token'])) {
        die("Token Error: $response");
    }

    return $data['access_token'];
}

// ----------------------------------------------------
// 6. HANDLE DEPOSIT REQUEST
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $amount = floatval($_POST['amount'] ?? 0);

    if ($amount <= 0) {
        $message = "Please enter a valid amount.";
    } else {

        $token = generateAccessToken();

        // Payload
        $payload = [
            "ShortCode"      => $shortcode,
            "CommandID"      => "CustomerPayBillOnline",
            "Amount"         => intval($amount),
            "Msisdn"         => $msisdn,
            "BillRefNumber"  => $account_number,
            "CallBackURL"    => $callback_url
        ];

        // Make C2B request
        $ch = curl_init("https://sandbox.safaricom.co.ke/mpesa/c2b/v1/simulate");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $token",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $error   = curl_error($ch);
        curl_close($ch);

        // Log raw response
        file_put_contents("mpesa_response.log", date("Y-m-d H:i:s") . " - $response" . PHP_EOL, FILE_APPEND);

        if ($error) {
            $message = "MPESA Request Error: $error";
        } else {

            $resp = json_decode($response, true);

            // If Request Accepted
            if (isset($resp['ResponseDescription'])) {

                if (stripos($resp['ResponseDescription'], "success") !== false ||
                    stripos($resp['ResponseDescription'], "accepted") !== false) {

                    // Successful → Update DB
                    $new_balance = $balance + $amount;

                    // Update User Balance
                    $stmt = $conn->prepare("UPDATE users SET balance=? WHERE id=?");
                    $stmt->bind_param("di", $new_balance, $user_id);
                    $stmt->execute();

                    // Log transaction with recipient and sender filled
                    $uuid = bin2hex(random_bytes(16));
                    $reference = $resp['ConversationID'] ?? "TXN-" . $uuid;
                    $recipient_account = $account_number;
                    $sender_account = "Sandbox";

                    $stmt = $conn->prepare("
                        INSERT INTO transactions
                        (uuid, user_id, recipient_account, sender_account, description, amount, type, status, balance_after, reference, created_at)
                        VALUES (?, ?, ?, ?, 'Deposit via M-Pesa Sandbox', ?, 'credit', 'completed', ?, ?, NOW())
                    ");
                    $stmt->bind_param("sissdss", $uuid, $user_id, $recipient_account, $sender_account, $amount, $new_balance, $reference);
                    $stmt->execute();

                    $balance = $new_balance;
                    $message = "Deposit of KES $amount successful! New balance: KES $balance";

                } else {
                    $message = "Deposit Failed: " . ($resp['errorMessage'] ?? "Unknown Error");
                }

            } else {
                $message = "Unexpected Response: $response";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>ZURI Sandbox Deposit</title>

    <style>
        body { margin: 0; padding: 0; font-family: "Segoe UI", Arial, sans-serif; background: #f0f4f8; }

        .nav-links {
            background: #003366; padding: 12px 20px;
            display: flex; justify-content: center; gap: 25px;
            box-shadow: 0px 2px 5px rgba(0,0,0,0.2);
        }
        .nav-links a { color: white; text-decoration: none; font-weight: 500; padding: 8px 14px; border-radius: 4px; transition: 0.3s; }
        .nav-links a:hover { background: #0055a5; }

        .box { background: white; padding: 30px; border-radius: 12px; max-width: 480px; margin: 50px auto; box-shadow: 0px 3px 10px rgba(0,0,0,0.15); }
        h2 { text-align: center; color: #003366; }
        p { font-size: 16px; color: #333; }
        .msg { padding: 12px; background: #e8fbe8; border-left: 4px solid #27ae60; border-radius: 6px; margin-bottom: 20px; color: #106b21; }
        input, button { width: 100%; padding: 12px; font-size: 15px; border-radius: 6px; border: 1px solid #ccc; }
        button { background: #003366; color: white; border: none; cursor: pointer; margin-top: 15px; }
        button:hover { background: #0055a5; }
    </style>
</head>
<body>

<div class="nav-links">
    <a href="dashboard_customer.php">Dashboard</a>
    <a href="balance_customer.php">Balance</a>
    <a href="transfer_customer.php">Transfer</a>
    <a href="Transaction_customer.php">Transactions</a>
    <a href="profile_customer.php">Profile</a>
    <a href="customer_support.php">Support</a>
    <a href="deposit_customer.php">Deposit</a>
    <a href="deposit_api.php">Sandbox Deposit</a>
    <a href="logout.php">Logout</a>
</div>

<div class="box">
    <h2>ZURI Sandbox Deposit</h2>

    <p><b>Account:</b> <?= $account_number ?></p>
    <p><b>Current Balance:</b> KES <?= number_format($balance, 2) ?></p>

    <?php if ($message): ?>
        <div class="msg"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Amount (KES)</label>
        <input type="number" name="amount" min="1" required>
        <button>Simulate Deposit</button>
    </form>
</div>

</body>
</html>
