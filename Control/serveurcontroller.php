<?php 
require __DIR__ . "/../models/config.php";
require __DIR__ . "/../models/Server.php";


class ServerController {


    function getAllServers() {
        $sql = "SELECT * FROM servers";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll();
        } 
        catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

   
    function listServers() {
        return $this->getAllServers();
    }

   
    function addServer($server) {
        $sql = "INSERT INTO servers(id, name) VALUES (NULL, :name)";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->bindValue('name', $server->getName());
            $query->execute();
        } 
        catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

    
    function updateServer($server, $id) {
        $sql = "UPDATE servers SET name = :name WHERE id = :id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id,
                'name' => $server->getName()
            ]);
        } 
        catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }


    function deleteServer($id) {
        $sql = "DELETE FROM servers WHERE id = :id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
        } 
        catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

    
    function showServer($id) {
        $sql = "SELECT * FROM servers WHERE id = :id";
        $db = config::getConnexion();

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
