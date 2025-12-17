<?php
// Controller.php → VERSION FINALE POUR DOSSIER "Views" ET "Auth" EN MAJUSCULE
class Controller {
    protected function view($path, $data = []) {
        extract($data);
        // Views en MAJUSCULE + Auth en MAJUSCULE
        require BASE_PATH . "/Views/{$path}.php";
    }

    protected function redirect($to = '') {
        // S'assurer qu'aucun output n'a été envoyé
        if (!headers_sent()) {
            $url = '/STRIMR/STRIMR-Web/' . ltrim($to, '/');
            header('Location: ' . $url, true, 302);
            exit;
        } else {
            // Fallback avec JavaScript si headers déjà envoyés
            echo "<script>window.location.href = '/STRIMR/STRIMR-Web/" . ltrim($to, '/') . "';</script>";
            exit;
        }
    }
}