<?php
session_start();
require_once '../app/Core/App.php';
require_once '../app/Core/Database.php';

$app = new App();
$app->run();