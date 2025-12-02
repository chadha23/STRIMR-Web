<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Message.php';

class MessageController {

    // ==============================
    // GET ALL MESSAGES
    // ==============================
    function getAllMessages() {
        $sql = "SELECT * FROM message";
        $db = DatabaseConfig::getConnexion();  // ✔ correcte

        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll();
        } 
        catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

    function listMessages() {
        return $this->getAllMessages();
    }

    // ==============================
    // GET MESSAGES BY SERVER
    // ==============================
    function getMessagesByServer($server_id) {
        $sql = "SELECT * FROM message WHERE server_id = :server_id ORDER BY created_at ASC";
        $db = DatabaseConfig::getConnexion();  // ✔ correcte

        try {
            $query = $db->prepare($sql);
            $query->execute(['server_id' => $server_id]);
            return $query->fetchAll();
        }
        catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

    // ==============================
    // ADD MESSAGE
    // ==============================
    function addMessage($message) {
    $sql = "INSERT INTO message (server_id, content) VALUES (:server_id, :content)";
    $db = DatabaseConfig::getConnexion();

    try {
        $query = $db->prepare($sql);
        $query->execute([
            'server_id' => (int)$message->getServerId(),
            'content' => $message->getContent()
        ]);
        echo "Message ajouté avec succès ! ID=" . $db->lastInsertId();
    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
    }
}


    // ==============================
    // UPDATE MESSAGE
    // ==============================
    function updateMessage($message, $id) {
        $sql = "UPDATE message SET content = :content WHERE id = :id";
        $db = DatabaseConfig::getConnexion();  // ✔ correcte

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id,
                'content' => $message->getContent()
            ]);
        }
        catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

    // ==============================
    // DELETE MESSAGE
    // ==============================
    function deleteMessage($id) {
        $sql = "DELETE FROM message WHERE id = :id";
        $db = DatabaseConfig::getConnexion();  // ✔ correcte

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
        }
        catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

    // ==============================
    // SHOW MESSAGE BY ID
    // ==============================
    function showMessage($id) {
        $sql = "SELECT * FROM message WHERE id = :id";
        $db = DatabaseConfig::getConnexion();  // ✔ correcte

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch();
        }
        catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }
}

?>

