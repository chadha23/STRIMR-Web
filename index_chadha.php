<?php

// index.php → STRIMR Complete Platform with Chadha Module Integration
session_start();
define('BASE_PATH', __DIR__);

// Integration: Complete Chadha Module (Users + Full App Structure)
require_once BASE_PATH . '/modules/chadha/integration.php';

// Check if user is authenticated
$userAuth = integrateChadhaAuth();

// Handle different actions
$action = $_GET['action'] ?? 'dashboard';

switch($action) {
    case 'login':
        include BASE_PATH . '/modules/chadha/login_view.php';
        break;
        
    case 'signup':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            include BASE_PATH . '/modules/chadha/signup.php';
        } else {
            include BASE_PATH . '/modules/chadha/login_view.php';
        }
        break;
        
    case 'authenticate':
        include BASE_PATH . '/modules/chadha/login.php';
        break;
        
    case 'logout':
        session_destroy();
        header('Location: ?action=login');
        break;
        
    case 'admin':
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
            include BASE_PATH . '/modules/chadha/admin_view.php';
        } else {
            header('Location: ?action=dashboard');
        }
        break;
        
    case 'profile':
        if (!$userAuth) {
            header('Location: ?action=login');
            exit;
        }
        include BASE_PATH . '/modules/chadha/profile_view.php';
        break;
        
    case 'update_profile':
        if (!$userAuth) {
            header('Location: ?action=login');
            exit;
        }
        include BASE_PATH . '/modules/chadha/update_profile.php';
        break;
        
    case 'get_profile':
        if (!$userAuth) {
            header('Location: ?action=login');
            exit;
        }
        include BASE_PATH . '/modules/chadha/get_profile.php';
        break;
        
    case 'dashboard':
    default:
        // If not authenticated, show login
        if (!$userAuth) {
            include BASE_PATH . '/modules/chadha/login_view.php';
        } else {
            // Main application dashboard with servers, stream, feed
            include BASE_PATH . '/modules/chadha/index_view.php';
        }
        break;
}

?>