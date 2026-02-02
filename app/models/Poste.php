<?php
require_once __DIR__ . '/BaseDeDonnees.php';

class Poste {

    public static function findAll() {
        $db = BaseDeDonnees::getConnexion();
        $stmt = $db->query("SELECT * FROM poste ORDER BY libelle");
        return $stmt->fetchAll();
    }

    public static function findById($id) {
        $db = BaseDeDonnees::getConnexion();
        $stmt = $db->prepare("SELECT * FROM poste WHERE Id_Poste = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($libelle, $niveau) {
        $db = BaseDeDonnees::getConnexion();
        $stmt = $db->prepare("INSERT INTO poste (libelle, niveau) VALUES (?, ?)");
        return $stmt->execute([$libelle, $niveau]);
    }

    public static function update($id, $libelle, $niveau) {
        $db = BaseDeDonnees::getConnexion();
        $stmt = $db->prepare("UPDATE poste SET libelle = ?, niveau = ? WHERE Id_Poste = ?");
        return $stmt->execute([$libelle, $niveau, $id]);
    }

    public static function delete($id) {
        $db = BaseDeDonnees::getConnexion();
        $stmt = $db->prepare("DELETE FROM poste WHERE Id_Poste = ?");
        return $stmt->execute([$id]);
    }

    public static function countPreferences($id) {
        $db = BaseDeDonnees::getConnexion();
        // Compter les préférences qui contiennent cet ID (stockées en JSON ou ancien format)
        $stmt = $db->prepare("SELECT Preference_Poste FROM aide_benevole WHERE Preference_Poste IS NOT NULL");
        $stmt->execute();
        $count = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pref = $row['Preference_Poste'];
            // Vérifier si c'est l'ancien format (ID unique) ou le nouveau (JSON)
            if (is_numeric($pref) && (int)$pref === (int)$id) {
                $count++;
            } else {
                $ids = json_decode($pref, true);
                if (is_array($ids) && in_array((int)$id, $ids)) {
                    $count++;
                }
            }
        }
        return $count;
    }

    public static function countCreneauLinks($id) {
        $db = BaseDeDonnees::getConnexion();
        $stmt = $db->prepare("SELECT COUNT(*) FROM creneau_poste WHERE Id_Poste = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn();
    }
}
