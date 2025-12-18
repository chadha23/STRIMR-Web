<?php 
require_once __DIR__ . "/../Model/config.php";
require_once __DIR__ . "/../Model/User Class";

class UserController {
    
    // Get all users
    public function getAllUsers() {
        $sql = "SELECT 
                    id,
                    username,
                    email,
                    password,
                    created_at,
                    avatar_url,
                    biography
                FROM users
                ORDER BY created_at DESC";
        
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log("Error in getAllUsers: " . $e->getMessage());
            return [];
        }
    }
    
    // Get total user count
    public function getUserCount() {
        $sql = "SELECT COUNT(*) as total FROM users";
        
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute();
            $result = $query->fetch();
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error in getUserCount: " . $e->getMessage());
            return 0;
        }
    }
    
    // Get user by ID
    public function getUserById($userId) {
        $sql = "SELECT 
                    id,
                    username,
                    email,
                    password,
                    created_at,
                    avatar_url,
                    biography
                FROM users
                WHERE id = :id";
        
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $userId]);
            return $query->fetch();
        } catch (Exception $e) {
            error_log("Error in getUserById: " . $e->getMessage());
            return null;
        }
    }
    
    // Get users created in last N days
    public function getRecentUsers($days = 7) {
        $sql = "SELECT COUNT(*) as total 
                FROM users 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)";
        
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['days' => $days]);
            $result = $query->fetch();
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error in getRecentUsers: " . $e->getMessage());
            return 0;
        }
    }
    
    // Get user growth data for chart (last 7 days)
    public function getUserGrowthData() {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as count
                FROM users
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log("Error in getUserGrowthData: " . $e->getMessage());
            return [];
        }
    }
    
    // Add new user
    public function addUser($username, $email, $password) {
        $db = Config::getConnexion();
        
        try {
            // Check if email already exists
            $checkEmail = $db->prepare("SELECT id FROM users WHERE email = :email");
            $checkEmail->execute(['email' => $email]);
            if ($checkEmail->fetch()) {
                return ['success' => false, 'message' => 'Email already exists'];
            }
            
            // Check if username already exists
            $checkUsername = $db->prepare("SELECT id FROM users WHERE username = :username");
            $checkUsername->execute(['username' => $username]);
            if ($checkUsername->fetch()) {
                return ['success' => false, 'message' => 'Username already exists'];
            }
            
            // Generate unique user ID
            $userId = 'user_' . time() . '_' . bin2hex(random_bytes(12));
            
            // Hash password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $sql = "INSERT INTO users (id, username, email, password_hash, password, created_at) 
                    VALUES (:id, :username, :email, :password_hash, :password, NOW())";
            
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $userId,
                'username' => $username,
                'email' => $email,
                'password_hash' => $passwordHash,
                'password' => $password
            ]);
            
            return ['success' => true, 'message' => 'User added successfully'];
        } catch (Exception $e) {
            error_log("Error in addUser: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error adding user: ' . $e->getMessage()];
        }
    }
    
    // Update user
    public function updateUser($userId, $username, $email, $password = null) {
        $db = Config::getConnexion();
        
        try {
            if ($password) {
                // Update with new password
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users 
                        SET username = :username, email = :email, password_hash = :password_hash, password = :password 
                        WHERE id = :id";
                $query = $db->prepare($sql);
                $query->execute([
                    'id' => $userId,
                    'username' => $username,
                    'email' => $email,
                    'password_hash' => $passwordHash,
                    'password' => $password
                ]);
            } else {
                // Update without changing password
                $sql = "UPDATE users 
                        SET username = :username, email = :email 
                        WHERE id = :id";
                $query = $db->prepare($sql);
                $query->execute([
                    'id' => $userId,
                    'username' => $username,
                    'email' => $email
                ]);
            }
            
            return ['success' => true, 'message' => 'User updated successfully'];
        } catch (Exception $e) {
            error_log("Error in updateUser: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error updating user: ' . $e->getMessage()];
        }
    }
    
    // Delete user
    public function deleteUser($userId) {
        $db = Config::getConnexion();
        
        try {
            $sql = "DELETE FROM users WHERE id = :id";
            $query = $db->prepare($sql);
            $query->execute(['id' => $userId]);
            
            return ['success' => true, 'message' => 'User deleted successfully'];
        } catch (Exception $e) {
            error_log("Error in deleteUser: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error deleting user: ' . $e->getMessage()];
        }
    }
}

?>
