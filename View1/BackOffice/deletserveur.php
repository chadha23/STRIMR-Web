<?php

require_once __DIR__ . '/../../controllers/ServerController.php';

$serverC = new ServerController();

if (isset($_GET['id'])) {
    $serverC->deleteServer($_GET['id']);
}

header("Location: listServer.php");
exit;

?>
