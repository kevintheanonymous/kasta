<?php
require_once __DIR__ . '/BaseDeDonnees.php';

class Categorie {
    public static function findAll() {
        $db = BaseDeDonnees::getConnexion();
        $stmt = $db->query("SELECT * FROM categorie_evenement ORDER BY libelle");
        return $stmt->fetchAll();
    }

    public static function findById($id) {
        $db = BaseDeDonnees::getConnexion();
        $stmt = $db->prepare("SELECT * FROM categorie_evenement WHERE Id_Categorie_evenement = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($libelle) {
        $db = BaseDeDonnees::getConnexion();
        $stmt = $db->prepare("INSERT INTO categorie_evenement (libelle) VALUES (?)");
        return $stmt->execute([$libelle]);
    }

    public static function update($id, $libelle) {
        $db = BaseDeDonnees::getConnexion();
        $stmt = $db->prepare("UPDATE categorie_evenement SET libelle = ? WHERE Id_Categorie_evenement = ?");
        return $stmt->execute([$libelle, $id]);
    }

    public static function delete($id) {
        $db = BaseDeDonnees::getConnexion();
        $stmt = $db->prepare("DELETE FROM categorie_evenement WHERE Id_Categorie_evenement = ?");
        return $stmt->execute([$id]);
    }

    public static function countEvents($id) {
        $db = BaseDeDonnees::getConnexion();
        $stmt = $db->prepare("SELECT COUNT(*) FROM event_sportif WHERE Id_Categorie_evenement = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn();
    }
}
