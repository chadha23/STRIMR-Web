<?php

require_once __DIR__ . '/../../controllers/ServerController.php';

$serverC = new ServerController();


$liste = $serverC->listServers();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des serveurs</title>
</head>

<body>

    <h1>Liste des Serveurs</h1>

    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Nom du serveur</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($liste as $s) { ?>
            <tr>
                <td><?php echo $s['id']; ?></td>
                <td><?php echo $s['name']; ?></td>

                <td>
                    
                    <a href="updateServer.php?id=<?php echo $s['id']; ?>">
                        Modifier
                    </a>

                    
                    <a href="deleteServer.php?id=<?php echo $s['id']; ?>">

                        Supprimer
                    </a>
                </td>
            </tr>
        <?php } ?>

    </table>

</body>
</html>
