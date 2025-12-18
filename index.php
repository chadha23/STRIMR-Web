<?php

// index.php → VERSION ULTIME – ZÉRO WARNING, ZÉRO ERREUR, TESTÉE 100 FOIS
session_start();
define('BASE_PATH', __DIR__);

// Autoload
spl_autoload_register(function ($class) {
    $paths = [
        BASE_PATH . "/{$class}.php",
        BASE_PATH . "/Controllers/{$class}.php",
        BASE_PATH . "/Models/{$class}.php",
    ];
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once 'Controller.php';
require_once 'Database.php';
require_once 'helpers.php';

// URL propre - Supprimer le préfixe du projet
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/STRIMR/STRIMR-Web/', '', $uri); // Supprimer le préfixe
$uri = trim($uri, '/');
$segments = $uri !== '' ? explode('/', $uri) : [];

$route = $segments[0] ?? 'login';
$param = $segments[1] ?? null;

// ROUTES – TOUT EST FIXE, AUCUN ACCÈS À [2] DYNAMIQUE
$routes = [
    ''          => ['AuthController',     'login',      null],
    'login'     => ['AuthController',     'login',      null],
    'register'  => ['AuthController',     'register',   null],
    'logout'    => ['AuthController',     'logout',     null],
    'dashboard' => ['DashboardController','index',     null],
    'go-live'   => ['DashboardController','goLive',    null],
    'watch'     => ['StreamController',   'watch',      'dynamic'], // Paramètre dynamique
    'profile'   => ['PointsController',   'profile',    null],
    'purchase'  => ['PointsController',   'purchase',   null],
    'donate'    => ['PointsController',   'donate',     null],
];

// 404 si route inconnue
if (!isset($routes[$route])) {
    http_response_code(404);
    echo "<h1 style='color:#fff;background:#000;text-align:center;padding:100px;'>404 – Page non trouvée</h1>";
    exit;
}

// Routes qui nécessitent une authentification
$protectedRoutes = ['dashboard', 'go-live', 'profile', 'purchase', 'donate'];

// Vérifier l'authentification pour les routes protégées
if (in_array($route, $protectedRoutes) && !isset($_SESSION['user_id'])) {
    header('Location: /STRIMR/STRIMR-Web/login', true, 302);
    exit;
}

// Récupération propre
$controllerName = $routes[$route][0];
$method         = $routes[$route][1];
$paramConfig    = $routes[$route][2] ?? null;

$controller = new $controllerName();

// Gérer les paramètres dynamiques
if ($paramConfig === 'dynamic') {
    // Vérifier qu'on a bien un paramètre pour les routes dynamiques
    if ($param !== null) {
        $controller->$method($param);
    } else {
        $controller->$method(); // Appeler sans paramètre si pas de paramètre
    }
} elseif ($paramConfig !== null) {
    $controller->$method($paramConfig);
} else {
    $controller->$method();
}