<?php
class StreamController extends Controller {

    public function watch($param) {
        if (!$param) {
            http_response_code(404);
            echo "<h1 style='color:#fff;background:#000;text-align:center;padding:100px;'>404</h1>";
            exit;
        }

        // Debug
        error_log("[STRIMR] StreamController::watch appelé avec param: '{$param}'");

        // Recherche prioritaire par username (même si numérique)
        $channel = null;
        $pdo = Database::get();
        
        // Essayer d'abord par username
        $stmt = $pdo->prepare("SELECT c.*, u.username FROM channels c JOIN users u ON c.user_id = u.id WHERE u.username = ?");
        $stmt->execute([$param]);
        $channel = $stmt->fetch();
        error_log("[STRIMR] Recherche par username '{$param}': " . ($channel ? "trouvé ID {$channel['id']}" : "pas trouvé"));
        
        // Si pas trouvé par username et que c'est numérique, essayer par ID
        if (!$channel && is_numeric($param) && (int)$param == $param) {
            $stmt = $pdo->prepare("SELECT c.*, u.username FROM channels c JOIN users u ON c.user_id = u.id WHERE c.id = ?");
            $stmt->execute([(int)$param]);
            $channel = $stmt->fetch();
            error_log("[STRIMR] Recherche par ID {$param}: " . ($channel ? "trouvé" : "pas trouvé"));
        }
        
        // Si toujours pas trouvé, essayer par slug
        if (!$channel) {
            $channel = Channel::getBySlug($param);
            error_log("[STRIMR] Recherche par slug '{$param}': " . ($channel ? "trouvé" : "pas trouvé"));
        }

        if (!$channel) {
            error_log("[STRIMR] Channel introuvable pour param: '{$param}'");
            http_response_code(404);
            echo "<h1 style='color:#fff;background:#000;text-align:center;padding:100px;'>
                    Channel introuvable: '{$param}'
                  </h1>";
            exit;
        }

        // +1 viewer avec session tracking amélioré
        $sessionId = session_id() ?: bin2hex(random_bytes(16));
        $userId = $_SESSION['user_id'] ?? null;
        
        Channel::incrementViewer($channel['id'], $sessionId, $userId);

        $this->view('watch', compact('channel'));
    }
}