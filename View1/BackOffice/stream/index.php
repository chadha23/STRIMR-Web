<?php 
session_start();
require_once __DIR__ . '/../../../Controller/AuthController.php';

$authController = new AuthController();

// Require admin login
if (!$authController->isLoggedIn() || !$authController->isAdmin()) {
    header('Location: ../auth/index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streams - Admin Panel</title>
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
                <a href="../servers/index.php" class="nav-link" data-section-target="servers-section">
                    <span class="nav-icon">💬</span>
                    <span class="nav-text">Servers</span>
                </a>
                <a href="../messages/index.php" class="nav-link" data-section-target="messages-section">
                    <span class="nav-icon">💌</span>
                    <span class="nav-text">Messages</span>
                </a>
                <a href="index.php" class="nav-link active" data-section-target="streams-section">
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
                    <h1 class="page-title" id="page-title">Streams</h1>
                    <p class="page-subtitle" id="page-subtitle">Manage platform streams</p>
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

            <section id="streams-section" class="content-section active">
                <div class="section-header">
                    <h2 class="section-title">Stream Management</h2>
                    <a href="addStream.php" class="add-btn" style="text-decoration: none; display: inline-block; padding: 10px 20px; background: #5865f2; color: white; border-radius: 4px; border: none; cursor: pointer;">+ Add Stream</a>
                </div>

                <div class="table-card">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Stream Title</th>
                                    <th>Streamer</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="streams-table">
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px; color: #a0a0a0;">
                                        No streams found. Click "+ Add Stream" to create one.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
