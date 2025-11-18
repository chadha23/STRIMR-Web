<?php 

class Server {

    
    private ?int $id;
    private string $name;

    
    public function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }

   
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

    
    public function saisir($name) {
        $this->name = $name;
    }

    
    public function afficher() {
        echo "Server Name : " . $this->name;
    }
}

?>
