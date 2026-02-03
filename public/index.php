<?php
// Afficher les erreurs en dev pour debug
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Configuration sécurisée des sessions AVANT session_start()
$isProduction = false; // Sera mis à jour après chargement de l'env

// Vérifier les variables d'environnement système d'abord (Railway)
if (getenv('APP_ENV') === 'production') {
    $isProduction = true;
}

// Chargement anticipé de l'environnement pour les paramètres de session
$envPath = dirname(__DIR__) . '/.env';
if (!$isProduction && file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        if (trim($name) === 'APP_ENV' && trim(trim($value), '"\'') === 'production') {
            $isProduction = true;
            break;
        }
    }
}

// Configuration sécurisée des cookies de session
session_set_cookie_params([
    'lifetime' => 0,                    // Expire à la fermeture du navigateur
    'path' => '/',
    'domain' => '',
    'secure' => $isProduction,          // HTTPS uniquement en production
    'httponly' => true,                 // Inaccessible via JavaScript
    'samesite' => 'Strict'              // Protection CSRF supplémentaire
]);

session_start();

$root = dirname(__DIR__);

// Chargement de l'environnement
require_once $root . '/config/env.php';
Env::load();

// SÉCURITÉ: Forçage HTTPS en production
// Ne s'active que si APP_ENV=production dans le .env
if (Env::get('APP_ENV') === 'production') {
    // Vérifier si la connexion n'est pas déjà en HTTPS
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                || $_SERVER['SERVER_PORT'] == 443
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    
    if (!$isHttps) {
        // Redirection permanente (301) vers HTTPS
        $httpsUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('Location: ' . $httpsUrl, true, 301);
        exit('Redirection vers HTTPS...');
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

// on charge les models pour pas avoir d'erreur "class not found"
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

// gestion de l'url pour supporter les sous-dossiers (genre /mon-projet/)
if (isset($_GET['path'])) {
    $uri = $_GET['path'];
} else {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $scriptDir = str_replace('\\', '/', dirname($scriptName));
    $projectDir = str_replace('\\', '/', dirname($scriptDir));

    // si l'url commence par le dossier public
    if (strpos($uri, $scriptDir) === 0) {
        $uri = substr($uri, strlen($scriptDir));
    } 
    // sinon si elle commence par la racine du projet
    elseif (strpos($uri, $projectDir) === 0) {
        $uri = substr($uri, strlen($projectDir));
    }

    // si c'est juste /index.php on considere que c'est la racine
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
    // Appel de méthode statique
    $controller::$action();
} else {
    http_response_code(404);
    echo 'Page non trouvée';
}