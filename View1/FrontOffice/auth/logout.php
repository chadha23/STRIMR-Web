<?php
session_start();
require_once __DIR__ . '/../../../Controller/AuthController.php';

$authController = new AuthController();
$authController->logout();

// Redirect to login page
header('Location: index.php');
exit();
?>
