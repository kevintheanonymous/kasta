<?php
// Chargement de la configuration depuis .env
require_once __DIR__ . '/env.php';
Env::load();

return [
    'host'      => Env::get('DB_HOST', 'localhost'),
    'port'      => Env::get('DB_PORT', '3306'),
    'dbname'    => Env::get('DB_NAME', 'kastasso'),
    'user'      => Env::get('DB_USER', 'root'),
    'password'  => Env::get('DB_PASSWORD', ''),
    'charset'   => Env::get('DB_CHARSET', 'utf8mb4')
];
?>
