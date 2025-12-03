<?php
require_once __DIR__ . '/../../../Controller/ReactionController.php';

$reactionC = new ReactionController();

if (isset($_GET['id'])) {
    $reactionId = $_GET['id'];
    $postId = isset($_GET['post_id']) ? $_GET['post_id'] : null;
    
    if ($reactionC->deleteReaction($reactionId)) {
        header('Location: index.php');
        exit();
    } else {
        echo "Error deleting reaction.";
    }
} else {
    header('Location: index.php');
    exit();
}
?>

