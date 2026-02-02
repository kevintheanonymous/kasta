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

        if (!file_exists($path)) {
            throw new Exception("Fichier .env introuvable à : $path");
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

    // recupere une variable d'environnement
    public static function get($key, $default = null) {
        if (!self::$loaded) {
            self::load();
        }

        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}
