<?php
class Channel {

    public static function getOrCreate($userId) {
        $pdo = Database::get();
        $stmt = $pdo->prepare("SELECT c.*, u.username FROM channels c JOIN users u ON c.user_id = u.id WHERE c.user_id = ?");
        $stmt->execute([$userId]);
        $ch = $stmt->fetch();

        if (!$ch) {
            $slug = strtolower($_SESSION['username']);
            $key = bin2hex(random_bytes(20));
            $pdo->prepare("INSERT INTO channels (user_id, slug, stream_key, is_live, viewer_count) VALUES (?, ?, ?, 0, 0)")
                ->execute([$userId, $slug, $key]);
            return self::getBySlug($slug);
        }

        // Ne pas marquer comme live automatiquement - MediaMTX le fera via webhook
        return $ch;
    }

    public static function getBySlug($slug) {
        $pdo = Database::get();
        $stmt = $pdo->prepare("SELECT c.*, u.username FROM channels c JOIN users u ON c.user_id = u.id WHERE c.slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    public static function getByStreamKey($key) {
        $pdo = Database::get();
        $stmt = $pdo->prepare("SELECT c.*, u.username FROM channels c JOIN users u ON c.user_id = u.id WHERE c.stream_key = ?");
        $stmt->execute([$key]);
        return $stmt->fetch();
    }

    public static function getLive() {
        $pdo = Database::get();
        
        // Nettoyer les streams inactifs d'abord
        self::cleanupInactiveStreams();
        
        // Maintenant récupérer les streams et les vérifier
        $stmt = $pdo->query("SELECT c.*, u.username FROM channels c JOIN users u ON c.user_id = u.id");
        $allChannels = $stmt->fetchAll();
        
        $activeStreams = [];
        
        foreach ($allChannels as $channel) {
            // Vérification stricte
            if (self::isStreamReallyActive($channel['stream_key'])) {
                // Si pas déjà marqué comme live, le marquer
                if (!$channel['is_live']) {
                    $pdo->prepare("UPDATE channels SET is_live = 1, viewer_count = GREATEST(viewer_count, 1) WHERE id = ?")
                        ->execute([$channel['id']]);
                    
                    // Recharger les données
                    $stmt = $pdo->prepare("SELECT c.*, u.username FROM channels c JOIN users u ON c.user_id = u.id WHERE c.id = ?");
                    $stmt->execute([$channel['id']]);
                    $channel = $stmt->fetch();
                }
                
                if ($channel) {
                    // Ajouter la preview image avec fallback plus robuste
                    $previewImage = StreamPreview::getRandomPreview($channel['id']);
                    
                    // Vérifier que le fichier existe vraiment
                    if ($previewImage && $previewImage !== 'assets/img/default-stream.svg') {
                        $fullPath = __DIR__ . '/../' . $previewImage;
                        if (!file_exists($fullPath)) {
                            $previewImage = 'assets/img/default-stream.svg';
                        }
                    } else {
                        $previewImage = 'assets/img/default-stream.svg';
                    }
                    
                    $channel['preview_image'] = $previewImage;
                    $activeStreams[] = $channel;
                }
            } else {
                // Si marqué comme live mais pas actif, corriger
                if ($channel['is_live']) {
                    self::setLive($channel['stream_key'], false);
                }
            }
        }
        
        return $activeStreams;
    }

    // +1 viewer (quand quelqu'un arrive) avec session tracking
    public static function incrementViewer($channelId, $sessionId = null, $userId = null) {
        $pdo = Database::get();
        
        // Créer un ID de session unique si pas fourni
        if (!$sessionId) {
            $sessionId = session_id() ?: bin2hex(random_bytes(16));
        }
        
        try {
            // Ajouter ou mettre à jour la session viewer
            $pdo->prepare("INSERT INTO viewer_sessions (channel_id, session_id, user_id) VALUES (?, ?, ?) 
                           ON DUPLICATE KEY UPDATE last_ping = CURRENT_TIMESTAMP, user_id = ?")->execute([$channelId, $sessionId, $userId, $userId]);
            
            // Recalculer le nombre réel de viewers actifs
            self::updateViewerCount($channelId);
            
        } catch (Exception $e) {
            // Fallback - juste incrémenter si la table n'existe pas encore
            $pdo->prepare("UPDATE channels SET viewer_count = viewer_count + 1 WHERE id = ?")->execute([$channelId]);
        }
    }

    // -1 viewer (quand quelqu'un part)
    public static function decrementViewer($channelId, $sessionId = null) {
        $pdo = Database::get();
        
        if (!$sessionId) {
            $sessionId = session_id();
        }
        
        try {
            // Supprimer la session
            $pdo->prepare("DELETE FROM viewer_sessions WHERE channel_id = ? AND session_id = ?")->execute([$channelId, $sessionId]);
            
            // Recalculer le nombre réel de viewers actifs
            self::updateViewerCount($channelId);
            
        } catch (Exception $e) {
            // Fallback - juste décrémenter si la table n'existe pas encore
            $pdo->prepare("UPDATE channels SET viewer_count = GREATEST(viewer_count - 1, 0) WHERE id = ?")->execute([$channelId]);
        }
    }
    
    // Mettre à jour le nombre de viewers en comptant les sessions actives
    public static function updateViewerCount($channelId) {
        $pdo = Database::get();
        
        try {
            // Nettoyer les sessions inactives (plus de 1 minute sans ping)
            $pdo->prepare("DELETE FROM viewer_sessions WHERE channel_id = ? AND last_ping < DATE_SUB(NOW(), INTERVAL 1 MINUTE)")->execute([$channelId]);
            
            // Compter les sessions actives
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM viewer_sessions WHERE channel_id = ?");
            $stmt->execute([$channelId]);
            $result = $stmt->fetch();
            $count = $result['count'];
            
            // Mettre à jour le compteur
            $pdo->prepare("UPDATE channels SET viewer_count = ? WHERE id = ?")->execute([$count, $channelId]);
            
        } catch (Exception $e) {
            // Si erreur, ne rien faire (table pas encore créée)
        }
    }
    
    // Ping pour maintenir la session active
    public static function pingViewerSession($channelId, $sessionId = null) {
        $pdo = Database::get();
        
        if (!$sessionId) {
            $sessionId = session_id() ?: bin2hex(random_bytes(16));
        }
        
        try {
            $pdo->prepare("UPDATE viewer_sessions SET last_ping = CURRENT_TIMESTAMP WHERE channel_id = ? AND session_id = ?")->execute([$channelId, $sessionId]);
        } catch (Exception $e) {
            // Ignore si table n'existe pas
        }
    }

    // Marquer un stream comme actif (sans heartbeat automatique)
    public static function setLive($streamKey, $isLive = true) {
        $pdo = Database::get();
        
        if ($isLive) {
            // Marquer comme live SEULEMENT si on détecte vraiment l'activité
            $stmt = $pdo->prepare("UPDATE channels SET is_live = 1 WHERE stream_key = ?");
            $stmt->execute([$streamKey]);
            
            error_log("[STRIMR] Stream {$streamKey} marqué comme LIVE");
        } else {
            // Marquer comme hors ligne et reset les compteurs
            $stmt = $pdo->prepare("UPDATE channels SET is_live = 0, viewer_count = 0 WHERE stream_key = ?");
            $stmt->execute([$streamKey]);
            
            error_log("[STRIMR] Stream {$streamKey} marqué comme OFFLINE");
        }
    }
    
    // Méthode pour marquer un canal comme offline par ID
    public static function setOffline($channelId) {
        $pdo = Database::get();
        $stmt = $pdo->prepare("UPDATE channels SET is_live = 0, viewer_count = 0 WHERE id = ?");
        $stmt->execute([$channelId]);
        
        error_log("[STRIMR] Channel {$channelId} marqué comme OFFLINE");
        return true;
    }
    
    // Forcer un stream en live (pour debug)
    public static function forceLive($streamKey) {
        $pdo = Database::get();
        $stmt = $pdo->prepare("UPDATE channels SET is_live = 1, viewer_count = GREATEST(viewer_count, 0), last_heartbeat = NOW() WHERE stream_key = ?");
        $stmt->execute([$streamKey]);
        
        error_log("[STRIMR] Stream {$streamKey} FORCÉ EN LIVE");
        return true;
    }
    
    // Vérifier si un stream est vraiment actif (méthode stricte)
    public static function isStreamReallyActive($streamKey) {
        // 1. Test HLS strict - doit avoir du contenu valide
        $manifestContent = self::getHLSContent($streamKey);
        if ($manifestContent && self::hasValidSegments($manifestContent)) {
            return true;
        }
        
        // 2. Vérifier via l'API MediaMTX si CE stream spécifique est actif
        if (self::checkStreamInMediaMTX($streamKey)) {
            return true;
        }
        
        return false;
    }
    
    // Vérifier si un stream spécifique est actif via l'API MediaMTX
    private static function checkStreamInMediaMTX($streamKey) {
        $apiUrl = "http://localhost:9997/v3/paths/list";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 || !$response) {
            return false;
        }
        
        $data = json_decode($response, true);
        if (!isset($data['items'])) {
            return false;
        }
        
        // Chercher si notre stream key est dans la liste des paths actifs
        $streamPath = "live/{$streamKey}";
        foreach ($data['items'] as $path) {
            if ($path['name'] === $streamPath) {
                error_log("[STRIMR] Stream {$streamKey} confirmé actif via MediaMTX API");
                return true;
            }
        }
        
        error_log("[STRIMR] Stream {$streamKey} NOT FOUND dans MediaMTX API");
        return false;
    }

    // Récupérer le contenu HLS avec vérification stricte
    private static function getHLSContent($streamKey) {
        $hlsUrl = "http://localhost:8888/live/{$streamKey}/index.m3u8";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $hlsUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'STRIMR/1.0');
        
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // STRICTEMENT HTTP 200 et contenu valide
        if ($httpCode !== 200) {
            error_log("[STRIMR] Stream {$streamKey}: HTTP {$httpCode} - pas de manifest");
            return false;
        }
        
        if (!empty($curlError)) {
            error_log("[STRIMR] Stream {$streamKey}: cURL error - {$curlError}");
            return false;
        }
        
        if (empty($content) || strlen($content) < 50) {
            error_log("[STRIMR] Stream {$streamKey}: contenu trop petit ou vide");
            return false;
        }
        
        return $content;
    }
    
