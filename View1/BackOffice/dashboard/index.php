<?php
session_start();
require_once __DIR__ . '/../../../Controller/AuthController.php';
require_once __DIR__ . '/../../../Controller/UserController.php';
require_once __DIR__ . '/../../../Controller/PostController.php';
require_once __DIR__ . '/../../../Controller/ServerController.php';
require_once __DIR__ . '/../../../Controller/MessageController.php';

$authController = new AuthController();

// Require admin login
if (!$authController->isLoggedIn() || !$authController->isAdmin()) {
    header('Location: ../auth/index.php');
    exit();
}

$userController = new UserController();
$postController = new PostController();
$serverController = new ServerController();
$messageController = new MessageController();

// Get statistics with error handling
try {
    $totalUsers = $userController->getUserCount();
    $recentUsers = $userController->getRecentUsers(7);
    $allUsers = $userController->getAllUsers();
    $userGrowthData = $userController->getUserGrowthData();
} catch (Exception $e) {
    error_log("Error getting user stats: " . $e->getMessage());
    $totalUsers = 0;
    $recentUsers = 0;
    $allUsers = [];
    $userGrowthData = [];
}

try {
    $allPosts = $postController->getAllPosts();
    $totalPosts = is_array($allPosts) ? count($allPosts) : 0;
} catch (Exception $e) {
    error_log("Error getting posts: " . $e->getMessage());
    $allPosts = [];
    $totalPosts = 0;
}

try {
    $servers = $serverController->listServers();
    $totalServers = is_array($servers) ? count($servers) : 0;
} catch (Exception $e) {
    error_log("Error getting servers: " . $e->getMessage());
    $servers = [];
    $totalServers = 0;
}

