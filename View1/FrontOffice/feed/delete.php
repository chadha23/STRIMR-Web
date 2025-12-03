<?php
require_once __DIR__ . '/../../../Controller/PostController.php';

$postController = new PostController();

if (isset($_GET['id'])) {
    $postController->deletePost($_GET['id']);
}

header('Location: index.php');
exit();
?>