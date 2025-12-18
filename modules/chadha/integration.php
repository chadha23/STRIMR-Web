<?php
/**
 * Module Chadha - Integration prototype for STRIMR
 * User Authentication and Management System
 */

// Include database configuration
require_once 'db.php';

class ChadhaUserModule {
    private $db;
    
    public function __construct() {
        $this->db = new PDO("mysql:host=localhost;dbname=strimr", "root", "");
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    /**
     * Authenticate user for streaming platform
     */
    public function authenticate($email, $password) {
        include 'login.php';
    }
    
    /**
     * Register new streamer
     */
    public function registerUser($userData) {
        include 'signup.php';
    }
    
    /**
     * Get user profile for streaming dashboard
     */
    public function getUserProfile($userId) {
        include 'users.php';
    }
    
    /**
     * Display login interface
     */
    public function showLogin() {
        include 'login_view.php';
    }
    
    /**
     * Display user profile interface
     */
    public function showProfile($userId) {
        include 'profile_view.php';
    }
}

// Integration with main STRIMR system
function integrateChadhaAuth() {
    $userModule = new ChadhaUserModule();
    
    // Check if user is logged in for streaming
    if (isset($_SESSION['user_id'])) {
        return $userModule->getUserProfile($_SESSION['user_id']);
    }
    
    return false;
}

// Hook into existing user system
if (!function_exists('authenticateForStream')) {
    function authenticateForStream($email, $password) {
        $chadha = new ChadhaUserModule();
        return $chadha->authenticate($email, $password);
    }
}
?>