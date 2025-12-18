<?php
class Notification {
    private ?int $id;
    private string $content;
    private ?string $created_at;

    public function __construct(?int $id = null, string $content = "", ?string $created_at = null) {
        $this->id = $id;
        $this->content = $content;
        $this->created_at = $created_at;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function setContent(string $content): void {
        $this->content = $content;
    }

    public function getCreatedAt(): ?string {
        return $this->created_at;
    }

    public function setCreatedAt(string $created_at): void {
        $this->created_at = $created_at;
    }
}
?>

