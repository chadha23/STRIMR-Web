<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../model/db.php';

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

try {
    // Check for duplicates (other users with same email or username)
    $checkSql = "SELECT id FROM users WHERE (email = :email OR username = :username) AND id <> :id LIMIT 1";
    $check = $conn->prepare($checkSql);
    $check->execute([
        ':email' => $email,
        ':username' => $username,
        ':id' => $id
    ]);
    $result = $check->fetch();

    if ($result) {
        echo json_encode(['success' => false, 'error' => 'Email or username already used by another account']);
        exit();
    }

    // Ensure full_name column exists (like in signup.php)
    $hasFullName = false;
    $columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'full_name'");
    if ($columnCheck && $columnCheck->fetch()) {
        $hasFullName = true;
    } else {
        $conn->exec("ALTER TABLE users ADD COLUMN full_name VARCHAR(100) NULL AFTER username");
        $columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'full_name'");
        if ($columnCheck && $columnCheck->fetch()) {
            $hasFullName = true;
        }
    }

    // Update query
    if ($hasFullName) {
        $sql = "UPDATE users SET username = :username, full_name = :full_name, email = :email WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':full_name' => $full_name,
            ':email' => $email,
            ':id' => $id
        ]);
    } else {
        $sql = "UPDATE users SET username = :username, email = :email WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':id' => $id
        ]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to update profile']);
}
