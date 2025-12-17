<?php
class Notification {
    public $id;
    public $content;
    public $created_at;

    public function __construct($id=null, $content="", $created_at=null) {
        $this->id = $id;
        $this->content = $content;
        $this->created_at = $created_at;
    }
}
?>
