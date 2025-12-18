<?php
class StreamSession {
    
    // Démarrer une nouvelle session de stream - FORCE SESSION FRAÎCHE
    public static function startStream($channelId, $title = null) {
        $pdo = Database::get();
        
        // ÉTAPE 1: Fermer IMPÉRATIVEMENT toutes les anciennes sessions
        error_log("🔒 Fermeture forcée de toutes les anciennes sessions pour channel $channelId");
        $stmt = $pdo->prepare("UPDATE stream_sessions SET is_active = FALSE, stream_end = NOW() WHERE channel_id = ?");
        $stmt->execute([$channelId]);
        
        // ÉTAPE 2: Vérifier qu'aucune session n'est encore active (double-sécurité)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM stream_sessions WHERE channel_id = ? AND is_active = TRUE");
        $stmt->execute([$channelId]);
        $remainingActive = $stmt->fetchColumn();
        
        if ($remainingActive > 0) {
            error_log("⚠️ ALERTE: $remainingActive sessions encore actives après fermeture!");
            // Force la fermeture avec UPDATE brutal
            $pdo->prepare("UPDATE stream_sessions SET is_active = FALSE WHERE channel_id = ?")->execute([$channelId]);
        }
        
        // ÉTAPE 3: Créer une session complètement fraîche avec timestamp précis
        $freshTitle = $title ?: 'Session ' . date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("
            INSERT INTO stream_sessions (channel_id, title, is_active, stream_start) 
            VALUES (?, ?, TRUE, NOW())
        ");
        
        $success = $stmt->execute([$channelId, $freshTitle]);
        
        if ($success) {
            $newSessionId = $pdo->lastInsertId();
            
            // Vérifier que la session a bien été créée
            $stmt = $pdo->prepare("SELECT * FROM stream_sessions WHERE id = ?");
            $stmt->execute([$newSessionId]);
            $newSession = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($newSession) {
                error_log("🎬 SESSION FRAÎCHE créée: ID $newSessionId, Start: {$newSession['stream_start']}");
                return $newSessionId;
            } else {
                error_log("❌ Erreur: session créée mais non trouvée!");
                return false;
            }
        } else {
            error_log("❌ Erreur lors de la création de session pour channel $channelId");
            return false;
        }
    }
    
    // Terminer la session de stream active
    public static function endActiveStream($channelId) {
        $pdo = Database::get();
        
        // Vérifier d'abord s'il y a des sessions actives
        $checkStmt = $pdo->prepare("SELECT id, stream_start FROM stream_sessions WHERE channel_id = ? AND is_active = TRUE");
        $checkStmt->execute([$channelId]);
        $activeSessions = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("🔍 [SESSION] Tentative fermeture pour channel $channelId");
        error_log("🔍 [SESSION] Sessions actives trouvées: " . count($activeSessions));
        
        if (empty($activeSessions)) {
            error_log("⚠️ [SESSION] Aucune session active à fermer pour channel $channelId");
            return true; // Pas d'erreur, juste rien à faire
        }
        
        foreach ($activeSessions as $session) {
            error_log("📋 [SESSION] Session ID {$session['id']} - Start: {$session['stream_start']}");
        }
        
        $stmt = $pdo->prepare("
            UPDATE stream_sessions 
            SET stream_end = NOW(), is_active = FALSE 
            WHERE channel_id = ? AND is_active = TRUE
        ");
        
        $result = $stmt->execute([$channelId]);
        $affectedRows = $stmt->rowCount();
        
        if ($result) {
            error_log("✅ [SESSION] $affectedRows sessions fermées pour channel $channelId");
        } else {
            error_log("❌ [SESSION] Erreur lors de la fermeture des sessions pour channel $channelId");
        }
        
        return $result;
    }
    
    // Obtenir la session active pour un canal - SEULEMENT les sessions récentes
    public static function getActiveSession($channelId) {
        $pdo = Database::get();
        
        // Ne récupérer que les sessions actives de moins de 24h pour éviter les anciennes sessions zombies
        $stmt = $pdo->prepare("
            SELECT * FROM stream_sessions 
            WHERE channel_id = ? AND is_active = TRUE 
              AND stream_start > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ORDER BY stream_start DESC 
            LIMIT 1
        ");
        
        $stmt->execute([$channelId]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Log pour debugging
        if ($session) {
            error_log("✅ Session active trouvée: ID {$session['id']}, Start: {$session['stream_start']}");
        } else {
            error_log("❌ Aucune session active récente pour channel $channelId");
        }
        
        return $session;
    }
    
    // Obtenir les messages/donations SEULEMENT de la session active
    public static function getSessionActivity($channelId, $limit = 50) {
        $session = self::getActiveSession($channelId);
        if (!$session) {
            return ['messages' => [], 'donations' => [], 'session' => null];
        }
        
        $pdo = Database::get();
        $limit = (int)$limit; // Forcer le cast
        
        // Messages de la session courante
        if ($session['stream_end']) {
            // Session fermée - entre start et end
            $stmt = $pdo->prepare("
                SELECT cm.*, u.username as user_username 
                FROM chat_messages cm
                LEFT JOIN users u ON cm.user_id = u.id
                WHERE cm.channel_id = ? 
                  AND cm.timestamp >= ?
                  AND cm.timestamp <= ?
                ORDER BY cm.timestamp DESC
                LIMIT $limit
            ");
            $stmt->execute([$channelId, $session['stream_start'], $session['stream_end']]);
        } else {
            // Session active - depuis start seulement
            $stmt = $pdo->prepare("
                SELECT cm.*, u.username as user_username 
                FROM chat_messages cm
                LEFT JOIN users u ON cm.user_id = u.id
                WHERE cm.channel_id = ? 
                  AND cm.timestamp >= ?
                ORDER BY cm.timestamp DESC
                LIMIT $limit
            ");
            $stmt->execute([$channelId, $session['stream_start']]);
        }
        $messages = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
        
        // Donations de la session courante - CORRIGER LE MAPPING
        // D'abord convertir channel_id en user_id car donations utilisent to_user_id
        $stmt = $pdo->prepare("SELECT user_id FROM channels WHERE id = ?");
        $stmt->execute([$channelId]);
        $channelOwnerId = $stmt->fetchColumn();
        
        if (!$channelOwnerId) {
            // Si pas de mapping trouvé, pas de donations
            $donations = [];
        } else {
            if ($session['stream_end']) {
                // Session fermée
                $stmt = $pdo->prepare("
                    SELECT t.id, t.from_user_id as user_id, t.amount, t.message, t.created_at, u.username
                    FROM transactions t
                    JOIN users u ON t.from_user_id = u.id
                    WHERE t.to_user_id = ? 
                      AND t.type = 'donation'
                      AND t.created_at >= ?
                      AND t.created_at <= ?
                    ORDER BY t.created_at DESC
                    LIMIT $limit
                ");
                $stmt->execute([$channelOwnerId, $session['stream_start'], $session['stream_end']]);
            } else {
                // Session active
                $stmt = $pdo->prepare("
                    SELECT t.id, t.from_user_id as user_id, t.amount, t.message, t.created_at, u.username
                    FROM transactions t
                    JOIN users u ON t.from_user_id = u.id
                    WHERE t.to_user_id = ? 
                      AND t.type = 'donation'
                      AND t.created_at >= ?
                    ORDER BY t.created_at DESC
                    LIMIT $limit
                ");
                $stmt->execute([$channelOwnerId, $session['stream_start']]);
            }
        }
        $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'messages' => $messages,
            'donations' => $donations,
            'session' => $session
        ];
    }

    // NOUVEAU: Obtenir seulement les nouvelles activités depuis un timestamp
    public static function getNewSessionActivity($channelId, $since, $limit = 20) {
        $session = self::getActiveSession($channelId);
        if (!$session) {
            return ['messages' => [], 'donations' => [], 'session' => null];
        }

        $pdo = Database::get();
        $limit = (int)$limit;

        // CRITICAL FIX: Normaliser le format de $since en format SQL
        try {
            $sinceDateTime = new DateTime($since);
            $sinceSQLFormat = $sinceDateTime->format('Y-m-d H:i:s');
        } catch(Exception $e) {
            // Fallback si format invalide
            $sinceSQLFormat = $since;
        }
        
        // Debug: log des paramètres avec format normalisé
        error_log("getNewSessionActivity - ChannelId: $channelId, Since original: $since, Since SQL: $sinceSQLFormat, Session start: " . $session['stream_start']);

        // LOGIC FIX: Une seule condition de timestamp pour éviter les conflits
        $effectiveSince = max($session['stream_start'], $sinceSQLFormat);
        
        $stmt = $pdo->prepare("
            SELECT cm.*, u.username as user_username 
            FROM chat_messages cm
            LEFT JOIN users u ON cm.user_id = u.id
            WHERE cm.channel_id = ? 
              AND cm.timestamp > ?
            ORDER BY cm.timestamp ASC
            LIMIT $limit
        ");
        
        $stmt->execute([$channelId, $effectiveSince]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // IMPORTANTE: Log détaillé pour débugger
        error_log("SQL Messages - ChannelId: $channelId, Session start: " . $session['stream_start'] . ", Since original: $since, Since SQL: $sinceSQLFormat, Effective since: $effectiveSince");
        error_log("getNewSessionActivity - Found " . count($messages) . " messages newer than $effectiveSince");

        // Donations - CORRIGER MAPPING channel_id vers user_id
        $stmt = $pdo->prepare("SELECT user_id FROM channels WHERE id = ?");
        $stmt->execute([$channelId]);
        $channelOwnerId = $stmt->fetchColumn();
        
        if (!$channelOwnerId) {
            $donations = [];
            error_log("ERREUR: Aucun owner trouvé pour channel_id $channelId");
        } else {
            $effectiveSinceDonations = max($session['stream_start'], $sinceSQLFormat);
            
            $stmt = $pdo->prepare("
                SELECT t.id, t.from_user_id as user_id, t.amount, t.message, t.created_at, u.username
                FROM transactions t
                JOIN users u ON t.from_user_id = u.id
                WHERE t.to_user_id = ? 
                  AND t.type = 'donation'
                  AND t.created_at > ?
                ORDER BY t.created_at ASC
                LIMIT $limit
            ");
            
            $stmt->execute([$channelOwnerId, $effectiveSinceDonations]);
            $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Debug détaillé pour donations
            error_log("SQL Donations - ChannelId: $channelId -> OwnerId: $channelOwnerId, Session start: " . $session['stream_start'] . ", Since original: $since, Since SQL: $sinceSQLFormat, Effective since: $effectiveSinceDonations");  
            error_log("getNewSessionActivity - Found " . count($donations) . " donations newer than $effectiveSinceDonations");
        }

        return [
            'messages' => $messages,
            'donations' => $donations,
            'session' => $session
        ];
    }
}
?>