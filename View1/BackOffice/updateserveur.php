<?php
require_once __DIR__ . '/../../controllers/ServerController.php';
require_once __DIR__ . '/../../models/Server.php';

$serverC = new ServerController();

if (isset($_GET['id'])) {
    $server = $serverC->showServer($_GET['id']);
}

if (isset($_POST['name']) && isset($_POST['id'])) {

    
    if (!empty($_POST['name'])) {
        $updatedServer = new Server($_POST['id'], $_POST['name']);
        $serverC->updateServer($updatedServer, $_POST['id']);
        header("Location: listServer.php");
        exit;
    } else {
        $erreur = "Le nom du serveur ne peut pas être vide.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Modifier Serveur</title>
</head>
<body>

<h1>Modifier le Serveur</h1>

<?php if (isset($erreur)) echo "<p style='color:red;'>$erreur</p>"; ?>

<form method="POST">
    <input type="hidden" name="id" value="<?php echo $server['id']; ?>">

    <label>Nom du serveur :</label>
    <input type="text" name="name" value="<?php echo $server['name']; ?>">

    <button type="submit">Modifier</button>
</form>

</body>
</html>
