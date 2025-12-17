<?php
class DashboardController extends Controller {

    public function index() {
        // Définir un timeout raisonnable
        set_time_limit(45);
        
        // Nettoyer les streams inactifs d'abord
        Channel::cleanupInactiveStreams();
        
        // Forcer la détection des streams actifs
        $channels = Channel::getLive();
        $userPoints = UserPoints::getPoints($_SESSION['user_id']);
        
        $this->view('dashboard', compact('channels', 'userPoints'));
    }
    
    // Forcer la vérification de tous les streams
    private function forceCheckAllStreams() {
        $pdo = Database::get();
        $stmt = $pdo->query("SELECT stream_key FROM channels");
        $streams = $stmt->fetchAll();
        
        foreach ($streams as $stream) {
            Channel::checkStreamStatus($stream['stream_key']);
        }
    }

    public function goLive() {
        $channel = Channel::getOrCreate($_SESSION['user_id']);
        $this->view('go-live', compact('channel'));
    }
}