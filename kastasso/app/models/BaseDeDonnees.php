<?php
class BaseDeDonnees {
    private static $connexion = null;

    public static function getConnexion() {
        if (self::$connexion === null) {
            $config = require dirname(__DIR__, 2) . '/config/bdd.php';
            try {
                self::$connexion = new PDO(
                    "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}",
                    $config['user'],
                    $config['password']
                );
                self::$connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$connexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$connexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } catch (Exception $e) {
                error_log('ERREUR FATALE BDD : ' . $e->getMessage());
                http_response_code(503);
                $baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
                echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Erreur Technique</title><link rel="stylesheet" href="' . $baseUrl . '/css/erreur_bdd.css"></head><body><h1>Service temporairement indisponible</h1><p>Nous rencontrons actuellement des difficultés techniques.</p><p>Veuillez réessayer dans quelques instants.</p><a href="' . $baseUrl . '/">Retour à l\'accueil</a></body></html>';
                exit(1);
            }
        }
        return self::$connexion;
    }
}
?>