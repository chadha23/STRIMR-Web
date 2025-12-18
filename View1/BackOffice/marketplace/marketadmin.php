<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Discord Clone</title>
    <link rel="stylesheet" href="admin-styles.css">
</head>

<body>
    <!-- ============================================
         ADMIN DASHBOARD PAGE
         ============================================ -->
    <div class="admin-container">
        <!-- ============================================
             SIDEBAR NAVIGATION
             ============================================ -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2 class="sidebar-title">Admin Panel</h2>
                <div class="admin-avatar">A</div>
            </div>

            <nav class="sidebar-nav">
                <a href="http://localhost/marketplace/view/admin.html" class="nav-link" onclick="switchSection('dashboard')">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="#users" class="nav-link" onclick="switchSection('users')">
                    <span class="nav-icon">👥</span>
                    <span class="nav-text">Users</span>
                </a>
                <a href="#servers" class="nav-link" onclick="switchSection('servers')">
                    <span class="nav-icon">💬</span>
                    <span class="nav-text">Servers</span>
                </a>
                <a href="#messages" class="nav-link" onclick="switchSection('messages')">
                    <span class="nav-icon">💌</span>
                    <span class="nav-text">Messages</span>
                </a>
                <a href="#streams" class="nav-link" onclick="switchSection('streams')">
                    <span class="nav-icon">📺</span>
                    <span class="nav-text">Streams</span>
                </a>
                <a href="#posts" class="nav-link" onclick="switchSection('posts')">
                    <span class="nav-icon">🐦</span>
                    <span class="nav-text">Posts</span>
                </a>
                <a href="#market" class="nav-link active" onclick="switchSection('market')">
                    <span class="nav-icon">🛒</span>
                    <span class="nav-text">Market</span>
                </a>
                <a href="#settings" class="nav-link" onclick="switchSection('settings')">
                    <span class="nav-icon">⚙️</span>
                    <span class="nav-text">Settings</span>
                </a>

            </nav>

            <div class="sidebar-footer">
                <button class="logout-btn" onclick="handleAdminLogout()">
                    <span class="nav-icon">🚪</span>
                    <span class="nav-text">Logout</span>
                </button>
            </div>
        </aside>

        <!-- ============================================
             MAIN CONTENT AREA
             ============================================ -->
        <main class="admin-main">
            <!-- Top Header -->
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
                    <button class="refresh-btn" onclick="refreshData()" title="Refresh Data">
                        🔄
                    </button>
                </div>
            </header>


            <!-- Charts/Graphs Area -->


            <!-- USERS SECTION -->
            <section id="users-section" class="content-section">
                <div class="section-header">
                    <h2 class="section-title">User Management</h2>
                    <button class="add-btn" onclick="showAddUserModal()">+ Add User</button>
                </div>

                <!-- Users Filter/Search -->
                <div class="filter-bar">
                    <div class="filter-group">
                        <label>Filter:</label>
                        <select id="user-filter" class="filter-select" onchange="filterUsers()">
                            <option value="all">All Users</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="new">New (Last 7 days)</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Sort:</label>
                        <select id="user-sort" class="filter-select" onchange="sortUsers()">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="name">By Name</option>
                            <option value="activity">Most Active</option>
                        </select>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="table-card">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Joined</th>
                                    <th>Status</th>
                                    <th>Messages</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="users-table">
                                <!-- User rows will be inserted here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    <button class="page-btn" onclick="changePage(-1)">← Previous</button>
                    <span class="page-info">Page <span id="current-page">1</span> of <span id="total-pages">1</span></span>
                    <button class="page-btn" onclick="changePage(1)">Next →</button>
                </div>
            </section>

            <!-- SERVERS SECTION -->
            <section id="servers-section" class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Server Management</h2>
                    <button class="add-btn" onclick="showAddServerModal()">+ Add Server</button>
                </div>

                <div class="cards-grid" id="servers-grid">
                    <!-- Server cards will be inserted here -->
                </div>
            </section>

            <!-- MESSAGES SECTION -->
            <section id="messages-section" class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Message Management</h2>
                </div>

                <div class="table-card">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Content</th>
                                    <th>Server</th>
                                    <th>Channel</th>
                                    <th>Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="messages-table">
                                <!-- Message rows will be inserted here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- STREAMS SECTION -->
            <section id="streams-section" class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Stream Management</h2>
                </div>

                <div class="table-card">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Streamer</th>
                                    <th>Title</th>
                                    <th>Viewers</th>
                                    <th>Status</th>
                                    <th>Started</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="streams-table">
                                <!-- Stream rows will be inserted here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- POSTS SECTION -->
            <section id="posts-section" class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Post Management</h2>
                </div>

                <div class="table-card">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Content</th>
                                    <th>Likes</th>
                                    <th>Retweets</th>
                                    <th>Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="posts-table">
                                <!-- Post rows will be inserted here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
            <!-- MARKETPLACE SECTION -->

            <div class="section-header">
                <h2 class="section-title">Badge Marketplace</h2>
            </div>

            <!-- Search Badge Form -->
            <div class="search-badge-form">
                <form method="GET" action="">
                    <input type="text" name="search_badge" placeholder="Search badge...">
                    <button type="submit">Search</button>
                </form>
            </div>

            <!-- Add Badge Form -->
            <div class="add-badge-form">
                <form method="POST" action="">
                    <input type="text" name="badge_name" placeholder="Badge Name" required>
                    <input type="text" name="badge_icon" placeholder="Badge Icon (emoji)" required>
                    <input type="text" name="description" placeholder="Description" required>
                    <input type="number" name="cost" placeholder="Cost" required>
                    <input type="number" name="exp_reward" placeholder="EXP reward" required>
                    <button type="submit" name="add_badge">Add Badge</button>
                </form>
            </div>






            <!-- Badges Table -->
            <div class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <td>id</td>
                            <td>name</td>
                            <td>icon</td>
                            <td>description</td>
                            <td>cost</td>
                            <td>exp_reward</td>

                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Connect to database using PDO
                        $host = "localhost";
                        $dbname = "badgemarket";
                        $username = "root";
                        $password = "";

                        try {
                            $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
                            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                            // Handle Add Badge
                            if (isset($_POST['add_badge'])) {
                                $name = $_POST['badge_name'];
                                $icon = $_POST['badge_icon'];
                                $description = $_POST['description'];
                                $cost = $_POST['cost'];
                                $exp_reward = $_POST['exp_reward'];

                                $stmt = $conn->prepare("INSERT INTO badges (name, icon, description, cost, exp_reward) VALUES (:name, :icon, :description, :cost, :exp_reward)");
                                $stmt->execute([
                                    ':name' => $name,
                                    ':icon' => $icon,
                                    ':description' => $description,
                                    ':cost' => $cost,
                                    ':exp_reward' => $exp_reward
                                ]);

                                echo "<p>Badge added successfully!</p>";
                            }

                            // Handle Delete Badge
                            if (isset($_GET['delete_badge'])) {
                                $id = intval($_GET['delete_badge']);

                                // Start transaction
                                $conn->beginTransaction();

                                try {
                                    // Delete from user_badges first (foreign key constraint)
                                    $stmt = $conn->prepare("DELETE FROM user_badges WHERE badge_id = :id");
                                    $stmt->execute([':id' => $id]);

                                    // Delete from badges
                                    $stmt = $conn->prepare("DELETE FROM badges WHERE id = :id");
                                    $stmt->execute([':id' => $id]);

                                    $conn->commit();
                                    echo "<p>Badge deleted successfully!</p>";
                                } catch (Exception $e) {
                                    $conn->rollBack();
                                    echo "<p>Error deleting badge: " . $e->getMessage() . "</p>";
                                }
                            }

                            // Handle Search
                            $search = '';
                            if (!empty($_GET['search_badge'])) {
                                $search = '%' . $_GET['search_badge'] . '%';
                                $stmt = $conn->prepare("SELECT * FROM badges WHERE name LIKE :search ORDER BY id DESC");
                                $stmt->execute([':search' => $search]);
                            } else {
                                $stmt = $conn->prepare("SELECT * FROM badges ORDER BY id DESC");
                                $stmt->execute();
                            }

                            // Display Badges
                            $badges = $stmt->fetchAll();

                            if (count($badges) > 0) {
                                foreach ($badges as $row) {
                                    echo "<tr>
                                        <td>{$row['id']}</td>
                                        <td>{$row['name']}</td>
                                        <td>{$row['icon']}</td>
                                        <td>{$row['description']}</td>
                                        <td>{$row['cost']}</td>
                                        <td>{$row['exp_reward']}</td>
                                        <td>
                                            <a href='?delete_badge={$row['id']}' class='btn-delete' onclick=\"return confirm('Delete this badge?');\">Delete</a>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7'>No badges found</td></tr>";
                            }
                        } catch (PDOException $e) {
                            die("Connection failed: " . $e->getMessage());
                        }

                        // Close connection (not strictly needed as PDO closes automatically)
                        $conn = null;
                        ?>

                    </tbody>
                </table>
            </div>





            </section>
        </main>
    </div>

    <!-- ============================================
         MODALS
         ============================================ -->
    <!-- Add User Modal -->
    <div id="add-user-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New User</h3>
                <button class="modal-close" onclick="closeModal('add-user-modal')">×</button>
            </div>
            <form class="modal-form" onsubmit="handleAddUser(event)">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" class="form-input" required />
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" class="form-input" required />
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" class="form-input" required />
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" class="form-input" required />
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('add-user-modal')">Cancel</button>
                    <button type="submit" class="btn-submit">Add User</button>
                </div>
            </form>
        </div>
    </div>


</body>

</html>