<?php

require_once __DIR__ . '/../../controllers/ServerController.php';

$serverC = new ServerController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['name'])) {

        if (!empty($_POST['name'])) {

            
            $serveur = new Server(null, $_POST['name']);

           
            $serverC->addServer($serveur);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Serveur</title>
</head>
<body>

    <form method="POST">
        <label>Nom du serveur</label>
        <input type="text" name="name">

        <button type="submit">Ajouter</button>
    </form>

</body>
</html>
