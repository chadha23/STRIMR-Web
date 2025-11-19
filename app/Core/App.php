<?php
class App {
    public function run() {
        // Fix trailing slash and get clean path
        $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $request_uri = rtrim($request_uri, '/');
        $request_uri = substr($request_uri, strlen(dirname($_SERVER['SCRIPT_NAME']))); // remove /public
        $request_uri = trim($request_uri, '/');
        $segments = $request_uri ? explode('/', $request_uri) : [];

        // Default controller & method
        $controllerName = 'AuthController';
        $method = 'login';
        $param = null;

        if (!empty($segments[0])) {
            // Capitalize first letter + Controller
            $controllerName = ucfirst($segments[0]) . 'Controller';
            $method = $segments[1] ?? 'index';
            $param = $segments[2] ?? null;
        }

        $controllerFile = __DIR__ . '/../Controllers/' . $controllerName . '.php';

        // Fallback routes if controller doesn't exist
        if (!file_exists($controllerFile)) {
            if ($segments[0] === 'watch' && isset($segments[1])) {
                $controllerName = 'StreamController';
                $method = 'watch';
                $param = $segments[1];
            } elseif ($segments[0] === 'go-live') {
                $controllerName = 'DashboardController';
                $method = 'goLive';
            } elseif ($segments[0] === 'dashboard') {
                $controllerName = 'DashboardController';
                $method = 'index';
            } elseif ($segments[0] === 'logout') {
                $controllerName = 'AuthController';
                $method = 'logout';
            } elseif ($segments[0] === 'register') {
                $controllerName = 'AuthController';
                $method = 'register';
            } else {
                // Default to login
                $controllerName = 'AuthController';
                $method = 'login';
            }
            $controllerFile = __DIR__ . '/../Controllers/' . $controllerName . '.php';
        }

        require_once $controllerFile;
        $controller = new $controllerName();

        if ($param !== null) {
            $controller->$method($param);
        } else {
            $controller->$method();
        }
    }
}