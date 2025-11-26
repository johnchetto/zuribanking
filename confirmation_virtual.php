<?php
require_once 'db_connect.php';

header("Content-Type: application/json");

// Read JSON from Safaricom
$data = file_get_contents("php://input");
$payload = json_decode($data, true);

// Log incoming results (optional, for debugging)
file_put_contents("mpesa_log.txt", $data . PHP_EOL, FILE_APPEND);

// Extract details
$transID        = $payload['TransID'] ?? '';
$amount         = $payload['TransAmount'] ?? 0;
$msisdn         = $payload['MSISDN'] ?? '';
$billRef        = $payload['BillRefNumber'] ?? '';  // This is AC1001, AC1002 etc
$transTime      = $payload['TransTime'] ?? '';
$status         = "completed";

// 1️⃣ Find user by account number
$stmt = $conn->prepare("SELECT id, balance FROM users WHERE account_number = ?");
$stmt->bind_param("s", $billRef);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["ResultCode" => 1, "ResultDesc" => "User Not Found"]);
    exit;
}

$user = $result->fetch_assoc();
$user_id = $user['id'];
$new_balance = $user['balance'] + $amount;

// 2️⃣ Update user balance
$update = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
$update->bind_param("di", $new_balance, $user_id);
$update->execute();

// 3️⃣ Insert into transactions table
$insert = $conn->prepare("INSERT INTO transactions 
(user_id, description, amount, type, sender_account, recipient_account, status, balance_after, reference) 
VALUES (?, 'M-Pesa Deposit', ?, 'credit', ?, ?, 'completed', ?, ?)");

$insert->bind_param("idssdss",
    $user_id,
    $amount,
    $msisdn,
    $billRef,
    $new_balance,
    $transID
);

$insert->execute();

// 4️⃣ Response to Safaricom (VERY IMPORTANT)
echo json_encode([
    "ResultCode" => 0,
    "ResultDesc" => "Accepted"
]);

?>
