<?php
require_once __DIR__ . '/../Models/Channel.php';
class StreamController {
    public function watch($slug = '') {
        if (!$slug) die("No channel");
        $channel = Channel::getBySlug($slug);
        if (!$channel) die("Offline");
        Channel::incrementViewer($slug);
        require __DIR__ . '/../Views/watch.php';
    }
}