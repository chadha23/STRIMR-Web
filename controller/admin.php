<?php
// admin.php
session_start();

// Si pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../view/login.html');
    exit;
}

// Si connecté mais pas admin
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../view/index.html');
    exit;
}

// Ici l'utilisateur est admin -> on sert admin.html
readfile(__DIR__ . '/../view/admin.html');
