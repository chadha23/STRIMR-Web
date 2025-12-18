<?php
require_once __DIR__ . '/../../../Controller/ServerController.php';

$serverC = new ServerController();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id > 0) {
        $serverC->deleteServer($id);
    }
}

header('Location: index.php');
exit();

?>


