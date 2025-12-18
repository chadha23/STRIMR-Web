<?php
class ChatMessage {
    
    // Envoyer un message
    public static function sendMessage($channelId, $userId, $username, $message) {
        $pdo = Database::get();
        
        // Validation basique
        $message = trim($message);
        if (empty($message) || strlen($message) > 500) {
            return false;
        }
        
        // Filtrage simple des mots interdits
        $badWords = ['merde', 'putain', 'connard', 'salope']; // Ajoutez selon vos besoins
        foreach ($badWords as $word) {
            $message = str_ireplace($word, str_repeat('*', strlen($word)), $message);
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO chat_messages (channel_id, user_id, username, message, timestamp) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        if ($stmt->execute([$channelId, $userId, $username, $message])) {
            // Récupérer l'ID et le timestamp du message inséré
            $messageId = $pdo->lastInsertId();
            $getStmt = $pdo->prepare("SELECT id, timestamp FROM chat_messages WHERE id = ?");
            $getStmt->execute([$messageId]);
            $messageData = $getStmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'id' => $messageData['id'],
                'timestamp' => $messageData['timestamp']
            ];
        }
        
        return false;
    }
    
    // Récupérer les messages récents
    public static function getRecentMessages($channelId, $limit = 50) {
        $pdo = Database::get();
        
        // Forcer le cast en entier pour éviter l'erreur SQL
        $limit = (int)$limit;
        
        $stmt = $pdo->prepare("
            SELECT cm.*, u.username as user_username 
            FROM chat_messages cm
            LEFT JOIN users u ON cm.user_id = u.id
            WHERE cm.channel_id = ?
            ORDER BY cm.timestamp DESC
            LIMIT $limit
        ");
        
        $stmt->execute([$channelId]);
        $messages = $stmt->fetchAll();
        
        return array_reverse($messages); // Plus récents en bas
    }
    
    // Récupérer nouveaux messages depuis un timestamp
    public static function getNewMessages($channelId, $since) {
        $pdo = Database::get();
        
        $stmt = $pdo->prepare("
            SELECT cm.*, u.username as user_username 
            FROM chat_messages cm
            LEFT JOIN users u ON cm.user_id = u.id
            WHERE cm.channel_id = ? AND cm.timestamp > ?
            ORDER BY cm.timestamp ASC
        ");
        
        $stmt->execute([$channelId, $since]);
        return $stmt->fetchAll();
    }
    
    // Signaler un message
    public static function reportMessage($messageId, $reportedBy, $reason) {
        $pdo = Database::get();
        
        $stmt = $pdo->prepare("
            UPDATE chat_messages 
            SET reported_by = ?, report_reason = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([$reportedBy, $reason, $messageId]);
    }
    
    // Supprimer un message (modération)
    public static function deleteMessage($messageId) {
        $pdo = Database::get();
        
        $stmt = $pdo->prepare("UPDATE chat_messages SET is_deleted = 1 WHERE id = ?");
        return $stmt->execute([$messageId]);
    }
    
    // Nettoyer les vieux messages (>24h)
    public static function cleanupOldMessages() {
        $pdo = Database::get();
        
        $stmt = $pdo->prepare("
            DELETE FROM chat_messages 
            WHERE timestamp < DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        
        return $stmt->execute();
    }
}
?>