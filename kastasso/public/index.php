<?php
session_start();

$root = dirname(__DIR__);
require_once $root . '/app/controleurs/ControleurAuth.php';
require_once $root . '/app/controleurs/ControleurAdmin.php';
require_once $root . '/app/controleurs/ControleurMembre.php';
require_once $root . '/app/controleurs/ControleurEvenement.php';

$routes = require $root . '/config/routes.php';

$uri = $_GET['path'] ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];
$map = $routes[$method] ?? [];

if (isset($map[$uri])) {
    [$controller, $action] = $map[$uri];
    if (!class_exists($controller) || !method_exists($controller, $action)) {
        http_response_code(500);
        echo 'Route mal configurée.';
        exit;
    }
    $instance = new $controller();           
    $instance->$action();  
} else {
    http_response_code(404);
    echo 'Page non trouvée';
}