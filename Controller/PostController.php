<?php 
require_once __DIR__ . "/../Model/config.php";
require_once __DIR__ . "/../Model/Classes User.php";
require_once __DIR__ . "/../Model/Comment Class";
require_once __DIR__ . "/../Model/Reacion Class";


class PostController {
    
    // Get all posts with user information
    public function getAllPosts() {
        $sql = "SELECT 
                    Posts.id,
                    Posts.author_id,
                    Posts.content,
                    Posts.created_at,
                    IFNULL(Users.username, 'Unknown') AS username
                FROM Posts
                LEFT JOIN Users ON Users.id = Posts.author_id
                ORDER BY Posts.created_at DESC";
        
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log("Error in getAllPosts: " . $e->getMessage());
            return [];
        }
    }

    // Generate unique ID for varchar(100) IDs
    private function generateId($prefix = 'post') {
        // Generate a unique ID: prefix_timestamp_randomhex (max ~50 chars, fits in varchar(100))
        return $prefix . '_' . time() . '_' . bin2hex(random_bytes(12));
    }
    
    // Add a new post
    public function addPost($post) {
        // Generate a unique ID for the post (varchar(100))
        $postId = $this->generateId('post');
        
        $sql = "INSERT INTO Posts (id, author_id, content, created_at) 
                VALUES (:id, :author_id, :content, NOW())";
        
        $db = Config::getConnexion();
        try {
            // Ensure user exists (for demo purposes)
            $this->ensureUserExists($post->getAuthorId());
            
            $query = $db->prepare($sql);
            $query->bindValue(':id', $postId);
            $query->bindValue(':author_id', $post->getAuthorId());
            $query->bindValue(':content', $post->getContent());
            $query->execute();
            
            return true;
        } catch (Exception $e) {
            error_log("Error in addPost: " . $e->getMessage());
            return false;
        }
    }

    // Update a post
    public function updatePost($post, $id) {
        $sql = "UPDATE Posts 
                SET content = :content 
                WHERE id = :id";
        
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id,
                'content' => $post->getContent()
            ]);
            
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error in addPost: " . $e->getMessage());
            return false;
        }
    }

    // Delete a post
    public function deletePost($id) {
        $sql = "DELETE FROM Posts WHERE id = :id";
        
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error in addPost: " . $e->getMessage());
            return false;
        }
    }

    // Get a single post by ID
    public function showPost($id) {
        $sql = "SELECT 
                    Posts.id,
                    Posts.author_id,
                    Posts.content,
                    Posts.created_at,
                    IFNULL(Users.username, 'Unknown') AS username
                FROM Posts
                LEFT JOIN Users ON Users.id = Posts.author_id
                WHERE Posts.id = :id";
        
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            error_log("Error in showPost: " . $e->getMessage());
            return null;
        }
    }

    // Helper function to ensure user exists
    private function ensureUserExists($userId) {
        // Check if user already exists
        $db = Config::getConnexion();
        $checkQuery = $db->prepare("SELECT id FROM Users WHERE id = :id");
        $checkQuery->execute(['id' => $userId]);
        
        if ($checkQuery->fetch()) {
            return; // User already exists
        }
        
        // User doesn't exist, create it
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