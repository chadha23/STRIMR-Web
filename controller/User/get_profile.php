<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../model/User.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing user ID']);
    exit();
}

$id = intval($_GET['id']);
$userModel = new User($conn);
$row = $userModel->getProfileById($id);

if ($row) {
    echo json_encode([
        'success' => true,
        'user'    => $row
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'User not found']);
}
