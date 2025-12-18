<?php
/**
 * Module Chadha - Complete Integration for STRIMR Platform
 * Full application structure: Users, Servers, Streams, Feed
 */

// Include database configuration
require_once 'db.php';

class ChadhaUserModule {
    private $db;
    
    public function __construct() {
        global $conn;
        $this->db = $conn;
    }
    
    /**
     * Authenticate user for complete platform
     */
    public function authenticate($email, $password) {
        include 'login.php';
    }
    
    /**
     * Register new user for complete platform
     */
    public function registerUser($userData) {
        include 'signup.php';
    }
    
    /**
     * Get user profile for dashboard
     */
    public function getUserProfile($userId) {
        include 'get_profile.php';
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($userId, $data) {
        include 'update_profile.php';
    }
    
    /**
     * Admin functions
     */
    public function adminDashboard() {
        include 'admin.php';
    }
    
    /**
     * Display complete application interface
     * Includes: Servers (Discord-style), Stream (Twitch-style), Feed (Twitter-style)
     */
    public function showCompleteApp() {
        include 'index_view.php';
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
    
    /**
     * Display admin interface
     */
    public function showAdmin() {
        include 'admin_view.php';
    }
}

// Integration with main STRIMR system
function integrateChadhaAuth() {
    // Check if user is logged in
    if (isset($_SESSION['user_id'])) {
        $userModule = new ChadhaUserModule();
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? 'User',
            'email' => $_SESSION['email'] ?? '',
            'is_admin' => $_SESSION['is_admin'] ?? false
        ];
    }
    
    return false;
}

// Main authentication function for the complete platform
if (!function_exists('authenticateForPlatform')) {
    function authenticateForPlatform($email, $password) {
        $chadha = new ChadhaUserModule();
        return $chadha->authenticate($email, $password);
    }
}

// Initialize CSS and JS assets paths
function getChadhaAssets() {
    return [
        'css' => [
            '/STRIMR/STRIMR-Web/assets/css/chadha.css',
            '/STRIMR/STRIMR-Web/modules/chadha/styles.css'
        ],
        'js' => [
            '/STRIMR/STRIMR-Web/assets/js/chadha.js', 
            '/STRIMR/STRIMR-Web/modules/chadha/script.js'
        ]
    ];
}
?>