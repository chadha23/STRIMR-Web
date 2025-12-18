<?php
// Test script to verify login.php is working
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Login.php</h2>";

// Test 1: Invalid user
echo "<h3>Test 1: Invalid user (randomuser123)</h3>";
$data = json_encode(['email' => 'randomuser123', 'password' => 'randompass']);
$ch = curl_init('http://localhost/STRIMR-Web/controller/login.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "<br>";
echo "Response: " . htmlspecialchars($response) . "<br>";
$result = json_decode($response, true);
echo "Success: " . ($result['success'] ?? 'not set') . "<br><br>";

// Test 2: Valid user with wrong password
echo "<h3>Test 2: Valid user (admin) with wrong password</h3>";
$data = json_encode(['email' => 'admin', 'password' => 'wrongpassword']);
$ch = curl_init('http://localhost/STRIMR-Web/controller/login.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "<br>";
echo "Response: " . htmlspecialchars($response) . "<br>";
$result = json_decode($response, true);
echo "Success: " . ($result['success'] ?? 'not set') . "<br><br>";

// Test 3: Valid user with correct password
echo "<h3>Test 3: Valid user (admin) with correct password (123)</h3>";
$data = json_encode(['email' => 'admin', 'password' => '123']);
$ch = curl_init('http://localhost/STRIMR-Web/controller/login.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "<br>";
echo "Response: " . htmlspecialchars($response) . "<br>";
$result = json_decode($response, true);
echo "Success: " . ($result['success'] ?? 'not set') . "<br>";
if (isset($result['user'])) {
    echo "User ID: " . $result['user']['id'] . "<br>";
    echo "Username: " . $result['user']['username'] . "<br>";
}
?>

