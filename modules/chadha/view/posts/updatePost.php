<?php

require_once __DIR__ . '/../../../Controller/PostController.php';
require_once __DIR__ . '/../../../Model/Classes User.php';
require_once __DIR__ . '/../../../Model/Comment Class';
require_once __DIR__ . '/../../../Model/Reacion Class';


$postController = new PostController();

$error = '';

$post = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
} elseif (isset($_POST['id'])) {
    $id = $_POST['id'];
} else {
    echo "Aucun ID fourni.";
    exit();
}

$post = $postController->showPost($id);

if (!$post) {
    echo "Post non trouvé.";
    exit();
}

if (isset($_POST['content'])) {
    if (!empty($_POST['content'])) {
        $p = new Post(
            $id,
            $post['author_id'],
            $_POST['content'],
            $post['created_at'],
            null
        );

        $postController->updatePost($p, $id);

        header('Location: index.php');
        exit();
    } else {
        $error = 'Veuillez remplir tous les champs';
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un post - Admin Panel</title>
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
                <a href="../dashboard/index.html" class="nav-link">
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
                    <h1 class="page-title">Modifier un post</h1>
                    <p class="page-subtitle">Modifier le contenu du post</p>
                </div>
            </header>

            <div class="form-container">
                <?php if ($error) echo '<p class="error">' . $error . '</p>'; ?>

                <form action="" method="POST">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">

                    <label>Contenu :</label>
                    <textarea name="content" rows="4"><?php echo htmlspecialchars($post['content']); ?></textarea>

                    <button type="submit">Mettre à jour</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

