<?php

require_once __DIR__ . '/../../controllers/MessageController.php';

$messageC = new MessageController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['content']) && isset($_POST['server_id'])) {

        if (!empty($_POST['content'])) {

            // Créer l'objet message
            $message = new Message(
                null,
                $_POST['server_id'],
                $_POST['content']
            );

            // Appel au controller
            $messageC->addMessage($message);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un message</title>
</head>
<body>

    <h2>Ajouter un message</h2>

    <form method="POST">
        
        <!-- Quel serveur ? -->
        <label>Serveur</label>
        <select name="server_id">
            <option value="1">Serveur 1</option>
            <option value="2">Serveur 2</option>
            <!-- Tu peux mettre les serveurs dynamiquement -->
        </select>
        <br><br>

        <!-- Message -->
        <label>Message</label>
        <textarea name="content" rows="4" cols="40"></textarea>

        <br><br>
        <button type="submit">Envoyer</button>
    </form>

</body>
</html>
