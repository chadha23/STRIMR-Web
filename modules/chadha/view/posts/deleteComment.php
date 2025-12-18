<?php
require_once __DIR__ . '/../../../Controller/CommentController.php';

$commentC = new CommentController();

if (isset($_GET['id'])) {
    $commentId = $_GET['id'];
    $postId = isset($_GET['post_id']) ? $_GET['post_id'] : null;
    
    if ($commentC->deleteComment($commentId)) {
        // Redirect back to posts index
        header('Location: index.php');
        exit();
    } else {
        echo "Error deleting comment.";
    }
} else {
    header('Location: index.php');
    exit();
}
?>

