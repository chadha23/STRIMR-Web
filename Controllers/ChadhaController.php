<?php

class ChadhaController 
{
    private $userModule;
    
    public function __construct() {
        $this->userModule = new ChadhaUserModule();
    }
    
    /**
     * Show login interface from Chadha module
     */
    public function showLogin() {
        // Integration with streaming platform
        if (isset($_SESSION['user_id'])) {
            header('Location: /STRIMR/STRIMR-Web/dashboard');
            exit;
        }
        
        $this->userModule->showLogin();
    }
    
    /**
     * Show user profile from Chadha module
     */
    public function showProfile() {
        // Check authentication for streaming
        if (!isset($_SESSION['user_id'])) {
            header('Location: /STRIMR/STRIMR-Web/user-login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $this->userModule->showProfile($userId);
    }
    
    /**
     * Handle authentication through Chadha module
     */
    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $result = authenticateForStream($email, $password);
            
            if ($result) {
                // Successful login - redirect to streaming dashboard
                $_SESSION['user_id'] = $result['id'];
                $_SESSION['username'] = $result['username'];
                header('Location: /STRIMR/STRIMR-Web/dashboard');
                exit;
            } else {
                // Failed login
                $_SESSION['error'] = 'Invalid credentials';
                header('Location: /STRIMR/STRIMR-Web/user-login');
                exit;
            }
        }
        
        header('Location: /STRIMR/STRIMR-Web/user-login');
    }
}
?>