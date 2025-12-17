<?php

class Message {

    private ?int $id;
    private int $server_id;
    private string $content;

    public function __construct($id, $server_id, $content) {
        $this->id = $id;
        $this->server_id = (int)$server_id;   // <-- Correction ici
        $this->content = $content;
    }

    public function getId() {
        return $this->id;
    }

    public function getServerId() {
        return $this->server_id;
    }

    public function getContent() {
        return $this->content;
    }
}

?>