    // Vérifier si le manifest HLS contient des segments vidéo récents
    private static function hasValidSegments($manifestContent) {
        // Doit contenir des segments .ts et être un manifest live
        if (strpos($manifestContent, '.ts') === false) {
            return false;
        }
        
        // Vérifier que c'est un stream live (pas VOD)
        if (strpos($manifestContent, '#EXT-X-ENDLIST') !== false) {
            return false; // C'est un fichier fini, pas un live
        }
        
        // Compter le nombre de segments
        $segmentCount = substr_count($manifestContent, '.ts');
        return $segmentCount >= 2; // Au moins 2 segments
    }
    
    // Vérifier si RTMP a des données actives
    private static function hasActiveRTMPData() {
        // Vérifier s'il y a vraiment des données qui transitent
        $connections = shell_exec('netstat -an | findstr ":1935" | findstr ESTABLISHED');
        
        if (empty($connections)) {
            return false;
        }
        
        // Vérifier qu'il y a plusieurs connexions (serveur + client)
        $connectionCount = substr_count($connections, 'ESTABLISHED');
        return $connectionCount >= 1;
    }
    public static function quickStreamCheck($streamKey) {
        // Test seulement le port principal avec timeout très court
        $hlsUrl = "http://localhost:8888/live/{$streamKey}/index.m3u8";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $hlsUrl);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1); // 1 seconde max
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ($httpCode === 200);
    }
    public static function checkStreamStatus($streamKey) {
        // Vérification rapide d'abord
        if (self::quickStreamCheck($streamKey)) {
            self::setLive($streamKey, true);
            return true;
        }
        
        // Si échec, vérifier RTMP rapidement
        $rtmpActive = self::checkRTMPConnection($streamKey);
        
        if ($rtmpActive) {
            self::setLive($streamKey, true);
            return true;
        }
        
        // Sinon, marquer comme hors ligne
        self::setLive($streamKey, false);
        return false;
    }
    
    // Vérifier la connexion RTMP active (version stricte)
    private static function checkRTMPConnection($streamKey) {
        // Vérifier s'il y a des connexions RTMP établies
        if (function_exists('shell_exec')) {
            $output = shell_exec('netstat -an | findstr ":1935" | findstr ESTABLISHED');
            
            if (!empty(trim($output))) {
                // Vérifier que le serveur MediaMTX accepte aussi les connexions
                $socket = @fsockopen('127.0.0.1', 8888, $errno, $errstr, 1);
                if ($socket) {
                    fclose($socket);
                    return true;
                }
            }
        }
        
        return false;
    }
    
    // Vérifier le manifest HLS sur différents ports (optimisé)
    private static function checkHLSManifest($streamKey) {
        // Tester seulement les ports principaux avec timeout court
        $ports = [8888, 8000];
        
        foreach ($ports as $port) {
            $hlsUrl = "http://localhost:{$port}/live/{$streamKey}/index.m3u8";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $hlsUrl);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1); // Réduit à 1 seconde
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                return true;
            }
        }
        
        return false;
    }
    
    // Vérifier si des segments HLS existent (optimisé)
    private static function checkHLSSegments($streamKey) {
        // Test rapide sur port principal seulement
        $manifestUrl = "http://localhost:8888/live/{$streamKey}/index.m3u8";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $manifestUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1); // Réduit à 1 seconde
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        
        $manifest = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && !empty($manifest)) {
            // Vérifier s'il y a des segments dans le manifest
            return (strpos($manifest, '.ts') !== false || strpos($manifest, '.m4s') !== false);
        }
        
        return false;
    }

    // Mettre à jour le heartbeat SEULEMENT si stream confirmé actif
    public static function updateHeartbeat($channelId) {
        $pdo = Database::get();
        
        // Vérifier d'abord que le stream est vraiment live
        $stmt = $pdo->prepare("SELECT stream_key, is_live FROM channels WHERE id = ?");
        $stmt->execute([$channelId]);
        $channel = $stmt->fetch();
        
        if (!$channel || !$channel['is_live']) {
            error_log("[STRIMR] Heartbeat ignoré pour channel {$channelId} - pas live");
            return;
        }
        
        // Vérifier que le stream est toujours réellement actif
        if (!self::isStreamReallyActive($channel['stream_key'])) {
            error_log("[STRIMR] Stream {$channel['stream_key']} n'est plus actif - arrêt automatique");
            self::setLive($channel['stream_key'], false);
            return;
        }
        
        // Seulement maintenant, mettre à jour le heartbeat
        if (self::columnExists('last_heartbeat')) {
            $pdo->prepare("UPDATE channels SET last_heartbeat = NOW() WHERE id = ?")->execute([$channelId]);
            error_log("[STRIMR] Heartbeat mis à jour pour channel {$channelId}");
        }
    }

    // Obtenir le nombre de viewers d'un channel
    public static function getViewerCount($channelId) {
        $pdo = Database::get();
        $stmt = $pdo->prepare("SELECT viewer_count FROM channels WHERE id = ?");
        $stmt->execute([$channelId]);
        $result = $stmt->fetch();
        return $result ? $result['viewer_count'] : 0;
    }

    // Nettoyer les streams inactifs (version stricte)
    public static function cleanupInactiveStreams() {
        $pdo = Database::get();
        
        // Méthode 1: Nettoyer par heartbeat si la colonne existe
        if (self::columnExists('last_heartbeat')) {
            $stmt = $pdo->prepare("
                UPDATE channels 
                SET is_live = 0, viewer_count = 0 
                WHERE is_live = 1 
                AND (last_heartbeat < DATE_SUB(NOW(), INTERVAL 1 MINUTE) OR last_heartbeat IS NULL)
            ");
            $stmt->execute();
            $cleaned = $stmt->rowCount();
            
            if ($cleaned > 0) {
                error_log("[STRIMR] {$cleaned} streams nettoyés par heartbeat");
            }
        }
        
        // Méthode 2: Vérifier chaque stream marqué comme live
        $stmt = $pdo->query("SELECT id, stream_key FROM channels WHERE is_live = 1");
        $liveChannels = $stmt->fetchAll();
        
        foreach ($liveChannels as $channel) {
            if (!self::isStreamReallyActive($channel['stream_key'])) {
                self::setLive($channel['stream_key'], false);
                error_log("[STRIMR] Stream {$channel['stream_key']} forcé offline - plus d'activité");
            }
        }
    }

    // Vérifier si une colonne existe dans la table channels
    private static function columnExists($columnName) {
        $pdo = Database::get();
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'channels' AND COLUMN_NAME = ?");
        $stmt->execute([$columnName]);
        $res = $stmt->fetch();
        return ($res && $res['cnt'] > 0);
    }
}