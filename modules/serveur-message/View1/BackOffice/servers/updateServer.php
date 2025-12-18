<?php
require_once __DIR__ . '/../../../Controller/ServerController.php';
require_once __DIR__ . '/../../../Model/Server.php';

$serverC = new ServerController();
$error   = '';
$server  = null;

if (isset($_GET['id'])) {
    $server = $serverC->showServer((int)$_GET['id']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['name'])) {
    $id   = (int)$_POST['id'];
    $name = trim($_POST['name']);

    if ($name !== '') {
        $updated = new Server($id, $name);
        if ($serverC->updateServer($updated, $id)) {
            header('Location: index.php');
            exit();
        } else {
            $error = 'Error updating server. Please try again.';
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
    <title>Edit Server - Admin Panel</title>
    <link rel="stylesheet" href="../admin-styles.css">
</head>
<body>
    <div class="admin-container">
        <main class="admin-main" style="margin-left:0; padding:40px;">
            <h1 class="page-title">Edit Server</h1>
            <p class="page-subtitle">Update server information</p>

            <?php if (!empty($error)): ?>
                <p style="color:#e0245e; margin-bottom:16px;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <?php if ($server): ?>
                <form method="POST" style="max-width:400px; background:#1e2230; padding:24px; border-radius:12px;">
                    <input type="hidden" name="id" value="<?php echo $server['id']; ?>">

                    <label for="name" style="display:block; margin-bottom:8px; color:#fff;">Server Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?php echo htmlspecialchars($server['name']); ?>"
                        class="admin-input"
                        style="width:100%; padding:10px; margin-bottom:16px; border-radius:6px; border:1px solid #444;"
                        required
                    >

                    <button type="submit" class="add-btn" style="border:none; cursor:pointer;">Save Changes</button>
                    <a href="index.php" style="margin-left:12px; color:#a0a0a0; text-decoration:none;">Cancel</a>
                </form>
            <?php else: ?>
                <p style="color:#a0a0a0;">Server not found.</p>
                <a href="index.php" style="color:#5865f2; text-decoration:none;">Back to list</a>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>


