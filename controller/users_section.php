<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../model/User.php';

$userModel = new User($conn);
$fetchedUsers = $userModel->lastRegisteredUsers(0);

$users = [];

foreach ($fetchedUsers as $user) {
    $users[] = [
        'id' => $user['id'],
        'name' => $user['username'],
        'username' => $user['username'],
        'email' => $user['email'],
        'joined' => $user['created_at'],
        'status' => 'active',
        'messages' => 0       // placeholder, or count from messages table if you want
    ];
}

echo json_encode([
    'users' => $users,
    'totalPages' => 1 // or calculate based on DB
]);
?>
