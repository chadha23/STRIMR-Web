<?php 
require_once __DIR__.'/../../../Controller/PostController.php';
require_once __DIR__.'/../../../Controller/ReactionController.php';
$postC = new PostController();
$reactionC = new ReactionController();
$list = $postC->getAllPosts();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts - Admin Panel</title>
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
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="../users/index.html" class="nav-link" data-section-target="users-section">
                    <span class="nav-icon">👥</span>
                    <span class="nav-text">Users</span>
                </a>
                <a href="../servers/index.html" class="nav-link" data-section-target="servers-section">
                    <span class="nav-icon">💬</span>
                    <span class="nav-text">Servers</span>
                </a>
                <a href="../messages/index.html" class="nav-link" data-section-target="messages-section">
                    <span class="nav-icon">💌</span>
                    <span class="nav-text">Messages</span>
                </a>
                <a href="../streams/index.html" class="nav-link" data-section-target="streams-section">
                    <span class="nav-icon">📺</span>
                    <span class="nav-text">Streams</span>
                </a>
                <a href="../posts/index.php" class="nav-link active" data-section-target="posts-section">
                    <span class="nav-icon">🐦</span>
                    <span class="nav-text">Posts</span>
                </a>
                <a href="../events/index.html" class="nav-link" data-section-target="events-section">
                    <span class="nav-icon">📅</span>
                    <span class="nav-text">Events</span>
                </a>
                <a href="../settings/index.html" class="nav-link" data-section-target="settings-section">
                    <span class="nav-icon">⚙️</span>
                    <span class="nav-text">Settings</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <button class="logout-btn" type="button" data-template-action="admin-logout">
                    <span class="nav-icon">🚪</span>
                    <span class="nav-text">Logout</span>
                </button>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1 class="page-title" id="page-title">Posts</h1>
                    <p class="page-subtitle" id="page-subtitle">Manage community posts</p>
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

            <section id="posts-section" class="content-section active">
                <div class="section-header">
                    <h2 class="section-title">Post Management</h2>
                    <a href="addPost.php" class="add-btn" style="text-decoration: none; display: inline-block; padding: 10px 20px; background: #5865f2; color: white; border-radius: 4px; border: none; cursor: pointer;">+ Add Post</a>
                </div>

                <div class="table-card">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Content</th>
                                    <th>Author</th>
                                    <th>Date</th>
                                    <th>Likes</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody id="posts-table">
                            <?php foreach($list as $post){ 
                                $reactionCount = $reactionC->getReactionCount($post['id'], 'heart');
                                $reactions = $reactionC->getAllReactionsForPost($post['id'], 'heart');
                            ?>
                                <tr>
                                    <td><?php echo $post['id']; ?></td>
                                    <td><?php echo htmlspecialchars($post['content']); ?></td>
                                    <td><?php echo htmlspecialchars($post['username']); ?></td>
                                    <td><?php echo $post['created_at']; ?></td>
                                    <td>
                                        <strong><?php echo $reactionCount; ?></strong>
                                        <?php if ($reactionCount > 0) { ?>
                                            <button type="button" class="view-reactions-btn" onclick="toggleReactions('<?php echo $post['id']; ?>')" style="margin-left: 8px; padding: 4px 8px; background: #5865f2; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">View</button>
                                        <?php } ?>
                                    </td>
                                    <td>Active</td>
                                    <td>
                                        <a href="deletePost.php?id=<?php echo $post['id']; ?>">Delete</a>
                                        |
                                        <form method="POST" action="updatePost.php" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                            <input type="submit" name="update" value="Modifier">
                                        </form>
                                    </td>
                                </tr>
                                <?php if ($reactionCount > 0) { ?>
                                <tr id="reactions-<?php echo $post['id']; ?>" style="display: none; background-color: #f5f5f5;">
                                    <td colspan="7" style="padding: 15px;">
                                        <div style="margin-bottom: 10px;">
                                            <strong>Users who liked this post (<?php echo $reactionCount; ?>):</strong>
                                        </div>
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <thead>
                                                <tr style="background-color: #e0e0e0;">
                                                    <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Username</th>
                                                    <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Date</th>
                                                    <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($reactions as $reaction) { ?>
                                                <tr>
                                                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($reaction['username']); ?></td>
                                                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo $reaction['created_at']; ?></td>
                                                    <td style="padding: 8px; border: 1px solid #ddd;">
                                                        <a href="deleteReaction.php?id=<?php echo $reaction['id']; ?>&post_id=<?php echo $post['id']; ?>" 
                                                           onclick="return confirm('Are you sure you want to delete this reaction?')"
                                                           style="color: #e0245e; text-decoration: none;">Delete</a>
                                                    </td>
                                                </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <?php } ?>
                            <?php } ?>
                            </tbody>

                            
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script>
        function toggleReactions(postId) {
            const row = document.getElementById('reactions-' + postId);
            if (row) {
                row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
            }
        }
    </script>
</body>
</html>