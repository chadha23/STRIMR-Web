<?php
require_once __DIR__ . '/../../../Controller/ServerController.php';
require_once __DIR__ . '/../../../Model/Server.php';

$serverC = new ServerController();
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['name']) && !empty(trim($_POST['name']))) {
        $server = new Server(null, trim($_POST['name']));
        if ($serverC->addServer($server)) {
            header('Location: index.php');
            exit();
        } else {
            $error = 'Error creating server. Please try again.';
        }
    } else {
        $error = 'Server name cannot be empty.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Server - Admin Panel</title>
    <link rel="stylesheet" href="../admin-styles.css">
</head>
<body>
    <div class="admin-container">
        <main class="admin-main" style="margin-left:0; padding:40px;">
            <h1 class="page-title">Add Server</h1>
            <p class="page-subtitle">Create a new community server</p>

            <?php if (!empty($error)): ?>
                <p style="color:#e0245e; margin-bottom:16px;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST" style="max-width:400px; background:#1e2230; padding:24px; border-radius:12px;">
                <label for="name" style="display:block; margin-bottom:8px; color:#fff;">Server Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="admin-input"
                    style="width:100%; padding:10px; margin-bottom:16px; border-radius:6px; border:1px solid #444;"
                    required
                >

                <button type="submit" class="add-btn" style="border:none; cursor:pointer;">Create Server</button>
                <a href="index.php" style="margin-left:12px; color:#a0a0a0; text-decoration:none;">Cancel</a>
            </form>
        </main>
    </div>
</body>
</html>


