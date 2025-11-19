<?php
require_once __DIR__ . '/../Models/User.php';
class AuthController {
    public function index() { $this->login(); }
    public function login() {
        if (isset($_SESSION['user_id'])) return redirect('/dashboard');
        if ($_POST) {
            if (User::login($_POST['username'], $_POST['password'])) {
                return redirect('/dashboard');
            }
            $error = "Wrong credentials";
        }
        require __DIR__ . '/../Views/Auth/login.php';
    }
    public function register() {
    if (!isset($_SESSION['user_id'])) {
        $error = $success = '';

        if ($_POST) {
            $username = trim($_POST['username']);
            $pass1    = $_POST['password'];
            $pass2    = $_POST['password2'];

            // Validation
            if ($pass1 !== $pass2) {
                $error = "Passwords do not match!";
            } elseif (strlen($username) < 3) {
                $error = "Username too short (min 3 chars)";
            } elseif (strlen($pass1) < 6) {
                $error = "Password too short (min 6 chars)";
            } else {
                try {
                    User::register($username, $pass1);
                    $success = "Account created! You can now log in.";
                    redirect('');
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) { // Duplicate entry
                        $error = "Username already taken!";
                    } else {
                        $error = "Database error. Try again.";
                    }
                }
            }
        }

        require __DIR__ . '/../Views/Auth/register.php';
    } else {
    }
}
    public function logout() {
        session_destroy();
        redirect('');
    }
}