<?php
// admin_stats.php
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

$totalUsers   = 0;
$recentUsers  = [];

try {
    // 1) TOTAL USERS
    $sqlTotal = "SELECT COUNT(*) AS total_users FROM users";
    $resultTotal = $conn->query($sqlTotal);
    if ($resultTotal && ($row = $resultTotal->fetch())) {
        $totalUsers = (int)$row['total_users'];
    }

    // 2) LAST 3 REGISTERED USERS
    // If you don't have created_at, it's fine: created_at will be null and JS will show "Recently".
    $sqlRecent = "SELECT id, username, created_at 
                  FROM users 
                  ORDER BY id DESC 
                  LIMIT 3";

    $resultRecent = $conn->query($sqlRecent);
    if ($resultRecent) {
        while ($row = $resultRecent->fetch()) {
            $recentUsers[] = [
                'id'         => (int)$row['id'],
                'username'   => $row['username'],
                'created_at' => $row['created_at'] ?? null
            ];
        }
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
