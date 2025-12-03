<?php
header('Content-Type: application/json');
include "db.php";

$sql = "SELECT * FROM users ORDER BY id DESC";
$result = $conn->query($sql);

$users = [];

if ($result->num_rows > 0) {
    while ($user = $result->fetch_assoc()) {
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
}

//echo json_encode($users);

echo json_encode([
    'users' => $users,
    'totalPages' => 1 // or calculate based on DB
]);
?>