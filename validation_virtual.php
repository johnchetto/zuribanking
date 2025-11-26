<?php
// validation.php
header("Content-Type: application/json");

// Get raw M-Pesa request
$mpesa_data = file_get_contents("php://input");

// Log for debugging
file_put_contents("validation_log.txt", $mpesa_data . PHP_EOL, FILE_APPEND);

// Always accept the transaction in sandbox
$response = [
    "ResultCode" => 0,
    "ResultDesc" => "Accepted"
];

echo json_encode($response);
?>
