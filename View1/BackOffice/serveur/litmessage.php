<?php
require_once __DIR__ . '/../../controllers/MessageController.php';

$messageC = new MessageController();
$listeMessages = $messageC->getAllMessages();  // ← ta fonction existe déjà
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Management - Tous les messages</title>
    <style>
        body { font-family: Arial; background: #1a1a1a; color: #ddd; padding: 20px; }
        h1 { color: #7289da; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #2c2f33; }
        th { background: #23272a; padding: 12px; text-align: left; }
        td { padding: 10px 12px; border-bottom: 1px solid #3f4144; }
        tr:hover { background: #36393f; }
        .delete { color: #f04747; text-decoration: none; font-weight: bold; }
        .delete:hover { text-decoration: underline; }
        .no-messages { text-align: center; color: #888; padding: 40px; font-size: 18px; }
    </style>
</head>
<body>

    <h1>Message Management</h1>
    <p>Tous les messages envoyés par les utilisateurs (tous les serveurs)</p>

    <?php if (empty($listeMessages)): ?>
        <div class="no-messages">Aucun message pour le moment</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>CONTENU DU MESSAGE</th>
                    <th>SERVER ID</th>
                    <th>DATE / HEURE</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listeMessages as $msg): ?>
                    <tr>
                        <td><?= $msg['id'] ?></td>
                        <td style="max-width:500px; word-wrap:break-word;">
                            <?= htmlspecialchars($msg['content']) ?>
                        </td>
                        <td><?= $msg['server_id'] ?></td>
                        <td><?= date('d/m/Y H:i:s', strtotime($msg['created_at'])) ?></td>
                        <td>
                            <a href="deleteMessage.php?id=<?= $msg['id'] ?>" 
                               class="delete"
                               onclick="return confirm('Supprimer ce message ?')">
                               Supprimer
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</body>
</html>
