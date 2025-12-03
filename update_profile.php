<?php
header('Content-Type: application/json');
require_once 'db.php';

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);

if (!$payload) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON payload']);
    exit();
}

$id        = isset($payload['id']) ? intval($payload['id']) : 0;
$username  = trim($payload['username'] ?? '');
$full_name = trim($payload['full_name'] ?? '');
$email     = trim($payload['email'] ?? '');

if ($id <= 0 || $username === '' || $full_name === '' || $email === '') {
    echo json_encode(['success' => false, 'error' => 'All fields are required']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email address']);
    exit();
}

// Check for duplicates (other users with same email or username)
$checkSql = "SELECT id FROM users WHERE (email = ? OR username = ?) AND id <> ? LIMIT 1";
$check = $conn->prepare($checkSql);
if (!$check) {
    echo json_encode(['success' => false, 'error' => 'Failed to prepare duplicate check']);
    exit();
}
$check->bind_param("ssi", $email, $username, $id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Email or username already used by another account']);
    $check->close();
    $conn->close();
    exit();
}
$check->close();

// Ensure full_name column exists (like in signup.php)
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

// Update query
if ($hasFullName) {
    $sql = "UPDATE users SET username = ?, full_name = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Failed to prepare update']);
        exit();
    }
    $stmt->bind_param("sssi", $username, $full_name, $email, $id);
} else {
    $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Failed to prepare update']);
        exit();
    }
    $stmt->bind_param("ssi", $username, $email, $id);
}

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'error' => 'Failed to update profile']);
    $stmt->close();
    $conn->close();
    exit();
}

$stmt->close();
$conn->close();

echo json_encode(['success' => true]);
