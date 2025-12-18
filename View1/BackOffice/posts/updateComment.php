<?php

require_once __DIR__ . '/../../../Controller/CommentController.php';
require_once __DIR__ . '/../../../Model/Comment Class';

$commentController = new CommentController();

$error = '';

$comment = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
} elseif (isset($_POST['id'])) {
    $id = $_POST['id'];
} else {
    echo "No ID provided.";
    exit();
}

$comment = $commentController->getCommentById($id);

if (!$comment) {
    echo "Comment not found.";
    exit();
}

if (isset($_POST['content'])) {
    if (!empty($_POST['content'])) {
        // Admin update - no user_id check needed
        if ($commentController->updateComment($id, null, $_POST['content'])) {
            header('Location: index.php');
            exit();
        } else {
            $error = 'Error updating comment';
        }
    } else {
        $error = 'Please fill in all fields';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Comment - Admin Panel</title>
    <link rel="stylesheet" href="../admin-styles.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 30px;
            background: #2f3136;
            border-radius: 8px;
        }
        .form-container label {
            display: block;
            margin-bottom: 8px;
            color: #b9bbbe;
        }
        .form-container textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #40444b;
            border-radius: 4px;
            font-size: 14px;
            background: #36393f;
            color: #ffffff;
            font-family: inherit;
            min-height: 120px;
            resize: vertical;
            box-sizing: border-box;
        }
        .form-container button {
            padding: 10px 20px;
            background: #5865f2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .form-container button:hover {
            background: #4752c4;
        }
        .error {
            color: #f04747;
            margin-bottom: 15px;
        }
        .info {
            color: #b9bbbe;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2 class="sidebar-title">Admin Panel</h2>
                <div class="admin-avatar">A</div>
            </div>

            <nav class="sidebar-nav">
                <a href="../dashboard/index.php" class="nav-link">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="index.php" class="nav-link active">
                    <span class="nav-icon">🐦</span>
                    <span class="nav-text">Posts</span>
                </a>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1 class="page-title">Update Comment</h1>
                    <p class="page-subtitle">Update comment content</p>
                </div>
            </header>

            <div class="form-container">
                <?php if ($error) echo '<p class="error">' . $error . '</p>'; ?>
                
                <div class="info">
                    <strong>Author:</strong> <?php echo htmlspecialchars($comment['username']); ?><br>
                    <strong>Date:</strong> <?php echo $comment['created_at']; ?>
                </div>

                <form action="" method="POST" onsubmit="return validateForm()">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">

                    <label>Content:</label>
                    <textarea name="content" id="content" rows="4"><?php echo htmlspecialchars($comment['content']); ?></textarea>

                    <button type="submit">Update Comment</button>
                </form>
            </div>
        </main>
    </div>
    <script>
        function validateForm() {
            const content = document.getElementById('content').value.trim();
            if (content === '') {
                alert('Please fill in the content field');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>

