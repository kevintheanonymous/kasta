<?php
require_once __DIR__ . '/BaseDeDonnees.php';

class EvenementAsso {

    public static function findAll($modeAdmin = false, $userIsAdherent = false, $includePrivate = false) {
        $db = BaseDeDonnees::getConnexion();
        $sql = "
            SELECT
                ea.Id_Event_associatif as id_event_asso,
                ea.Titre as titre,
                ea.Descriptif as descriptif,
                ea.Adresse as adresse,
                ea.Code_Postal as code_postal,
                ea.Ville as ville,
                ea.Lien_Maps as lieu_maps,
                ea.Date_Evenement as date_event_asso,
                ea.Tarif as tarif,
                ea.Prive as prive,
                ea.Date_Visibilite as date_visible,
                ea.Date_Cloture as date_cloture,
                ea.Url_HelloAsso as url_helloasso
            FROM event_associatif ea
            WHERE 1=1";

        if(!$modeAdmin) {
            if(!$includePrivate && !$userIsAdherent) {
                $sql .= " AND ea.Prive = 0";
            } elseif(!$includePrivate && $userIsAdherent) {
            } elseif(!$includePrivate) {
                $sql .= " AND ea.Prive = 0";
            }

            $sql .= " AND ea.Date_Visibilite <= CURDATE()";
        }

        $sql .= " ORDER BY ea.Date_Evenement DESC";

        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    public static function findAllPublic() {
        return self::findAll(false, false, false);
    }

    public static function findById($id) {
        $db = BaseDeDonnees::getConnexion();
        $sql = "
            SELECT 
                ea.Id_Event_associatif as id_event_asso,
                ea.Titre as titre,
                ea.Descriptif as descriptif,
                ea.Adresse as adresse,
                ea.Code_Postal as code_postal,
                ea.Ville as ville,
                ea.Lien_Maps as lieu_maps,
                ea.Date_Evenement as date_event_asso,
                ea.Tarif as tarif,
                ea.Prive as prive,
                ea.Date_Visibilite as date_visible,
                ea.Date_Cloture as date_cloture,
                ea.Url_HelloAsso as url_helloasso
            FROM event_associatif ea
            WHERE ea.Id_Event_associatif = ?
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($data) {
        $db = BaseDeDonnees::getConnexion();
        $sql = "
            INSERT INTO event_associatif (Titre, Descriptif, Adresse, Code_Postal, Ville, Lien_Maps, Date_Visibilite, Date_Cloture, Date_Evenement, Tarif, Prive, Url_HelloAsso)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
            $data['date_event_asso'],
            $data['tarif'],
            $data['prive'] ?? 0,
            $data['url_helloasso'] ?? null
        ]);
    }

    public static function update($id, $data) {
        $db = BaseDeDonnees::getConnexion();
        $sql = "
            UPDATE event_associatif
            SET Titre = ?, Descriptif = ?, Adresse = ?, Code_Postal = ?, Ville = ?, Lien_Maps = ?,
                Date_Visibilite = ?, Date_Cloture = ?, Date_Evenement = ?,
                Tarif = ?, Prive = ?, Url_HelloAsso = ?
            WHERE Id_Event_associatif = ?
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
            $data['date_event_asso'],
            $data['tarif'],
            $data['prive'] ?? 0,
            $data['url_helloasso'] ?? null,
            $id
        ]);
    }

    public static function delete($id) {
        $db = BaseDeDonnees::getConnexion();
        $stmt = $db->prepare("DELETE FROM event_associatif WHERE Id_Event_associatif = ?");
        return $stmt->execute([$id]);
    }

    public static function obtenirParticipantsAvecRegimes($idEventAssociatif) {
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
                p.Date_inscription as date_inscription,
                p.nb_invites,
                p.Paiement as paiement,
                GROUP_CONCAT(DISTINCT ra.libelle ORDER BY ra.libelle SEPARATOR ', ') as restrictions,
                GROUP_CONCAT(DISTINCT pa.libelle ORDER BY pa.libelle SEPARATOR ', ') as preferences
            FROM participer p
            JOIN membre m ON p.Id_Membre = m.Id_Membre
            LEFT JOIN regimes_alimentaires regalim ON m.regime_alimentaire_id = regalim.id
            LEFT JOIN liaison_regime lr ON m.Id_Membre = lr.Id_Membre
            LEFT JOIN restriction_alimentaire ra ON lr.Id_Restriction_alimentaire = ra.Id_Restriction_alimentaire
            LEFT JOIN liaison_pref lp ON m.Id_Membre = lp.Id_Membre
            LEFT JOIN preference_alimentaire pa ON lp.Id_Preference_alimentaire = pa.Id_Preference_alimentaire
            WHERE p.Id_Event_associatif = ?
            GROUP BY m.Id_Membre, m.Nom, m.Prenom, m.Mail, m.Telephone, m.Commentaire_Alimentaire,
                     regalim.nom, p.Date_inscription, p.nb_invites, p.Paiement
            ORDER BY m.Nom, m.Prenom
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([$idEventAssociatif]);
        return $stmt->fetchAll();
    }
}
