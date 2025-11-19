<?php
require_once __DIR__ . '/../Models/Channel.php';
class DashboardController {
    public function index() {
        if (!isset($_SESSION['user_id'])) return redirect('');
        $channels = Channel::getLive();
        require __DIR__ . '/../Views/dashboard.php';
    }
    public function goLive() {
        if (!isset($_SESSION['user_id'])) return redirect('');
        $channel = Channel::getOrCreate($_SESSION['user_id']);
        require __DIR__ . '/../Views/go-live.php';
    }
}