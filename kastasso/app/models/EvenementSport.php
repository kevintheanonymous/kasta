<?php
require_once __DIR__ . '/BaseDeDonnees.php';

class EvenementSport {

    public static function findAll() {
        $db = BaseDeDonnees::getConnexion();
        $sql = "
            SELECT 
                es.Id_Event_sportif as id_event_sport,
                es.Titre as titre,
                es.Descriptif as descriptif,
                es.Adresse as adresse,
                es.Code_Postal as code_postal,
                es.Ville as ville,
                es.Date_Visibilite as date_visible,
                es.Date_Cloture as date_cloture,
                es.Id_Categorie_evenement as id_cat_event,
                ce.libelle
            FROM event_sportif es
            LEFT JOIN categorie_evenement ce ON es.Id_Categorie_evenement = ce.Id_Categorie_evenement
            ORDER BY es.Date_Cloture DESC
        ";
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    public static function findAllPublic() {
        $db = BaseDeDonnees::getConnexion();
        $sql = "
            SELECT 
                es.Id_Event_sportif as id_event_sport,
                es.Titre as titre,
                es.Descriptif as descriptif,
                es.Adresse as adresse,
                es.Code_Postal as code_postal,
                es.Ville as ville,
                es.Date_Visibilite as date_visible,
                es.Date_Cloture as date_cloture,
                es.Id_Categorie_evenement as id_cat_event,
                ce.libelle
            FROM event_sportif es
            LEFT JOIN categorie_evenement ce ON es.Id_Categorie_evenement = ce.Id_Categorie_evenement
            WHERE es.Date_Visibilite <= CURDATE()
            ORDER BY es.Date_Cloture DESC
        ";
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    public static function findById($id) {
        $db = BaseDeDonnees::getConnexion();
        $sql = "
            SELECT 
                es.Id_Event_sportif as id_event_sport,
                es.Titre as titre,
                es.Descriptif as descriptif,
                es.Adresse as adresse,
                es.Code_Postal as code_postal,
                es.Ville as ville,
                es.Lien_Maps as lieu_maps,
                es.Date_Visibilite as date_visible,
                es.Date_Cloture as date_cloture,
                es.Id_Categorie_evenement as id_cat_event,
                ce.libelle
            FROM event_sportif es
            LEFT JOIN categorie_evenement ce ON es.Id_Categorie_evenement = ce.Id_Categorie_evenement
            WHERE es.Id_Event_sportif = ?
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($data) {
        $db = BaseDeDonnees::getConnexion();
        $sql = "
            INSERT INTO event_sportif (Titre, Descriptif, Adresse, Code_Postal, Ville, Lien_Maps, Date_Visibilite, Date_Cloture, Id_Categorie_evenement)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $data['titre'],
            $data['descriptif'],
            $data['adresse'],
            $data['code_postal'],
            $data['ville'],
            $data['lieu_maps'] ?? null,
            $data['date_visible'],
            $data['date_cloture'],
            $data['id_cat_event']
        ]);
    }

    public static function createAndReturnId($data) {
        $db = BaseDeDonnees::getConnexion();
        $sql = "
            INSERT INTO event_sportif (Titre, Descriptif, Adresse, Code_Postal, Ville, Lien_Maps, Date_Visibilite, Date_Cloture, Id_Categorie_evenement)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $db->prepare($sql);
        $ok = $stmt->execute([
            $data['titre'],
            $data['descriptif'],
            $data['adresse'],
            $data['code_postal'],
            $data['ville'],
            $data['lieu_maps'] ?? null,
            $data['date_visible'],
            $data['date_cloture'],
            $data['id_cat_event']
        ]);

        if (!$ok) {
            return false;
        }

        return (int)$db->lastInsertId();
    }

    public static function update($id, $data) {
        $db = BaseDeDonnees::getConnexion();
        $sql = "
            UPDATE event_sportif
            SET Titre = ?, Descriptif = ?, Adresse = ?, Code_Postal = ?, Ville = ?, Lien_Maps = ?,
                Date_Visibilite = ?, Date_Cloture = ?, Id_Categorie_evenement = ?
            WHERE Id_Event_sportif = ?
        ";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $data['titre'],
            $data['descriptif'],
            $data['adresse'],
            $data['code_postal'],
            $data['ville'],
            $data['lieu_maps'] ?? null,
            $data['date_visible'],
            $data['date_cloture'],
            $data['id_cat_event'],
            $id
        ]);
    }

    public static function delete($id) {
        $db = BaseDeDonnees::getConnexion();
        $stmt = $db->prepare("DELETE FROM event_sportif WHERE Id_Event_sportif = ?");
        return $stmt->execute([$id]);
    }

    public static function obtenirParticipantsAvecRegimes($idEventSportif) {
        $db = BaseDeDonnees::getConnexion();
        $sql = "
            SELECT DISTINCT
                m.Id_Membre as id_membre,
                m.Nom as nom,
                m.Prenom as prenom,
                m.Mail as mail,
                m.Telephone as telephone,
                m.Commentaire_Alimentaire as commentaire_alimentaire,
                regalim.nom as regime_alimentaire,
                GROUP_CONCAT(DISTINCT ra.libelle ORDER BY ra.libelle SEPARATOR ', ') as restrictions,
                GROUP_CONCAT(DISTINCT pa.libelle ORDER BY pa.libelle SEPARATOR ', ') as preferences,
                GROUP_CONCAT(DISTINCT CONCAT(ce.Type, ' - ', DATE_FORMAT(ce.Date_creneau, '%d/%m/%Y'), ' ', TIME_FORMAT(ce.Heure_Debut, '%H:%i'), '-', TIME_FORMAT(ce.Heure_Fin, '%H:%i')) ORDER BY ce.Date_creneau, ce.Heure_Debut SEPARATOR ' | ') as creneaux
            FROM aide_benevole ab
            JOIN creneau_event ce ON ab.Id_creneau = ce.Id_creneau
            JOIN membre m ON ab.Id_Membre = m.Id_Membre
            LEFT JOIN regimes_alimentaires regalim ON m.regime_alimentaire_id = regalim.id
            LEFT JOIN liaison_regime lr ON m.Id_Membre = lr.Id_Membre
            LEFT JOIN restriction_alimentaire ra ON lr.Id_Restriction_alimentaire = ra.Id_Restriction_alimentaire
            LEFT JOIN liaison_pref lp ON m.Id_Membre = lp.Id_Membre
            LEFT JOIN preference_alimentaire pa ON lp.Id_Preference_alimentaire = pa.Id_Preference_alimentaire
            WHERE ce.Id_Event_sportif = ?
            GROUP BY m.Id_Membre, m.Nom, m.Prenom, m.Mail, m.Telephone, m.Commentaire_Alimentaire, regalim.nom
            ORDER BY m.Nom, m.Prenom
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([$idEventSportif]);
        return $stmt->fetchAll();
    }
}
