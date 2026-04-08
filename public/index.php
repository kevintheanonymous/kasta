<?php
// Configuration securisee des sessions AVANT session_start()
$isProduction = false;
$isDebug = false;

// Variables d'environnement systeme (Railway/Heroku)
if (getenv('APP_ENV') === 'production') {
    $isProduction = true;
}
if (getenv('APP_DEBUG') === 'true') {
    $isDebug = true;
}

// Chargement anticipe de l'environnement pour les parametres de session
$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim(trim($value), '"\'');
        if ($name === 'APP_ENV' && $value === 'production') {
            $isProduction = true;
        }
        if ($name === 'APP_DEBUG' && $value === 'true') {
            $isDebug = true;
        }
    }
}

// Erreurs : affichage uniquement en mode debug hors production
if ($isDebug && !$isProduction) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

// Configuration securisee des cookies de session
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $isProduction,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

$root = dirname(__DIR__);

// Chargement de l'environnement
require_once $root . '/config/env.php';
Env::load();

// SECURITE: En-tetes de securite HTTP
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
if ($isProduction) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; frame-ancestors 'none'");
}

// SECURITE: Forcage HTTPS en production
if (Env::get('APP_ENV') === 'production') {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || $_SERVER['SERVER_PORT'] == 443
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    if (!$isHttps) {
        $httpsUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('Location: ' . $httpsUrl, true, 301);
        exit;
    }
}

require_once $root . '/app/fonctions_commun.php';
require_once $root . '/app/controleurs/ControleurAuth.php';
require_once $root . '/app/controleurs/ControleurAdmin.php';
require_once $root . '/app/controleurs/ControleurMembre.php';
require_once $root . '/app/controleurs/ControleurEvenement.php';
require_once $root . '/app/controleurs/ControleurCategorie.php';
require_once $root . '/app/controleurs/ControleurPoste.php';
require_once $root . '/app/controleurs/ControleurCreneau.php';
require_once $root . '/app/controleurs/ControleurGestionnaire.php';
require_once $root . '/app/controleurs/ControleurDocument.php';
require_once $root . '/app/controleurs/ControleurRegimeAlimentaire.php';

require_once $root . '/app/models/BaseDeDonnees.php';
require_once $root . '/app/models/Membre.php';
require_once $root . '/app/models/Admin.php';
require_once $root . '/app/models/EvenementSport.php';
require_once $root . '/app/models/EvenementAsso.php';
require_once $root . '/app/models/Categorie.php';
require_once $root . '/app/models/Poste.php';
require_once $root . '/app/models/Creneau.php';
require_once $root . '/app/models/CreneauPoste.php';
require_once $root . '/app/models/Participation.php';

$routes = require $root . '/config/routes.php';

// Gestion de l'URL pour supporter les sous-dossiers
if (isset($_GET['path'])) {
    $uri = $_GET['path'];
} else {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $scriptDir = str_replace('\\', '/', dirname($scriptName));
    $projectDir = str_replace('\\', '/', dirname($scriptDir));

    if (strpos($uri, $scriptDir) === 0) {
        $uri = substr($uri, strlen($scriptDir));
    } elseif (strpos($uri, $projectDir) === 0) {
        $uri = substr($uri, strlen($projectDir));
    }

    if ($uri === '/index.php') {
        $uri = '/';
    }
}

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
    $controller::$action();
} else {
    http_response_code(404);
    echo 'Page non trouvée';
}
