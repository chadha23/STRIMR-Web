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

try {
    // Ensure the users table has a full_name column (ignore errors if it already exists)
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

    // Check for duplicates
    $check = $conn->prepare("SELECT id FROM users WHERE email = :email OR username = :username LIMIT 1");
    $check->execute([
        ':email' => $email,
        ':username' => $username
    ]);
    $checkResult = $check->fetch();

    if ($checkResult) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Email or username already exists']);
        exit();
    }

    $defaultPassword = '123';
    $passwordHash = password_hash($defaultPassword, PASSWORD_DEFAULT);

    if ($hasFullName) {
        $stmt = $conn->prepare("INSERT INTO users (username, full_name, email, password, created_at) VALUES (:username, :full_name, :email, :password, NOW())");
        $stmt->execute([
            ':username' => $username,
            ':full_name' => $fullName,
            ':email' => $email,
            ':password' => $passwordHash
        ]);
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, created_at) VALUES (:username, :email, :password, NOW())");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $passwordHash
        ]);
    }

    $newUserId = $conn->lastInsertId();

    $userQuery = $conn->prepare("SELECT id, username, email, created_at" . ($hasFullName ? ", full_name" : "") . " FROM users WHERE id = :id");
    $userQuery->execute([':id' => $newUserId]);
    $userData = $userQuery->fetch();

    echo json_encode([
        'success' => true,
        'user' => $userData,
        'defaultPassword' => $defaultPassword
    ]);
    exit();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit();
}

