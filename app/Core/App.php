<?php
class App {
    public function run() {
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $segments = explode('/', $uri);

        $controllerName = ucfirst($segments[0] ?? 'Auth') . 'Controller';
        $method = $segments[1] ?? 'index';

        $controllerPath = "../app/Controllers/{$controllerName}.php";

        if (!file_exists($controllerPath)) {
            $controllerName = 'AuthController';
            $method = 'index';
            $controllerPath = '../app/Controllers/AuthController.php';
        }

        require_once $controllerPath;
        $controller = new $controllerName();
        $controller->$method(...array_slice($segments, 2));
    }
}