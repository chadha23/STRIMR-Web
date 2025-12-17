<?php
class UserPoints {
    
    // Obtenir les points d'un utilisateur
    public static function getPoints($userId) {
        $pdo = Database::get();
        $stmt = $pdo->prepare("SELECT points FROM user_points WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result ? $result['points'] : 0;
    }
    
    // Ajouter des points à un utilisateur
    public static function addPoints($userId, $amount) {
        $pdo = Database::get();
        $pdo->prepare("INSERT INTO user_points (user_id, points) VALUES (?, ?) 
                       ON DUPLICATE KEY UPDATE points = points + ?")->execute([$userId, $amount, $amount]);
    }
    
    // Retirer des points à un utilisateur
    public static function deductPoints($userId, $amount) {
        $pdo = Database::get();
        $stmt = $pdo->prepare("UPDATE user_points SET points = GREATEST(points - ?, 0) WHERE user_id = ?");
        $stmt->execute([$amount, $userId]);
        return $stmt->rowCount() > 0;
    }
    
    // Transférer des points entre utilisateurs
    public static function transferPoints($fromUserId, $toUserId, $amount, $message = '') {
        $pdo = Database::get();
        
        try {
            $pdo->beginTransaction();
            
            // Vérifier que l'utilisateur a assez de points
            $currentPoints = self::getPoints($fromUserId);
            if ($currentPoints < $amount) {
                throw new Exception("Points insuffisants");
            }
            
            // Retirer les points du donateur
            self::deductPoints($fromUserId, $amount);
            
            // Ajouter les points au receveur
            self::addPoints($toUserId, $amount);
            
            // Enregistrer la transaction
            $stmt = $pdo->prepare("INSERT INTO transactions (from_user_id, to_user_id, amount, type, message) 
                                   VALUES (?, ?, ?, 'donation', ?)");
            $stmt->execute([$fromUserId, $toUserId, $amount, $message]);
            
            $pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $pdo->rollback();
            return false;
        }
    }
    
    // Historique des transactions d'un utilisateur
    public static function getTransactionHistory($userId, $limit = 50) {
        $pdo = Database::get();
        // Valider et nettoyer la limite pour éviter l'injection SQL
        $limit = intval($limit);
        if ($limit <= 0) $limit = 50;
        if ($limit > 1000) $limit = 1000; // Limite max pour éviter les requêtes trop lourdes
        
        $stmt = $pdo->prepare("
            SELECT t.*, 
                   u1.username as from_username, 
                   u2.username as to_username
            FROM transactions t 
            LEFT JOIN users u1 ON t.from_user_id = u1.id
            LEFT JOIN users u2 ON t.to_user_id = u2.id
            WHERE t.from_user_id = ? OR t.to_user_id = ?
            ORDER BY t.created_at DESC 
            LIMIT {$limit}
        ");
        $stmt->execute([$userId, $userId]);
        return $stmt->fetchAll();
    }
    
    // Simuler un achat de points
    public static function purchasePoints($userId, $amount, $cardInfo = []) {
        $pdo = Database::get();
        
        try {
            $pdo->beginTransaction();
            
            // Ajouter les points
            self::addPoints($userId, $amount);
            
            // Enregistrer la transaction d'achat
            $stmt = $pdo->prepare("INSERT INTO transactions (to_user_id, amount, type, message) 
                                   VALUES (?, ?, 'purchase', ?)");
            $stmt->execute([$userId, $amount, "Achat de $amount points"]);
            
            $pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $pdo->rollback();
            return false;
        }
    }
}
?>