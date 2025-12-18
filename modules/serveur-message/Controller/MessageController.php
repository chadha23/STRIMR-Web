<?php
require_once __DIR__ . "/../Model/config.php";
require_once __DIR__ . "/../Model/Message.php";

class MessageController
{
    public function getAllMessages()
    {
        $sql = "SELECT * FROM message";
        $db  = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log("Error in getAllMessages: " . $e->getMessage());
            return [];
        }
    }

    public function listMessages()
    {
        return $this->getAllMessages();
    }

    public function getMessagesByServer(int $server_id)
    {
        $sql = "SELECT * FROM message WHERE server_id = :server_id ORDER BY created_at ASC";
        $db  = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([':server_id' => $server_id]);
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log("Error in getMessagesByServer: " . $e->getMessage());
            return [];
        }
    }

    public function addMessage(Message $message): bool
    {
        $sql = "INSERT INTO message (server_id, content, created_at) VALUES (:server_id, :content, NOW())";
        $db  = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':server_id' => $message->getServerId(),
                ':content'   => $message->getContent(),
            ]);
            return true;
        } catch (Exception $e) {
            error_log("Error in addMessage: " . $e->getMessage());
            return false;
        }
    }

    public function updateMessage(Message $message, int $id): bool
    {
        $sql = "UPDATE message SET content = :content WHERE id = :id";
        $db  = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':id'      => $id,
                ':content' => $message->getContent(),
            ]);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error in updateMessage: " . $e->getMessage());
            return false;
        }
    }

    public function deleteMessage(int $id): bool
    {
        $sql = "DELETE FROM message WHERE id = :id";
        $db  = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $id]);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error in deleteMessage: " . $e->getMessage());
            return false;
        }
    }

    public function showMessage(int $id)
    {
        $sql = "SELECT * FROM message WHERE id = :id";
        $db  = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            error_log("Error in showMessage: " . $e->getMessage());
            return null;
        }
    }
}

?>


