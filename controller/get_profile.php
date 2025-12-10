<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../model/db.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing user ID']);
    exit();
}

$id = intval($_GET['id']);

$sql = "SELECT id, username, email, full_name FROM users WHERE id = :id LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id]);
$row = $stmt->fetch();

if ($row) {
    echo json_encode([
        'success' => true,
        'user'    => $row
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'User not found']);
}
