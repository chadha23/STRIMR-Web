<?php
require_once __DIR__ . '/../../controllers/ServerController.php';
require_once __DIR__ . '/../../controllers/MessageController.php';
require_once __DIR__ . '/../../models/Server.php';
require_once __DIR__ . '/../../controllers/NotificationController.php';

$notifC = new NotificationController();
$notifications = $notifC->getAll();

$serverC  = new ServerController();
$messageC = new MessageController();

/// === APRÈS L'AJOUT D'UN SERVEUR ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addServer'])) {
    if (!empty(trim($_POST['name']))) {
        $server = new Server(null, trim($_POST['name']));
        $serverC->addServer($server);
        $notifC->add("Nouveau serveur créé : " . htmlspecialchars(trim($_POST['name'])));
    }
    header("Location: admin.php#servers");
    exit;
}

// === APRÈS LA MODIFICATION D'UN SERVEUR ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editServer'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    if ($id && $name !== '') {
        $updated = new Server($id, $name);
        $serverC->updateServer($updated, $id);
        $notifC->add("Serveur modifié : \"$name\"");
    }
    header("Location: admin.php#servers");
    exit;
}

// === SUPPRESSION SERVEUR / MESSAGE ===
if (isset($_GET['delete_server'])) {
    $id = (int)$_GET['delete_server'];
    if ($id > 0) {
        $serverC->deleteServer($id);
        $notifC->add("Serveur supprimé (ID: $id)");
    }
    header("Location: admin.php#servers");
    exit;
}
if (isset($_GET['delete_message'])) {
    $id = (int)$_GET['delete_message'];
    if ($id > 0) {
        $messageC->deleteMessage($id);
        $notifC->add("Message supprimé (ID: $id)");
    }
    header("Location: admin.php#messages");
    exit;
}

$total_servers  = $serverC->countServers();
$total_messages = $messageC->countMessages();

// Recherche + tri alphabétique
$search = trim($_GET['search'] ?? '');
$allServers = $search !== '' ? $serverC->searchServers($search) : $serverC->listServers();
usort($allServers, fn($a, $b) => strcasecmp($a['name'], $b['name']));
$servers  = $allServers;
$messages = $messageC->getAllMessages();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Discord Clone</title>
    <link rel="stylesheet" href="admin-styles.css">
    <style>
        .content-section { display: none; }
        .content-section.active { display: block; }
    </style>
</head>
<body>

