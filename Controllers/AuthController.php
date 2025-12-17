<?php
class AuthController extends Controller {

    public function login() {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('dashboard');
            return;
        }

        $error = '';
        if ($_POST) {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            // Validation côté serveur finale
            if (empty($username) || empty($password)) {
                $error = "Tous les champs sont requis";
            } elseif (User::login($username, $password)) {
                $this->redirect('dashboard');
                return;
            } else {
                $error = "Mauvais identifiants";
            }
        }

        $this->view('Auth/login', ['error' => $error]);
    }

    public function register() {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('dashboard');
            return;
        }

        $error = $success = '';
        if ($_POST) {
            $username = trim($_POST['username'] ?? '');
            $pass1 = $_POST['password'] ?? '';
            $pass2 = $_POST['password2'] ?? '';

            // Validation stricte côté serveur
            if (empty($username) || empty($pass1) || empty($pass2)) {
                $error = "Tous les champs sont requis";
            } elseif (strlen($username) < 3) {
                $error = "Le nom d'utilisateur doit contenir au moins 3 caractères";
            } elseif (strlen($username) > 20) {
                $error = "Le nom d'utilisateur ne peut pas dépasser 20 caractères";
            } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
                $error = "Le nom d'utilisateur ne peut contenir que des lettres, chiffres, _ et -";
            } elseif (strlen($pass1) < 6) {
                $error = "Le mot de passe doit contenir au moins 6 caractères";
            } elseif ($pass1 !== $pass2) {
                $error = "Les mots de passe ne correspondent pas";
            } elseif (User::usernameExists($username)) {
                $error = "Ce nom d'utilisateur est déjà pris. Veuillez en choisir un autre.";
            } else {
                try {
                    $userId = User::register($username, $pass1);
                    // Log succès
                    error_log("Utilisateur créé avec succès: $username (ID: $userId)");
                    // Après inscription, rediriger vers la page de login
                    $this->redirect('login');
                    return;
                } catch (Exception $e) {
                    $error = "Erreur lors de la création du compte: " . $e->getMessage();
                    error_log("Erreur inscription pour '$username': " . $e->getMessage());
                }
            }
        }

        $this->view('Auth/register', compact('error', 'success'));
    }

    public function logout() {
        session_destroy();
        $this->redirect('');
    }
}