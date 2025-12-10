<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../model/db.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing id']);
    exit;
}

$id = intval($_GET['id']);

$sql = "SELECT full_name FROM users WHERE id = :id LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id]);
$row = $stmt->fetch();

if ($row) {
    echo json_encode([
        'success' => true,
        'full_name' => $row['full_name']
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'User not found']);
}