<div class="admin-container">
    <!-- SIDEBAR -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <h2 class="sidebar-title">Admin Panel</h2>
            <div class="admin-avatar">A</div>
        </div>

        <nav class="sidebar-nav">
            <a href="#dashboard" class="nav-link active" onclick="switchSection('dashboard'); return false;">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="#users" class="nav-link" onclick="switchSection('users'); return false;">
                <span class="nav-icon">👥</span>
                <span class="nav-text">Users</span>
            </a>
            <a href="#servers" class="nav-link" onclick="switchSection('servers'); return false;">
                <span class="nav-icon">💬</span>
                <span class="nav-text">Servers</span>
            </a>
            <a href="#messages" class="nav-link" onclick="switchSection('messages'); return false;">
                <span class="nav-icon">💌</span>
                <span class="nav-text">Messages</span>
            </a>
            <a href="#streams" class="nav-link" onclick="switchSection('streams'); return false;">
                <span class="nav-icon">📺</span>
                <span class="nav-text">Streams</span>
            </a>
            <a href="#posts" class="nav-link" onclick="switchSection('posts'); return false;">
                <span class="nav-icon">🐦</span>
                <span class="nav-text">Posts</span>
            </a>
            <a href="#settings" class="nav-link" onclick="switchSection('settings'); return false;">
                <span class="nav-icon">⚙️</span>
                <span class="nav-text">Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <button class="logout-btn" onclick="handleAdminLogout()">
                <span class="nav-icon">Exit</span>
                <span class="nav-text">Logout</span>
            </button>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="admin-main">

        <!-- DASHBOARD -->
        <section id="dashboard-section" class="content-section active">
            <header class="admin-header">
                <div class="header-left">
                    <h1 class="page-title" id="page-title">Dashboard</h1>
                    <p class="page-subtitle" id="page-subtitle">Overview of your platform</p>
                </div>
            </header>

            <!-- Toutes tes stats cards (exactement comme avant) -->
            <div class="stats-grid">
                <!-- Total Users -->
                <div class="stat-card">
                    <div class="stat-icon users-icon">👥</div>
                    <div class="stat-content">
                        <div class="stat-label">Users</div>
                        <div class="stat-value" id="stat-total-users">0</div>
                        <div class="stat-change positive">+12% from last month</div>
                    </div>
                </div>
                <!-- Active Users -->
                <div class="stat-card">
                    <div class="stat-icon active-icon">🟢</div>
                    <div class="stat-content">
                        <div class="stat-label">Active Users</div>
                        <div class="stat-value" id="stat-active-users">0</div>
                        <div class="stat-change">Online now</div>
                    </div>
                </div>
                <!-- Total Servers -->
                <div class="stat-card">
                    <div class="stat-icon servers-icon">💬</div>
                    <div class="stat-content">
                        <div class="stat-label">Total Servers</div>
                        <div class="stat-value"><?= number_format($total_servers) ?></div>
                        <div class="stat-change">Actifs sur la plateforme</div>
                    </div>
                </div>
                <!-- Total Messages -->
                <div class="stat-card">
                    <div class="stat-icon messages-icon">💌</div>
                    <div class="stat-content">
                        <div class="stat-label">Total Messages</div>
                        <div class="stat-value"><?= number_format($total_messages) ?></div>
                        <div class="stat-change positive">Depuis le lancement</div>
                    </div>
                </div>
                <!-- Live Streams & Posts (inchangés) -->
                <div class="stat-card">
                    <div class="stat-icon streams-icon">📺</div>
                    <div class="stat-content">
                        <div class="stat-label">Live Streams</div>
                        <div class="stat-value" id="stat-live-streams">0</div>
                        <div class="stat-change">Currently streaming</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon posts-icon">🐦</div>
                    <div class="stat-content">
                        <div class="stat-label">Total Posts</div>
                        <div class="stat-value" id="stat-total-posts">0</div>
                        <div class="stat-change positive">+8.1% from last week</div>
                    </div>
                </div>
            </div>

            <!-- Graphiques + Activité récente (exactement ton code) -->
            <div class="charts-grid">
                <div class="chart-card">
                    <h3 class="chart-title">User Growth</h3>
                    <div class="chart-placeholder" id="user-growth-chart">
                        <div class="chart-bars">
                            <div class="chart-bar" style="height: 60%;"></div>
                            <div class="chart-bar" style="height: 75%;"></div>
                            <div class="chart-bar" style="height: 85%;"></div>
                            <div class="chart-bar" style="height: 70%;"></div>
                            <div class="chart-bar" style="height: 90%;"></div>
                            <div class="chart-bar" style="height: 100%;"></div>
                            <div class="chart-bar" style="height: 95%;"></div>
                        </div>
                        <div class="chart-labels">
                            <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
                        </div>
                    </div>
                </div>

                <div class="chart-card">
                    <h3 class="chart-title">Activity Overview</h3>
                    <div class="activity-list">
                        <?php if (empty($notifications)): ?>
                            <div class="activity-empty">Aucune activité récente</div>
                        <?php else: foreach ($notifications as $n): ?>
                            <div class="activity-item">
                                <div class="activity-content">
                                    <div class="activity-text"><?= htmlspecialchars($n->content) ?></div>
                                </div>
                                <div class="activity-time"><?= date('H:i', strtotime($n->created_at)) ?></div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>

            <div class="table-card">
                <div class="table-header">
                    <h3 class="table-title">Recent Activity</h3>
                    <button class="view-all-btn" onclick="switchSection('messages')">View All</button>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead><tr><th>User</th><th>Action</th><th>Details</th><th>Time</th></tr></thead>
                        <tbody id="recent-activity-table"></tbody>
                    </table>
                </div>
            </div>
        </section>

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
<!-- SERVERS SECTION  -->
<section id="servers-section" class="content-section">
    <!-- En-tête avec titre + recherche à droite -->
    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
        <h2 class="section-title">Server Management</h2>

        <!-- BARRE DE RECHERCHE PETITE ET À DROITE -->
        <div class="search-box" style="width: 240px;">
            <input 
                type="text" 
                placeholder="Rechercher un serveur..." 
                id="server-search-input"
                class="search-input"
                onkeyup="filterServersTable()"
                style="font-size: 13px; height: 38px; padding-left: 38px;"
            />
            <span class="search-icon">Search</span>
        </div>
    </div>

    <!-- fin section-header -->

    <!-- Formulaire ajout serveur -->
    <form method="POST" class="add-form" style="margin: 20px 0; display: flex; gap: 10px; align-items: end;">
        <div class="form-group">
            <label>Nom du serveur</label>
            <input type="text" name="name" class="form-input" required style="width: 300px;">
        </div>
        <button type="submit" name="addServer" class="add-btn">+ Ajouter Serveur</button>
    </form>

    <!-- Tableau des serveurs -->
    <div class="table-card">
        <div class="table-container">
            <table class="data-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom du serveur</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="servers-table-body">
                    <?php if (!empty($servers)): ?>
                        <?php foreach ($servers as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['id']) ?></td>
                                <td><?= htmlspecialchars($s['name']) ?></td>
                                <td>
                                    <a href="admin.php?edit=<?= $s['id'] ?>#servers" class="edit-btn">Modifier</a>
                                    <a href="admin.php?delete_server=<?= $s['id'] ?>#servers" 
                                       class="delete-btn" 
                                       onclick="return confirm('Supprimer ce serveur et tous ses messages ?')">
                                       Supprimer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align:center; color:#888; padding:30px;">
                                Aucun serveur trouvé
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Formulaire d'édition -->
    <?php if (isset($_GET['edit'])) {
        $editId = (int)$_GET['edit'];
        $serv = $serverC->showServer($editId);
        if ($serv) { ?>
            <div class="edit-panel" style="margin-top:20px;padding:15px;border:1px solid #333;background:#111;border-radius:8px;">
                <h3>Modifier le serveur #<?= $serv['id'] ?></h3>
                <form method="POST">
                    <input type="hidden" name="editServer" value="1">
                    <input type="hidden" name="id" value="<?= $serv['id'] ?>">
                    <input type="text" name="name" value="<?= htmlspecialchars($serv['name']) ?>" class="form-input" style="width:300px;" required>
                    <button type="submit" class="btn-submit">Enregistrer</button>
                    <a href="admin.php#servers" class="btn-cancel">Annuler</a>
                </form>
            </div>
        <?php }
    } ?>
