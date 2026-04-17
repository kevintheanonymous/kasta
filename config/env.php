<?php

// charge les variables d'environnement depuis le fichier .env
class Env {
    private static $loaded = false;

    // charge le fichier .env
    public static function load($path = null) {
        if (self::$loaded) {
            return;
        }

        if ($path === null) {
            $path = dirname(__DIR__) . '/.env';
        }

        // Si le fichier .env n'existe pas, utiliser les variables d'environnement système
        // (utile pour Railway, Heroku, etc.)
        if (!file_exists($path)) {
            self::$loaded = true;
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Ignorer les commentaires
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parser les lignes KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                // Supprimer les guillemets
                $value = trim($value, '"\'');

                // Définir la variable d'environnement
                if (!array_key_exists($name, $_ENV)) {
                    $_ENV[$name] = $value;
                    putenv("$name=$value");
                }
            }
        }

        self::$loaded = true;
    }

    public static function get($key, $default = null) {
        if (!self::$loaded) {
            self::load();
        }

        // 1. Vérifier si une variable _FILE existe (Docker Secrets)
        $fileKey = $key . '_FILE';
        $filePath = getenv($fileKey) ?: ($_ENV[$fileKey] ?? null);

        if ($filePath && file_exists($filePath)) {
            return trim(file_get_contents($filePath));
        }

        // 2. Sinon lire la variable d'environnement classique
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }

        $envValue = getenv($key);
        if ($envValue !== false && $envValue !== '') {
            return $envValue;
        }

        return $default;
    }
}
