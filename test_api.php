<?php

// Simple script to test the API registration endpoint

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test registration
echo "\n===== TESTING REGISTRATION =====\n";
$url = 'http://localhost:8000/api/register';
$data = [
    'name' => 'Test User',
    'email' => 'test' . time() . '@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123'
];

$jsonData = json_encode($data);
echo "Request data: $jsonData\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Content-Length: ' . strlen($jsonData)
]);

// Get request headers for debugging
curl_setopt($ch, CURLOPT_VERBOSE, true);
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$error = curl_error($ch);

echo "HTTP Code: $httpCode\n";
echo "Content-Type: $contentType\n";
echo "Response: $response\n";

if ($error) {
    echo "Error: $error\n";
}

// Get verbose information
rewind($verbose);
$verboseLog = stream_get_contents($verbose);
echo "\nVerbose information:\n$verboseLog\n";

// Parse the response
if ($response) {
    $responseData = json_decode($response, true);
    echo "\nParsed Response:\n";
    print_r($responseData);
    
    // Check if token exists
    if (isset($responseData['token'])) {
        echo "\nToken received: " . $responseData['token'] . "\n";
    } else {
        echo "\nNo token received in the response!\n";
    }
}

curl_close($ch);