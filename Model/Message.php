<?php

class Message {

    private ?int $id;
    private int $server_id;
    private string $content;

    public function __construct(?int $id, int $server_id, string $content)
    {
        $this->id = $id;
        $this->server_id = $server_id;
        $this->content = $content;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getServerId(): int
    {
        return $this->server_id;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}

?>


