<?php
$cfg = dirname(__DIR__, 2) . '/config/bdd.php';
require_once $cfg;
class BaseDeDonnees {
    private static $connexion = null;

    public static function getConnexion() {
        global $PARAM_HOTE, $PARAM_PORT, $PARAM_NOM_BD, $PARAM_UTILISATEUR, $PARAM_MDP, $PARAM_CHARSET;

        if (self::$connexion === null) {
            try {
                self::$connexion = new PDO(
                    "mysql:host=$PARAM_HOTE;port=$PARAM_PORT;dbname=$PARAM_NOM_BD;charset=$PARAM_CHARSET",
                    $PARAM_UTILISATEUR,
                    $PARAM_MDP
                );
                self::$connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$connexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } catch (Exception $e) {
                die('Erreur de connexion : ' . $e->getMessage());
            }
        }
        return self::$connexion;
    }
}
?>