try {
    $messages = $messageController->getAllMessages();
    $totalMessages = is_array($messages) ? count($messages) : 0;
} catch (Exception $e) {
    error_log("Error getting messages: " . $e->getMessage());
    $messages = [];
    $totalMessages = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <link rel="stylesheet" href="../admin-styles.css">
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2 class="sidebar-title">Admin Panel</h2>
                <div class="admin-avatar">A</div>
            </div>

            <nav class="sidebar-nav">
                <a href="index.php" class="nav-link active" data-section-target="dashboard-section">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="../users/index.php" class="nav-link" data-section-target="users-section">
                    <span class="nav-icon">👥</span>
                    <span class="nav-text">Users</span>
                </a>
                <a href="../servers/index.php" class="nav-link" data-section-target="servers-section">
                    <span class="nav-icon">💬</span>
                    <span class="nav-text">Servers</span>
                </a>
                <a href="../messages/index.php" class="nav-link" data-section-target="messages-section">
                    <span class="nav-icon">💌</span>
                    <span class="nav-text">Messages</span>
                </a>
                <a href="../streams/index.php" class="nav-link" data-section-target="streams-section">
                    <span class="nav-icon">📺</span>
                    <span class="nav-text">Streams</span>
                </a>
                <a href="../posts/index.php" class="nav-link" data-section-target="posts-section">
                    <span class="nav-icon">🐦</span>
                    <span class="nav-text">Posts</span>
                </a>
                <a href="../events/index.php" class="nav-link" data-section-target="events-section">
                    <span class="nav-icon">📅</span>
                    <span class="nav-text">Events</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <a href="../auth/logout.php" class="logout-btn" style="text-decoration: none; display: flex; align-items: center; gap: 12px; padding: 12px 16px; color: #ed4245; background: transparent; border: none; cursor: pointer; width: 100%; border-radius: 8px; transition: background 0.2s;">
                    <span class="nav-icon">🚪</span>
                    <span class="nav-text">Logout</span>
                </a>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1 class="page-title" id="page-title">Dashboard</h1>
                    <p class="page-subtitle" id="page-subtitle">Overview of your platform</p>
                </div>
                <div class="header-right">
                    <div class="search-box">
                        <input type="text" placeholder="Search..." id="admin-search" class="search-input" />
                        <span class="search-icon">🔍</span>
                    </div>
                    <button class="refresh-btn" type="button" title="Refresh Data" onclick="location.reload()">
                        🔄
                    </button>
                </div>
            </header>

            <section id="dashboard-section" class="content-section active">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon users-icon">👥</div>
                        <div class="stat-content">
                            <div class="stat-label">Total Users</div>
                            <div class="stat-value" id="stat-total-users"><?php echo $totalUsers; ?></div>
                            <div class="stat-change positive">+<?php echo $recentUsers; ?> new this week</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon active-icon">🟢</div>
                        <div class="stat-content">
                            <div class="stat-label">Active Users</div>
                            <div class="stat-value" id="stat-active-users"><?php echo $totalUsers; ?></div>
                            <div class="stat-change positive">All registered users</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon messages-icon">💌</div>
                        <div class="stat-content">
                            <div class="stat-label">Total Messages</div>
                            <div class="stat-value" id="stat-total-messages"><?php echo $totalMessages; ?></div>
                            <div class="stat-change positive">All messages</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon servers-icon">💬</div>
                        <div class="stat-content">
                            <div class="stat-label">Total Servers</div>
                            <div class="stat-value" id="stat-total-servers"><?php echo $totalServers; ?></div>
                            <div class="stat-change">Active servers</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon posts-icon">🐦</div>
                        <div class="stat-content">
                            <div class="stat-label">Total Posts</div>
                            <div class="stat-value" id="stat-total-posts"><?php echo $totalPosts; ?></div>
                            <div class="stat-change positive">All posts</div>
                        </div>
                    </div>
                </div>

                <div class="charts-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                    <div class="chart-card" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 20px;">
                        <h3 class="chart-title" style="color: #ffffff; font-size: 18px; margin-bottom: 20px;">User Growth (Last 7 Days)</h3>
                        <div class="chart-placeholder" id="user-growth-chart" style="height: 200px; display: flex; flex-direction: column; justify-content: flex-end;">
                            <?php
                            // Create array with 7 days of data
                            $chartData = [];
                            $maxCount = 1;
                            for ($i = 6; $i >= 0; $i--) {
                                $date = date('Y-m-d', strtotime("-$i days"));
                                $count = 0;
                                foreach ($userGrowthData as $data) {
                                    if ($data['date'] == $date) {
                                        $count = (int)$data['count'];
                                        break;
                                    }
                                }
                                $chartData[] = ['date' => $date, 'count' => $count];
                                if ($count > $maxCount) $maxCount = $count;
                            }
                            if ($maxCount == 0) $maxCount = 1;
                            ?>
                            <div class="chart-bars" style="display: flex; align-items: flex-end; gap: 8px; height: 150px; margin-bottom: 10px;">
                                <?php foreach ($chartData as $data): ?>
                                    <div class="chart-bar" style="flex: 1; background: linear-gradient(to top, #5865f2, #4752c4); border-radius: 4px 4px 0 0; height: <?php echo ($data['count'] / $maxCount) * 100; ?>%; min-height: 2px;" title="<?php echo $data['count']; ?> users on <?php echo date('M d', strtotime($data['date'])); ?>"></div>
                                <?php endforeach; ?>
                            </div>
                            <div class="chart-labels" style="display: flex; justify-content: space-between; font-size: 11px; color: #a0a0a0;">
                                <?php foreach ($chartData as $data): ?>
                                    <span><?php echo date('D', strtotime($data['date'])); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="chart-card" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 20px;">
                        <h3 class="chart-title" style="color: #ffffff; font-size: 18px; margin-bottom: 20px;">Activity Overview</h3>
                        <div class="activity-list" id="activity-list" style="max-height: 200px; overflow-y: auto;">
                            <?php 
                            // Show recent user registrations
                            $recentUsers = array_slice($allUsers, 0, 5);
                            foreach ($recentUsers as $user): 
                                $timeAgo = '';
                                $created = strtotime($user['created_at']);
                                $diff = time() - $created;
                                if ($diff < 3600) {
                                    $timeAgo = round($diff / 60) . ' minutes ago';
                                } elseif ($diff < 86400) {
                                    $timeAgo = round($diff / 3600) . ' hours ago';
                                } else {
                                    $timeAgo = round($diff / 86400) . ' days ago';
                                }
                            ?>
                                <div class="activity-item" style="display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                    <div class="activity-icon" style="font-size: 20px;">👤</div>
                                    <div class="activity-content" style="flex: 1;">
                                        <p class="activity-text" style="color: #ffffff; margin: 0 0 4px 0; font-size: 14px;"><strong><?php echo htmlspecialchars($user['username']); ?></strong> joined the platform.</p>
                                        <p class="activity-time" style="color: #a0a0a0; margin: 0; font-size: 12px;"><?php echo $timeAgo; ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($recentUsers) == 0): ?>
                                <div class="activity-item">
                                    <div class="activity-content">
                                        <p class="activity-text" style="color: #a0a0a0;">No recent activity</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">All Users (<?php echo $totalUsers; ?>)</h3>
                        <a href="../users/index.php" class="view-all-btn">View All</a>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="users-table">
                                <?php if (count($allUsers) > 0): ?>
                                    <?php foreach (array_slice($allUsers, 0, 10) as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(substr($user['id'], 0, 20)); ?>...</td>
                                            <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <button class="action-btn btn-edit" type="button">Edit</button>
                                                <button class="action-btn btn-delete" type="button">Remove</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 20px; color: #a0a0a0;">
                                            No users found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">Recent Posts</h3>
                        <a href="../posts/index.php" class="view-all-btn">View All</a>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Author</th>
                                    <th>Content</th>
                                    <th>Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="recent-posts-table">
                                <?php if (count($allPosts) > 0): ?>
                                    <?php foreach (array_slice($allPosts, 0, 10) as $post): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($post['username'] ?? 'Unknown'); ?></td>
                                            <td><?php echo htmlspecialchars(substr($post['content'], 0, 100)); ?><?php echo strlen($post['content']) > 100 ? '...' : ''; ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($post['created_at'])); ?></td>
                                            <td>
                                                <a href="../posts/index.php" class="action-btn btn-edit">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 20px; color: #a0a0a0;">
                                            No posts found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
