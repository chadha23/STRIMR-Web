<?php 
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
                <a href="../dashboard/index.html" class="nav-link" data-section-target="dashboard-section">
                    <span class="nav-icon">­ƒôè</span>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="../users/index.html" class="nav-link" data-section-target="users-section">
                    <span class="nav-icon">­ƒæÑ</span>
                    <span class="nav-text">Users</span>
                </a>
                <a href="../servers/index.php" class="nav-link active" data-section-target="servers-section">
                    <span class="nav-icon">­ƒÆ¼</span>
                    <span class="nav-text">Servers</span>
                </a>
                <a href="../messages/index.html" class="nav-link" data-section-target="messages-section">
                    <span class="nav-icon">­ƒÆî</span>
                    <span class="nav-text">Messages</span>
                </a>
                <a href="../streams/index.html" class="nav-link" data-section-target="streams-section">
                    <span class="nav-icon">­ƒô║</span>
                    <span class="nav-text">Streams</span>
                </a>
                <a href="../posts/index.php" class="nav-link" data-section-target="posts-section">
                    <span class="nav-icon">­ƒÉª</span>
                    <span class="nav-text">Posts</span>
                </a>
                <a href="../events/index.html" class="nav-link" data-section-target="events-section">
                    <span class="nav-icon">­ƒôà</span>
                    <span class="nav-text">Events</span>
                </a>
                <a href="../settings/index.html" class="nav-link" data-section-target="settings-section">
                    <span class="nav-icon">ÔÜÖ´©Å</span>
                    <span class="nav-text">Settings</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <button class="logout-btn" type="button" data-template-action="admin-logout">
                    <span class="nav-icon">­ƒÜ¬</span>
                    <span class="nav-text">Logout</span>
                </button>
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
                        <span class="search-icon">­ƒöì</span>
                    </div>
                    <button class="refresh-btn" type="button" title="Refresh Data" data-template-action="refresh">
                        ­ƒöä
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
                                    <div class="server-icon-sm">­ƒÄ«</div>
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


