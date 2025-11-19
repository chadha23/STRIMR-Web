<?php
require_once __DIR__ . '/../Models/User.php';
class AuthController {
    public function index() { $this->login(); }
    public function login() {
        if (isset($_SESSION['user_id'])) return header('Location: /dashboard');
        if ($_POST) {
            if (User::login($_POST['username'], $_POST['password'])) {
                return header('Location: /dashboard');
            }
            $error = "Wrong credentials";
        }
        require __DIR__ . '/../Views/auth/login.php';
    }
    public function register() {
        if ($_POST) {
            User::register($_POST['username'], $_POST['password']);
            return header('Location: /login');
        }
        require __DIR__ . '/../Views/auth/register.php';
    }
    public function logout() {
        session_destroy();
        header('Location: /');
    }
}