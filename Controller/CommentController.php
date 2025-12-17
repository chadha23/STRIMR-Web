<?php
require_once __DIR__ . "/../Model/config.php";
require_once __DIR__ . "/../Model/Comment Class";

class CommentController {
    // Generate unique ID for varchar(100) IDs
    private function generateId($prefix = 'comment') {
        return $prefix . '_' . time() . '_' . bin2hex(random_bytes(12));
    }

    // Add a new comment to a post
    public function addComment($post_id, $user_id, $content) {
        $db = Config::getConnexion();
        try {
            $this->ensureUserExists($user_id);

            $commentId = $this->generateId('comment');
            $sql = "INSERT INTO Comments (id, post_id, user_id, content, created_at)
                    VALUES (:id, :post_id, :user_id, :content, NOW())";
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $commentId,
                'post_id' => $post_id,
                'user_id' => $user_id,
                'content' => $content
            ]);

            return $commentId;
        } catch (Exception $e) {
            error_log("Error in addComment: " . $e->getMessage());
            return false;
        }
    }

    // Update an existing comment (only by the owner, or admin without user_id check)
    public function updateComment($id, $user_id, $content) {
        $db = Config::getConnexion();
        $sql = $user_id
            ? "UPDATE Comments SET content = :content WHERE id = :id AND user_id = :user_id"
            : "UPDATE Comments SET content = :content WHERE id = :id";
        
        $params = [
            'id' => $id,
            'content' => $content
        ];
        
        if ($user_id) {
            $params['user_id'] = $user_id;
        }
        
        try {
            $query = $db->prepare($sql);
            $query->execute($params);

            return $query->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error in updateComment: " . $e->getMessage());
            return false;
        }
    }
    
    // Get a single comment by ID with user info
    public function getCommentById($id) {
        $sql = "SELECT 
                    Comments.id,
                    Comments.post_id,
                    Comments.user_id,
                    Comments.content,
                    Comments.created_at,
                    IFNULL(Users.username, 'Unknown') AS username
                FROM Comments
                LEFT JOIN Users ON Users.id = Comments.user_id
                WHERE Comments.id = :id";
        
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            error_log("Error in getCommentById: " . $e->getMessage());
            return null;
        }
    }

    // Delete a comment (owners or back office can call without user_id)
    public function deleteComment($id, $user_id = null) {
        $db = Config::getConnexion();
        $sql = $user_id
            ? "DELETE FROM Comments WHERE id = :id AND user_id = :user_id"
            : "DELETE FROM Comments WHERE id = :id";

        $params = ['id' => $id];
        if ($user_id) {
            $params['user_id'] = $user_id;
        }

        try {
            $query = $db->prepare($sql);
            $query->execute($params);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error in deleteComment: " . $e->getMessage());
            return false;
        }
    }

    // Get all comments for a post with user info
    public function getCommentsForPost($post_id) {
        $sql = "SELECT 
                    Comments.id,
                    Comments.post_id,
                    Comments.user_id,
                    Comments.content,
                    Comments.created_at,
                    IFNULL(Users.username, 'Unknown') AS username
                FROM Comments
                LEFT JOIN Users ON Users.id = Comments.user_id
                WHERE Comments.post_id = :post_id
                ORDER BY Comments.created_at ASC";

        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['post_id' => $post_id]);
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log("Error in getCommentsForPost: " . $e->getMessage());
            return [];
        }
    }

    // Get number of comments for a post
    public function getCommentCount($post_id) {
        $sql = "SELECT COUNT(*) as count FROM Comments WHERE post_id = :post_id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['post_id' => $post_id]);
            $result = $query->fetch();
            return $result ? (int)$result['count'] : 0;
        } catch (Exception $e) {
            error_log("Error in getCommentCount: " . $e->getMessage());
            return 0;
        }
    }

    // Ensure the user exists for demo purposes
    private function ensureUserExists($userId) {
        $db = Config::getConnexion();
        $checkQuery = $db->prepare("SELECT id FROM Users WHERE id = :id");
        $checkQuery->execute(['id' => $userId]);

        if ($checkQuery->fetch()) {
            return;
        }

        $sql = "INSERT INTO Users (id, username, email, password_hash, created_at)
                VALUES (:id, :username, :email, :password_hash, NOW())";
        $query = $db->prepare($sql);
        $query->execute([
            'id' => $userId,
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password_hash' => 'dummyhash'
        ]);
    }
}

?>

