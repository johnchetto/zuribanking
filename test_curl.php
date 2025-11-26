<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>Testing PHP cURL to Safaricom Sandbox</h3>";

$ch = curl_init("https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);

if(curl_errno($ch)){
    echo "<p><b>cURL Error:</b> " . curl_error($ch) . "</p>";
} else {
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}

curl_close($ch);
?>  