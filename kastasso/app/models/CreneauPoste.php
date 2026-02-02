<?php
require_once __DIR__ . '/BaseDeDonnees.php';

class CreneauPoste {

    public static function lierPostesACreneau($idCreneau, array $idPostes) {
        if (empty($idPostes)) {
            return true;
        }

        $pdo = BaseDeDonnees::getConnexion();
        $sqlDelete = "DELETE FROM creneau_poste WHERE Id_creneau = ?";
        $stmt = $pdo->prepare($sqlDelete);
        $stmt->execute([$idCreneau]);
        $sqlInsert = "INSERT INTO creneau_poste (Id_creneau, Id_Poste) VALUES (?, ?)";
        $stmt = $pdo->prepare($sqlInsert);

        foreach ($idPostes as $idPoste) {
            $ok = $stmt->execute([$idCreneau, $idPoste]);
            if (!$ok) {
                return false;
            }
        }

        return true;
    }

    public static function getPostesPourCreneau($idCreneau) {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "
            SELECT p.*
            FROM poste p
            INNER JOIN creneau_poste cp ON p.Id_Poste = cp.Id_Poste
            WHERE cp.Id_creneau = ?
            ORDER BY p.libelle
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idCreneau]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function supprimerLiaisonsCreneau($idCreneau) {
        $pdo = BaseDeDonnees::getConnexion();
        $stmt = $pdo->prepare("DELETE FROM creneau_poste WHERE Id_creneau = ?");
        return $stmt->execute([$idCreneau]);
    }
}
