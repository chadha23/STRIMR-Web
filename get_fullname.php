<?php
header('Content-Type: application/json');
require_once 'db.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing id']);
    exit;
}

$id = intval($_GET['id']);

$sql = "SELECT full_name FROM users WHERE id = $id LIMIT 1";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    echo json_encode([
        'success' => true,
        'full_name' => $row['full_name']
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'User not found']);
}
