<?php
// Webhook pour MediaMTX - à appeler quand un stream commence/s'arrête
session_start();
require_once __DIR__ . '/Models/Channel.php';
require_once __DIR__ . '/Models/StreamSession.php';
require_once __DIR__ . '/Database.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST') {
    $action = $input['action'] ?? $_GET['action'] ?? '';
    $streamKey = $input['stream_key'] ?? $_GET['stream_key'] ?? '';
    
    switch ($action) {
        case 'start':
            // Stream commencé
            if ($streamKey) {
                Channel::setLive($streamKey, true);
                
                // Démarrer une session (ou utiliser l'existante)
                $channel = Channel::getByStreamKey($streamKey);
                if ($channel) {
                    $sessionId = StreamSession::startStream($channel['user_id'], $input['title'] ?? null);
                    $session = StreamSession::getActiveSession($channel['user_id']);
                    
                    error_log("Stream session for Channel {$channel['user_id']}, Session $sessionId");
                    
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Stream session active',
                        'session_id' => $sessionId,
                        'session_start' => $session['stream_start'] ?? date('Y-m-d H:i:s'),
                        'session_existing' => $session ? true : false
                    ]);
                } else {
                    echo json_encode(['success' => true, 'message' => 'Stream marked as live (no channel found)']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Missing stream key']);
            }
            break;
            
        case 'stop':
            // Stream arrêté
            if ($streamKey) {
                Channel::setLive($streamKey, false);
                
                // Terminer la session active
                $channel = Channel::getByStreamKey($streamKey);
                if ($channel) {
                    StreamSession::endActiveStream($channel['user_id']);
                    error_log("Stream session ended: Channel {$channel['user_id']}, Stream Key: $streamKey");
                    
                    // SÉCURITÉ : Générer automatiquement une nouvelle stream key
                    $newStreamKey = bin2hex(random_bytes(20));
                    error_log("🔐 [SÉCURITÉ AUTO] Génération nouvelle stream key pour user {$channel['user_id']}: $newStreamKey");
                    
                    // Mettre à jour avec la nouvelle clé sécurisée
                    try {
                        $pdo = Database::get();
                        $stmt = $pdo->prepare("UPDATE channels SET stream_key = ? WHERE user_id = ?");
                        $result = $stmt->execute([$newStreamKey, $channel['user_id']]);
                        
                        if ($result) {
                            error_log("✅ [SÉCURITÉ AUTO] Stream key mise à jour pour user {$channel['user_id']}");
                        }
                        
                        // Forcer un nettoyage des sessions zombies
                        $stmt = $pdo->prepare("UPDATE stream_sessions SET is_active = FALSE, stream_end = NOW() WHERE channel_id = ? AND is_active = TRUE");
                        $stmt->execute([$channel['user_id']]);
                        error_log("Forced cleanup of zombie sessions for channel {$channel['user_id']}");
                        
                    } catch(Exception $e) {
                        error_log("Error updating stream key or cleaning sessions: " . $e->getMessage());
                    }
                    
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Stream stopped and secured',
                        'session_end' => date('Y-m-d H:i:s'),
                        'security_updated' => true,
                        'new_key_preview' => substr($newStreamKey, 0, 8) . '...'
                    ]);
                } else {
                    error_log("Stream stopped but no channel found for key: $streamKey");
                    echo json_encode(['success' => true, 'message' => 'Stream marked as offline (no channel found)']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Missing stream key']);
            }
            break;
            
        case 'check':
            // Vérifier l'état actuel d'un stream
            if ($streamKey) {
                $isLive = Channel::checkStreamStatus($streamKey);
                echo json_encode(['success' => true, 'is_live' => $isLive]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Missing stream key']);
            }
            break;
            
        case 'check_all':
            // Vérifier tous les streams marqués comme live
            $pdo = Database::get();
            $stmt = $pdo->query("SELECT stream_key FROM channels WHERE is_live = 1");
            $streams = $stmt->fetchAll();
            
            $results = [];
            foreach ($streams as $stream) {
                $isLive = Channel::checkStreamStatus($stream['stream_key']);
                $results[] = [
                    'stream_key' => $stream['stream_key'],
                    'is_live' => $isLive
                ];
            }
            
            echo json_encode(['success' => true, 'results' => $results]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
    }
} else {
    // GET - vérification simple
    $streamKey = $_GET['stream_key'] ?? '';
    if ($streamKey) {
        $isLive = Channel::checkStreamStatus($streamKey);
        echo json_encode(['success' => true, 'stream_key' => $streamKey, 'is_live' => $isLive]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing stream key']);
    }
}
?>