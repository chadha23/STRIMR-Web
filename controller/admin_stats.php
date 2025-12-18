<?php
// admin_stats.php
header('Content-Type: application/json');

require_once __DIR__ . '/../model/User.php';

$totalUsers   = 0;
$recentUsers  = [];

try {
    $userModel = new User($conn);
    $totalUsers = $userModel->countUsers();
    $recentRows = $userModel->lastRegisteredUsers(3);

    foreach ($recentRows as $row) {
        $recentUsers[] = [
            'id'         => (int) $row['id'],
            'username'   => $row['username'],
            'created_at' => $row['created_at'] ?? null
        ];
    }

    echo json_encode([
        'success'    => true,
        'stats'      => [
            'totalUsers'    => $totalUsers,
            'activeUsers'   => 0,
            'totalMessages' => 0,
            'totalServers'  => 0,
            'liveStreams'   => 0,
            'totalPosts'    => 0,
        ],
        'recentUsers' => $recentUsers
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
