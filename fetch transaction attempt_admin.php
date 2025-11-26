<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([]);
    exit();
}

//  Use correct column names
$query = "SELECT id, date, description, amount, type, status, 
                 sender_account, recipient_account, recipient_name, reference
          FROM transactions
          ORDER BY date DESC
          LIMIT 50";

$result = $conn->query($query);

$transactions = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $transactions[] = [
            'id' => $row['id'],
            'date' => $row['date'],
            'description' => htmlspecialchars($row['description']),
            'amount' => number_format($row['amount'], 2),
            'type' => ucfirst($row['type']),
            'status' => ucfirst($row['status']),
            'sender_account' => htmlspecialchars($row['sender_account']),
            'recipient_account' => htmlspecialchars($row['recipient_account']),
            'recipient_name' => htmlspecialchars($row['recipient_name'] ?? 'N/A'),
            'reference' => htmlspecialchars($row['reference'])
        ];
    }
}

echo json_encode($transactions);
?>
