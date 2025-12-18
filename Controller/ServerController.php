<?php
require_once __DIR__ . "/../Model/config.php";
require_once __DIR__ . "/../Model/Server.php";

class ServerController
{
    public function getAllServers()
    {
        $sql = "SELECT * FROM servers";
        $db  = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log("Error in getAllServers: " . $e->getMessage());
            return [];
        }
    }

    public function listServers()
    {
        return $this->getAllServers();
    }

    public function addServer(Server $server): bool
    {
        $sql = "INSERT INTO servers (id, name) VALUES (NULL, :name)";
        $db  = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->bindValue(':name', $server->getName());
            $query->execute();
            return true;
        } catch (Exception $e) {
            error_log("Error in addServer: " . $e->getMessage());
            return false;
        }
    }

    public function updateServer(Server $server, int $id): bool
    {
        $sql = "UPDATE servers SET name = :name WHERE id = :id";
        $db  = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':id'   => $id,
                ':name' => $server->getName(),
            ]);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error in updateServer: " . $e->getMessage());
            return false;
        }
    }

    public function deleteServer(int $id): bool
    {
        $sql = "DELETE FROM servers WHERE id = :id";
        $db  = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $id]);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error in deleteServer: " . $e->getMessage());
            return false;
        }
    }

    public function showServer(int $id)
    {
        $sql = "SELECT * FROM servers WHERE id = :id";
        $db  = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            error_log("Error in showServer: " . $e->getMessage());
            return null;
        }
    }
}

?>


