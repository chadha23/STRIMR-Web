<?php
// marketadmin.php
session_start();
require_once 'Config11.php';
$pdo = Config::getConnexion();

// Fetch badges from database
$stmt = $pdo->query("SELECT * FROM badges");
$badges = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badge Marketplace - Admin</title>
    <link rel="stylesheet" href="admin-styles.css">
</head>

<body>
    <div class="admin-container">
        <!-- SIDEBAR NAVIGATION -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2 class="sidebar-title">Admin Panel</h2>
                <div class="admin-avatar">A</div>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php" class="nav-link">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="marketadmin.php" class="nav-link active">
                    <span class="nav-icon">🛒</span>
                    <span class="nav-text">Market</span>
                </a>
                <!-- add other links if needed -->
            </nav>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1 class="page-title">Badge Marketplace</h1>
                    <p class="page-subtitle">Manage badges available in your store</p>
                </div>
            </header>

            <!-- MARKETPLACE TABLE -->
            <section id="market-section" class="content-section active">
                <div class="table-card">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Badge Name</th>
                                    <th>Badge Image</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($badges as $badge): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($badge['id']) ?></td>
                                        <td><?= htmlspecialchars($badge['name']) ?></td>
                                        <td>
                                            <?php if (!empty($badge['image'])): ?>
                                                <img src="<?= htmlspecialchars($badge['image']) ?>" alt="<?= htmlspecialchars($badge['name']) ?>" width="40">
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($badge['price']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="../controllers/admin-script.js"></script>
</body>

</html>