<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Daraja Sandbox Credentials
define('CONSUMER_KEY', 'euDnkdlarKxrAetuaA07vipRERzEahgDI4KurMc3l6Pvg7aW');
define('CONSUMER_SECRET', 'fTQUIvCWZh7v4TVtxbokQQAk0AOYXIT7XJLDKDrpxgAvDjnoE7cLQNoi9OQ0JuGK');

function generateAccessToken() {
    $url = "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
    $credentials = base64_encode(CONSUMER_KEY . ":" . CONSUMER_SECRET);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic {$credentials}"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo "cURL Error: " . curl_error($ch);
        return null;
    }

    $data = json_decode($response, true);
    return $data['access_token'] ?? null;
}

// Test
$token = generateAccessToken();
if ($token) {
    echo "Access Token: $token";
} else {
    echo "Failed to generate access token";
}
