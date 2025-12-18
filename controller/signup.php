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

require_once __DIR__ . '/../model/db.php';

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
    $check = $conn->prepare("SELECT id, is_verified FROM users WHERE email = :email OR username = :username LIMIT 1");
    $check->execute([
        ':email' => $email,
        ':username' => $username
    ]);
    $checkResult = $check->fetch();

    if ($checkResult) {
        $isVerified = isset($checkResult['is_verified']) ? (int)$checkResult['is_verified'] : 0;
        http_response_code(409);
        if ($isVerified === 0) {
            echo json_encode([
                'success' => false,
                'code' => 'EMAIL_NOT_VERIFIED',
                'error' => 'Email not verified. Please verify your email.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'code' => 'ALREADY_EXISTS',
                'error' => 'Email or username already exists'
            ]);
        }
        exit();
    }

    $defaultPassword = '123';
    $passwordHash = password_hash($defaultPassword, PASSWORD_DEFAULT);

    $isAdmin = ($username === 'admin');
    $verificationToken = $isAdmin ? null : bin2hex(random_bytes(32));
    $isVerifiedValue = $isAdmin ? 1 : 0;

    if ($hasFullName) {
        $stmt = $conn->prepare("INSERT INTO users (username, full_name, email, password, is_verified, verification_token, created_at) VALUES (:username, :full_name, :email, :password, :is_verified, :verification_token, NOW())");
        $stmt->execute([
            ':username' => $username,
            ':full_name' => $fullName,
            ':email' => $email,
            ':password' => $passwordHash,
            ':is_verified' => $isVerifiedValue,
            ':verification_token' => $verificationToken
        ]);
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, is_verified, verification_token, created_at) VALUES (:username, :email, :password, :is_verified, :verification_token, NOW())");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $passwordHash,
            ':is_verified' => $isVerifiedValue,
            ':verification_token' => $verificationToken
        ]);
    }

    if (!$isAdmin) {
        require_once __DIR__ . '/../core/Mailer.php';
        $fullNameOrUsername = $fullName !== '' ? $fullName : $username;

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/controller/signup.php';
        $directory = str_replace('\\', '/', dirname($scriptName));
        if ($directory === '/' || $directory === '\\' || $directory === '.' || $directory === '') {
            $directory = '';
        }
        $directory = rtrim($directory, '/');
        $verifyPath = ($directory === '' ? '' : $directory) . '/verify_email.php';
        if (strpos($verifyPath, '/') !== 0) {
            $verifyPath = '/' . ltrim($verifyPath, '/');
        }
        $verifyUrl = $scheme . '://' . $host . $verifyPath . '?token=' . urlencode($verificationToken);

        $mailSent = Mailer::sendVerificationEmail($email, $fullNameOrUsername, $verifyUrl, 'within 24 hours');
        if (!$mailSent) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Could not send verification email']);
            exit();
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Account created. Please check your email to verify your account.'
    ]);
    exit();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit();
}

