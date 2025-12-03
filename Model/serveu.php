<?php

require_once __DIR__ . "/../config.php";

class Server {

    private ?int $id;
    private string $name;

    public function __construct($id = null, $name = "") {
        $this->id = $id;
        $this->name = $name;
    }

    // ─────────── Getters / Setters ───────────

    public function getId() {
        return $this->id;
    }
    public function setId($id) {
        $this->id = $id;
    }

    public function getName() {
        return $this->name;
    }
    public function setName($name) {
        $this->name = $name;
    }

    // ─────────── Méthodes Métier ───────────

    public static function getAll() {
        $db = DatabaseConfig::getConnexion();
        $query = $db->query("SELECT * FROM servers");
        return $query->fetchAll(PDO::FETCH_CLASS, 'Server');
    }

    public static function searchByName($keyword) {
        $db = DatabaseConfig::getConnexion();
        $query = $db->prepare("SELECT * FROM servers WHERE name LIKE ?");
        $query->execute(["%$keyword%"]);
        return $query->fetchAll(PDO::FETCH_CLASS, 'Server');
    }

    // (OPTIONNEL)
    public function afficher() {
        echo "Server Name : " . $this->name;
    }
}

?>
