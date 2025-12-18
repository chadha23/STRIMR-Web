<?php
// admin.php
session_start();

// Si pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../view/login.php');
    exit;
}

// Si connecté mais pas admin
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../view/index.php');
    exit;
}

// Ici l'utilisateur est admin -> on sert la vue PHP
include __DIR__ . '/../view/admin.php';
