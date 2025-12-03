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

        if($postController->updatePost($p, $id)){
            header('Location: index.php');
            exit();
        } else {
            $error = 'Erreur lors de la mise à jour';
        }
    } else {
        $error = 'Veuillez remplir tous les champs';
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un post</title>
</head>
<body>

    <h2>Modifier un post</h2>

    <?php if ($error) echo '<p style="color:red;">' . $error . '</p>'; ?>

    <form action="" method="POST">

        <input type="hidden" name="id" value="<?php echo $id; ?>">

        <label>Contenu :</label><br>

        <textarea name="content" rows="4"><?php echo htmlspecialchars($post['content']); ?></textarea><br>

        <button type="submit">Mettre à jour</button>

    </form>

</body>
</html>

