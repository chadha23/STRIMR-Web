<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/User Class";

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Config::getConnexion();
    }
    
    // Validate email format
    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    // Validate username (alphanumeric and underscore, 3-20 chars)
    public function validateUsername($username) {
        return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
    }
    
    // Validate password strength
    public function validatePassword($password) {
        return strlen($password) >= 6;
    }
    
    // Check if email exists
    public function emailExists($email) {
        $sql = "SELECT id FROM users WHERE email = :email";
        $query = $this->db->prepare($sql);
        $query->execute(['email' => $email]);
        return $query->fetch() !== false;
    }
    
    // Check if username exists
    public function usernameExists($username) {
        $sql = "SELECT id FROM users WHERE username = :username";
        $query = $this->db->prepare($sql);
        $query->execute(['username' => $username]);
        return $query->fetch() !== false;
    }
}

?>
