<?php
class Post {
    private $id;
    private $author_id;
    private $content;
    private $image_url;
    private $created_at;

    public function __construct($id, $author_id, $content, $created_at, $image_url = null) {
        $this->id = $id;
        $this->author_id = $author_id;
        $this->content = $content;
        $this->created_at = $created_at;
        $this->image_url = $image_url;
    }

    public function getId() { return $this->id; }
    public function getAuthorId() { return $this->author_id; }
    public function getContent() { return $this->content; }
    public function getImageUrl() { return $this->image_url; }
    public function getCreatedAt() { return $this->created_at; }

    public function setAuthorId($author_id) { $this->author_id = $author_id; }
    public function setContent($content) { $this->content = $content; }
    public function setImageUrl($image_url) { $this->image_url = $image_url; }
    public function setId($id) { $this->id = $id; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
}
?>