</section>
   


        <!-- MESSAGES SECTION (CORRIGÉ : id changé) -->
        <section id="messages-section" class="content-section">
            <div class="section-header">
                <h2 class="section-title">Message Management</h2>
                <p class="page-subtitle">Tous les messages envoyés par les utilisateurs</p>
            </div>
            <div class="table-card">
                <div class="table-container">
                    <?php if (empty($messages)): ?>
                        <p style="text-align:center; padding:60px; color:#888; font-size:1.1em;">Aucun message pour le moment</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th><th>DE</th><th>À</th><th>CONTENU</th><th>SERVEUR</th><th>DATE / HEURE</th><th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $msg): ?>
                                <tr>
                                    <td><?= htmlspecialchars($msg['id']) ?></td>
                                    <td><strong>#<?= htmlspecialchars($msg['sender_id']) ?></strong></td>
                                    <td><strong>#<?= htmlspecialchars($msg['receiver_id']) ?></strong></td>
                                    <td style="max-width:500px; word-wrap:break-word; font-size:0.95em;">
                                        <?= htmlspecialchars($msg['content']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($msg['server_id']) ?></td>
                                    <td><?= date('d/m/Y H:i:s', strtotime($msg['created_at'])) ?></td>
                                    <td>
                                        <a href="admin.php?delete_message=<?= $msg['id'] ?>#messages"
                                           style="color:#f04747; font-weight:bold; text-decoration:none;"
                                           onclick="return confirm('Supprimer ce message ?')">
                                           Supprimer
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
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

            <!-- SETTINGS SECTION -->
            <section id="settings-section" class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Settings</h2>
                </div>

                <div class="settings-card">
                    <h3 class="settings-group-title">General Settings</h3>
                    <div class="settings-item">
                        <label class="settings-label">Site Name</label>
                        <input type="text" class="settings-input" value="Discord Clone" />
                    </div>
                    <div class="settings-item">
                        <label class="settings-label">Maintenance Mode</label>
                        <label class="toggle-switch">
                            <input type="checkbox" />
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="settings-item">
                        <label class="settings-label">Max Users</label>
                        <input type="number" class="settings-input" value="10000" />
                    </div>
                    <button class="save-btn">Save Settings</button>
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

       

<script src="admin.js"></script>

<!-- Petit script pour gérer le #hash au chargement -->
<script>
    // Active la bonne section selon le #hash
    function activateSectionFromHash() {
        const hash = window.location.hash.substring(1) || 'dashboard';
        switchSection(hash);
    }
    window.addEventListener('load', activateSectionFromHash);
    window.addEventListener('hashchange', activateSectionFromHash);
</script>

</body>
</html>




