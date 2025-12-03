<?php 
require_once __DIR__ . "/../Model/config.php";
require_once __DIR__ . "/../Model/Reacion Class";

class ReactionController {
    
    private function generateId($prefix = 'reaction') {
        return $prefix . '_' . time() . '_' . bin2hex(random_bytes(12));
    }
    
    // Toggle reaction (add if not exists, remove if exists)
    public function toggleReaction($post_id, $user_id, $type = 'heart') {
        $db = Config::getConnexion();
        try {
            $checkSql = "SELECT id FROM Reactions WHERE post_id = :post_id AND user_id = :user_id AND type = :type";
            $checkQuery = $db->prepare($checkSql);
            $checkQuery->execute([
                'post_id' => $post_id,
                'user_id' => $user_id,
                'type' => $type
            ]);
            
            $existing = $checkQuery->fetch();
            
            if ($existing) {
                $deleteSql = "DELETE FROM Reactions WHERE id = :id";
                $deleteQuery = $db->prepare($deleteSql);
                $deleteQuery->execute(['id' => $existing['id']]);
                return ['action' => 'removed', 'count' => $this->getReactionCount($post_id, $type)];
            } else {
                $reactionId = $this->generateId('reaction');
                $insertSql = "INSERT INTO Reactions (id, post_id, user_id, type, created_at) 
                             VALUES (:id, :post_id, :user_id, :type, NOW())";
                $insertQuery = $db->prepare($insertSql);
                $insertQuery->execute([
                    'id' => $reactionId,
                    'post_id' => $post_id,
                    'user_id' => $user_id,
                    'type' => $type
                ]);
                return ['action' => 'added', 'count' => $this->getReactionCount($post_id, $type)];
            }
        } catch (Exception $e) {
            error_log("Error in toggleReaction: " . $e->getMessage());
            return false;
        }
    }
    
    public function getReactionCount($post_id, $type = 'heart') {
        $sql = "SELECT COUNT(*) as count FROM Reactions WHERE post_id = :post_id AND type = :type";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'post_id' => $post_id,
                'type' => $type
            ]);
            $result = $query->fetch();
            return $result ? (int)$result['count'] : 0;
        } catch (Exception $e) {
            error_log("Error in getReactionCount: " . $e->getMessage());
            return 0;
        }
    }
    
    public function hasUserReacted($post_id, $user_id, $type = 'heart') {
        $sql = "SELECT id FROM Reactions WHERE post_id = :post_id AND user_id = :user_id AND type = :type";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'post_id' => $post_id,
                'user_id' => $user_id,
                'type' => $type
            ]);
            return $query->fetch() !== false;
        } catch (Exception $e) {
            error_log("Error in hasUserReacted: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAllReactionsForPost($post_id, $type = 'heart') {
        $sql = "SELECT 
                    Reactions.id,
                    Reactions.post_id,
                    Reactions.user_id,
                    Reactions.type,
                    Reactions.created_at,
                    IFNULL(Users.username, 'Unknown') AS username
                FROM Reactions
                LEFT JOIN Users ON Users.id = Reactions.user_id
                WHERE Reactions.post_id = :post_id AND Reactions.type = :type
                ORDER BY Reactions.created_at DESC";
        
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'post_id' => $post_id,
                'type' => $type
            ]);
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log("Error in getAllReactionsForPost: " . $e->getMessage());
            return [];
        }
    }
    
    public function deleteReaction($id) {
        $sql = "DELETE FROM Reactions WHERE id = :id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error in deleteReaction: " . $e->getMessage());
            return false;
        }
    }
}

?>

