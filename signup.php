<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

$payload = json_decode(file_get_contents('php://input'), true);

if (!$payload) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON payload']);
    exit();
}

$fullName = trim($payload['name'] ?? '');
$email = trim($payload['email'] ?? '');
$username = trim($payload['username'] ?? '');

if ($fullName === '' || $email === '' || $username === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'All fields are required']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid email address']);
    exit();
}

require_once 'db.php';

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

// Ensure the users table has a full_name column (ignore errors if it already exists)
$hasFullName = false;
$columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'full_name'");
if ($columnCheck && $columnCheck->num_rows > 0) {
    $hasFullName = true;
} else {
    $conn->query("ALTER TABLE users ADD COLUMN full_name VARCHAR(100) NULL AFTER username");
    $columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'full_name'");
    if ($columnCheck && $columnCheck->num_rows > 0) {
        $hasFullName = true;
    }
}

// Check for duplicates
$check = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");
$check->bind_param("ss", $email, $username);
$check->execute();
$checkResult = $check->get_result();

if ($checkResult->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['success' => false, 'error' => 'Email or username already exists']);
    $check->close();
    $conn->close();
    exit();
}
$check->close();

$defaultPassword = '123';
$passwordHash = password_hash($defaultPassword, PASSWORD_DEFAULT);

if ($hasFullName) {
    $stmt = $conn->prepare("INSERT INTO users (username, full_name, email, password, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $username, $fullName, $email, $passwordHash);
} else {
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $username, $email, $passwordHash);
}

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to create user']);
    $stmt->close();
    $conn->close();
    exit();
}

$newUserId = $stmt->insert_id;
$stmt->close();

$userQuery = $conn->prepare("SELECT id, username, email, created_at" . ($hasFullName ? ", full_name" : "") . " FROM users WHERE id = ?");
$userQuery->bind_param("i", $newUserId);
$userQuery->execute();
$userResult = $userQuery->get_result();
$userData = $userResult->fetch_assoc();
$userQuery->close();

$conn->close();

echo json_encode([
    'success' => true,
    'user' => $userData,
    'defaultPassword' => $defaultPassword
]);
exit();

