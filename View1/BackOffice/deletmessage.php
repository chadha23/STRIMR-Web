<?php
require_once __DIR__ . '/../../controllers/MessageController.php';

if (isset($_GET['id'])) {
    $messageC = new MessageController();
    $messageC->deleteMessage($_GET['id']);
}

header("Location: listMessages.php");
exit;
