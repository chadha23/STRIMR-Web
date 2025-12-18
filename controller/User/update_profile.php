<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../model/User.php';

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
    $userModel = new User($conn);
    $result = $userModel->updateProfile($id, $username, $full_name, $email);

    if (!$result['success']) {
        if (($result['error'] ?? '') === 'duplicate') {
            echo json_encode(['success' => false, 'error' => 'Email or username already used by another account']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update profile']);
        }
        exit();
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to update profile']);
}
