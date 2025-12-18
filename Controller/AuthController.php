<?php 
require_once __DIR__ . "/../Model/config.php";
require_once __DIR__ . "/../Model/User Class";

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class AuthController {
    
    // Admin credentials (you can change these)
    private const ADMIN_EMAIL = "admin@strimr.com";
    private const ADMIN_PASSWORD = "admin123"; // In production, this should be hashed in database
    
    // Generate unique ID for varchar(100) IDs
    private function generateId($prefix = 'user') {
        return $prefix . '_' . time() . '_' . bin2hex(random_bytes(12));
    }
    
    // User Sign Up
    public function signup($username, $email, $password, $fullName = null) {
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
            
            // Validate password length
            if (strlen($password) < 6) {
                return ['success' => false, 'message' => 'Password must be at least 6 characters'];
            }
            
            // Generate unique user ID
            $userId = $this->generateId('user');
            
            // Hash password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user (store both hash and actual password)
            $sql = "INSERT INTO users (id, username, email, password_hash, password, created_at) 
                    VALUES (:id, :username, :email, :password_hash, :password, NOW())";
            
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $userId,
                'username' => $username,
                'email' => $email,
                'password_hash' => $passwordHash,
                'password' => $password  // Store actual password
            ]);
            
            return ['success' => true, 'message' => 'Account created successfully', 'user_id' => $userId];
            
        } catch (Exception $e) {
            error_log("Error in signup: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error creating account. Please try again.'];
        }
    }
    
    // User Login
    public function login($emailOrUsername, $password) {
        $db = Config::getConnexion();
        
        try {
            // Check if it's admin login
            if ($emailOrUsername === self::ADMIN_EMAIL && $password === self::ADMIN_PASSWORD) {
                $_SESSION['user_id'] = 'admin';
                $_SESSION['username'] = 'admin';
                $_SESSION['email'] = self::ADMIN_EMAIL;
                $_SESSION['is_admin'] = true;
                return ['success' => true, 'message' => 'Admin login successful', 'is_admin' => true];
            }
            
            // Find user by email or username
            $sql = "SELECT id, username, email, password_hash FROM users 
                    WHERE email = :identifier OR username = :identifier";
            
            $query = $db->prepare($sql);
            $query->execute(['identifier' => $emailOrUsername]);
            $user = $query->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid email/username or password'];
            }
            
            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Invalid email/username or password'];
            }
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['is_admin'] = false;
            
            return ['success' => true, 'message' => 'Login successful', 'is_admin' => false, 'user' => $user];
            
        } catch (Exception $e) {
            error_log("Error in login: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error during login. Please try again.'];
        }
    }
    
    // Admin Login (separate method for back office)
    public function adminLogin($email, $password) {
        if ($email === self::ADMIN_EMAIL && $password === self::ADMIN_PASSWORD) {
            $_SESSION['user_id'] = 'admin';
            $_SESSION['username'] = 'admin';
            $_SESSION['email'] = self::ADMIN_EMAIL;
            $_SESSION['is_admin'] = true;
            return ['success' => true, 'message' => 'Admin login successful'];
        }
        
        return ['success' => false, 'message' => 'Invalid admin credentials'];
    }
    
    // Logout
    public function logout() {
        session_unset();
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    // Check if user is admin
    public function isAdmin() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }
    
    // Get current user ID
    public function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    // Get current user info
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        if ($this->isAdmin()) {
            return [
                'id' => 'admin',
                'username' => 'admin',
                'email' => self::ADMIN_EMAIL,
                'is_admin' => true
            ];
        }
        
        $db = Config::getConnexion();
        try {
            $sql = "SELECT id, username, email, avatar_url, biography, created_at FROM users WHERE id = :id";
            $query = $db->prepare($sql);
            $query->execute(['id' => $_SESSION['user_id']]);
            $user = $query->fetch();
            
            if ($user) {
                $user['is_admin'] = false;
            }
            
            return $user;
        } catch (Exception $e) {
            error_log("Error in getCurrentUser: " . $e->getMessage());
            return null;
        }
    }
    
    // Require login (redirect if not logged in)
    public function requireLogin($redirectTo = '../auth/index.php') {
        if (!$this->isLoggedIn()) {
            header('Location: ' . $redirectTo);
            exit();
        }
    }
    
    // Require admin (redirect if not admin)
    public function requireAdmin($redirectTo = '../auth/index.php') {
        if (!$this->isAdmin()) {
            header('Location: ' . $redirectTo);
            exit();
        }
    }
}

?>
