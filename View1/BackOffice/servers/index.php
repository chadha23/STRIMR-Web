<?php 
session_start();
require_once __DIR__ . '/../../../Controller/AuthController.php';

$authController = new AuthController();

// Require admin login
if (!$authController->isLoggedIn() || !$authController->isAdmin()) {
    header('Location: ../auth/index.php');
    exit();
}

require_once __DIR__ . '/../../../Controller/ServerController.php';

$serverC = new ServerController();
$list    = $serverC->listServers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servers - Admin Panel</title>
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
                <a href="../dashboard/index.php" class="nav-link" data-section-target="dashboard-section">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="../users/index.php" class="nav-link" data-section-target="users-section">
                    <span class="nav-icon">👥</span>
                    <span class="nav-text">Users</span>
                </a>
                <a href="index.php" class="nav-link active" data-section-target="servers-section">
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
                    <h1 class="page-title" id="page-title">Servers</h1>
                    <p class="page-subtitle" id="page-subtitle">Manage community servers</p>
                </div>
                <div class="header-right">
                    <div class="search-box">
                        <input type="text" placeholder="Search..." id="admin-search" class="search-input" />
                        <span class="search-icon">🔍</span>
                    </div>
                    <button class="refresh-btn" type="button" title="Refresh Data" data-template-action="refresh">
                        🔄
                    </button>
                </div>
            </header>

            <section id="servers-section" class="content-section active">
                <div class="section-header">
                    <h2 class="section-title">Server Management</h2>
                    <a href="addServer.php" class="add-btn" style="text-decoration:none;">+ Add Server</a>
                </div>

                <div class="cards-grid" id="servers-grid">
                    <?php if (!empty($list)): ?>
                        <?php foreach ($list as $server): ?>
                            <article class="server-card">
                                <div class="server-card-header">
                                    <div class="server-icon-sm">🎮</div>
                                    <div>
                                        <h3 class="server-name"><?php echo htmlspecialchars($server['name']); ?></h3>
                                        <p class="server-meta">ID: <?php echo $server['id']; ?></p>
                                    </div>
                                    <span class="status-badge status-active">Active</span>
                                </div>
                                <div class="server-card-body">
                                    <p>Manage channels and messages for this server.</p>
                                </div>
                                <div class="server-card-actions">
                                    <a href="updateServer.php?id=<?php echo $server['id']; ?>" class="action-btn btn-edit">Edit</a>
                                    <a href="deleteServer.php?id=<?php echo $server['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Are you sure you want to delete this server?');">Delete</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color:#a0a0a0;">No servers found. Click &quot;+ Add Server&quot; to create one.</p>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>


