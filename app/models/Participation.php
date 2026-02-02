<?php
require_once __DIR__ . '/BaseDeDonnees.php';

class Participation {

    public static function inscrireCreneau($idCreneau, $idMembre, $preferencesPostes = []) {
        $pdo = BaseDeDonnees::getConnexion();

        $stmt = $pdo->prepare("SELECT * FROM aide_benevole WHERE Id_creneau = ? AND Id_Membre = ?");
        $stmt->execute([$idCreneau, $idMembre]);
        if ($stmt->fetch()) {
            return false;
        }

        // Stocker les préférences de postes en JSON (ou null si vide)
        $preferencesJson = !empty($preferencesPostes) ? json_encode(array_values($preferencesPostes)) : null;
        
        $stmt = $pdo->prepare("INSERT INTO aide_benevole (Id_creneau, Id_Membre, Presence, Preference_Poste) VALUES (?, ?, 0, ?)");
        $resultat = $stmt->execute([$idCreneau, $idMembre, $preferencesJson]);
        if ($resultat) {
            require_once __DIR__ . '/../services/EmailService.php';
            EmailService::envoyerConfirmationInscriptionCreneau($idCreneau, $idMembre);
        }

        return $resultat;
    }

    public static function desinscrireCreneau($idCreneau, $idMembre) {
        $pdo = BaseDeDonnees::getConnexion();
        $stmt = $pdo->prepare("DELETE FROM aide_benevole WHERE Id_creneau = ? AND Id_Membre = ?");
        $resultat = $stmt->execute([$idCreneau, $idMembre]);
        
        if ($resultat && $stmt->rowCount() > 0) {
            require_once __DIR__ . '/../services/EmailService.php';
            EmailService::envoyerConfirmationDesinscriptionCreneau($idCreneau, $idMembre);
        }
        
        return $resultat;
    }

    public static function getInscritsCreneaux($idCreneau) {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "
            SELECT m.Id_Membre, m.Prenom, m.Nom, m.Mail, m.Telephone, ab.Presence
            FROM aide_benevole ab
            INNER JOIN membre m ON ab.Id_Membre = m.Id_Membre
            WHERE ab.Id_creneau = ?
            ORDER BY m.Nom, m.Prenom
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idCreneau]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function inscrireEvenementAsso($idMembre, $idEventAsso, $nbInvites = 0) {
        $pdo = BaseDeDonnees::getConnexion();

        $stmt = $pdo->prepare("SELECT * FROM participer WHERE Id_Membre = ? AND Id_Event_associatif = ?");
        $stmt->execute([$idMembre, $idEventAsso]);
        if ($stmt->fetch()) {
            return false;
        }

        $sql = "INSERT INTO participer (Id_Membre, Id_Event_associatif, Paiement, nb_invites) VALUES (?, ?, 0, ?)";
        $stmt = $pdo->prepare($sql);
        $resultat = $stmt->execute([$idMembre, $idEventAsso, $nbInvites]);
        if ($resultat) {
            require_once __DIR__ . '/../services/EmailService.php';
            EmailService::envoyerConfirmationInscriptionEventAsso($idEventAsso, $idMembre, $nbInvites);
        }

        return $resultat;
    }

    public static function desinscrireEvenementAsso($idMembre, $idEventAsso) {
        $pdo = BaseDeDonnees::getConnexion();
        $stmt = $pdo->prepare("DELETE FROM participer WHERE Id_Membre = ? AND Id_Event_associatif = ?");
        $resultat = $stmt->execute([$idMembre, $idEventAsso]);
        
        if ($resultat && $stmt->rowCount() > 0) {
            require_once __DIR__ . '/../services/EmailService.php';
            EmailService::envoyerConfirmationDesinscriptionEventAsso($idEventAsso, $idMembre);
        }
        
        return $resultat;
    }

    public static function modifierAccompagnateurs($idMembre, $idEventAsso, $nbInvites) {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "UPDATE participer SET nb_invites = ? WHERE Id_Membre = ? AND Id_Event_associatif = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$nbInvites, $idMembre, $idEventAsso]);
    }

    public static function desinscrireEvenementSportifComplet($idMembre, $idEventSport) {
        $pdo = BaseDeDonnees::getConnexion();
        
        // Récupérer les créneaux auxquels le membre est inscrit avant de supprimer
        $sqlCreneaux = "
            SELECT ab.Id_creneau 
            FROM aide_benevole ab
            INNER JOIN creneau_event ce ON ab.Id_creneau = ce.Id_creneau
            WHERE ab.Id_Membre = ? AND ce.Id_Event_sportif = ?
        ";
        $stmtCreneaux = $pdo->prepare($sqlCreneaux);
        $stmtCreneaux->execute([$idMembre, $idEventSport]);
        $creneaux = $stmtCreneaux->fetchAll(PDO::FETCH_COLUMN);
        
        // Supprimer les inscriptions
        $sql = "
            DELETE ab FROM aide_benevole ab
            INNER JOIN creneau_event ce ON ab.Id_creneau = ce.Id_creneau
            WHERE ab.Id_Membre = ? AND ce.Id_Event_sportif = ?
        ";
        $stmt = $pdo->prepare($sql);
        $resultat = $stmt->execute([$idMembre, $idEventSport]);
        
        // Envoyer un email pour le premier créneau (représentant l'événement)
        if ($resultat && $stmt->rowCount() > 0 && !empty($creneaux)) {
            require_once __DIR__ . '/../services/EmailService.php';
            EmailService::envoyerConfirmationDesinscriptionCreneau($creneaux[0], $idMembre);
        }
        
        return $resultat;
    }

    public static function getParticipants($idEventAsso) {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "
            SELECT m.Id_Membre, m.Prenom, m.Nom, m.Mail, m.Telephone,
                   p.Paiement, p.nb_invites, p.Date_inscription
            FROM participer p
            INNER JOIN membre m ON p.Id_Membre = m.Id_Membre
            WHERE p.Id_Event_associatif = ?
            ORDER BY m.Nom, m.Prenom
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idEventAsso]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenirNombreInscritsEvenement($idEventSport) {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "
            SELECT COUNT(DISTINCT ab.Id_Membre) as nb_inscrits
            FROM aide_benevole ab
            INNER JOIN creneau_event ce ON ab.Id_creneau = ce.Id_creneau
            WHERE ce.Id_Event_sportif = ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idEventSport]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['nb_inscrits'] ?? 0);
    }

    public static function obtenirInscritsPourEvenement($idEventSport) {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "
            SELECT
                ce.Id_creneau,
                ce.Type as type_creneau,
                ce.Date_creneau,
                ce.Heure_Debut,
                ce.Heure_Fin,
                ce.Commentaire as commentaire_creneau,
                m.Id_Membre,
                m.Nom,
                m.Prenom,
                m.Mail,
                m.Telephone,
                ab.Date_inscription,
                ab.Presence,
                ab.Preference_Poste
            FROM creneau_event ce
            LEFT JOIN aide_benevole ab ON ce.Id_creneau = ab.Id_creneau
            LEFT JOIN membre m ON ab.Id_Membre = m.Id_Membre
            WHERE ce.Id_Event_sportif = ?
            ORDER BY ce.Date_creneau, ce.Heure_Debut, m.Nom, m.Prenom
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idEventSport]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les libellés des postes à partir d'une liste d'IDs (stockés en JSON)
     */
    public static function getPostesLibellesFromJson($preferencesJson) {
        if (empty($preferencesJson)) {
            return [];
        }
        
        $ids = json_decode($preferencesJson, true);
        if (!is_array($ids) || empty($ids)) {
            // Compatibilité avec ancien format (ID unique)
            if (is_numeric($preferencesJson)) {
                $ids = [(int)$preferencesJson];
            } else {
                return [];
            }
        }
        
        $pdo = BaseDeDonnees::getConnexion();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT Id_Poste, libelle, niveau FROM poste WHERE Id_Poste IN ($placeholders)");
        $stmt->execute($ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenirInscritsPourGestionnaire($idEventSport) {
        return self::obtenirInscritsPourEvenement($idEventSport);
    }

    public static function obtenirNombreInscritsEvenementAsso($idEventAsso) {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "
            SELECT COUNT(*) as nb_inscrits
            FROM participer
            WHERE Id_Event_associatif = ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idEventAsso]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['nb_inscrits'] ?? 0);
    }

    public static function obtenirInscritsPourEvenementAsso($idEventAsso) {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "
            SELECT
                m.Id_Membre,
                m.Nom,
                m.Prenom,
                m.Mail,
                m.Telephone,
                p.Date_inscription,
                p.Paiement,
                p.nb_invites
            FROM participer p
            INNER JOIN membre m ON p.Id_Membre = m.Id_Membre
            WHERE p.Id_Event_associatif = ?
            ORDER BY p.Date_inscription DESC, m.Nom, m.Prenom
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idEventAsso]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function marquerPresence($idCreneau, $idMembre, $presence) {
        try {
            $pdo = BaseDeDonnees::getConnexion();

            $stmt = $pdo->prepare("SELECT * FROM aide_benevole WHERE Id_creneau = ? AND Id_Membre = ?");
            $stmt->execute([$idCreneau, $idMembre]);

            if (!$stmt->fetch()) {
                return false;
            }
            $stmt = $pdo->prepare("UPDATE aide_benevole SET Presence = ? WHERE Id_creneau = ? AND Id_Membre = ?");
            return $stmt->execute([$presence, $idCreneau, $idMembre]);
            
        } catch (Throwable $e) {
            error_log('Participation::marquerPresence error: ' . $e->getMessage());
            return false;
        }
    }

    public static function marquerPresencesMasse($idCreneau, $presences) {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $pdo->beginTransaction();
            
            $updated = 0;
            $errors = [];
            
            foreach ($presences as $idMembre => $presence) {
                $stmt = $pdo->prepare("UPDATE aide_benevole SET Presence = ? WHERE Id_creneau = ? AND Id_Membre = ?");
                
                if ($stmt->execute([$presence, $idCreneau, $idMembre])) {
                    if ($stmt->rowCount() > 0) {
                        $updated++;
                    }
                } else {
                    $errors[] = "Erreur pour le membre ID $idMembre";
                }
            }
            
            $pdo->commit();
            
            return [
                'success' => true,
                'updated' => $updated,
                'errors' => $errors
            ];
            
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Participation::marquerPresencesMasse error: ' . $e->getMessage());
            return [
                'success' => false,
                'updated' => 0,
                'errors' => ['Erreur lors de la mise à jour des présences']
            ];
        }
    }

    public static function marquerTousPresents($idCreneau, $presence = 1) {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $stmt = $pdo->prepare("UPDATE aide_benevole SET Presence = ? WHERE Id_creneau = ?");
            return $stmt->execute([$presence, $idCreneau]);
        } catch (Throwable $e) {
            error_log('Participation::marquerTousPresents error: ' . $e->getMessage());
            return false;
        }
    }

    public static function estInscritCreneau($idCreneau, $idMembre) {
        $pdo = BaseDeDonnees::getConnexion();
        $stmt = $pdo->prepare("SELECT 1 FROM aide_benevole WHERE Id_creneau = ? AND Id_Membre = ?");
        $stmt->execute([$idCreneau, $idMembre]);
        return $stmt->fetch() !== false;
    }

    public static function estInscritEvenementAsso($idMembre, $idEventAsso) {
        $pdo = BaseDeDonnees::getConnexion();
        $stmt = $pdo->prepare("SELECT 1 FROM participer WHERE Id_Membre = ? AND Id_Event_associatif = ?");
        $stmt->execute([$idMembre, $idEventAsso]);
        return $stmt->fetch() !== false;
    }

    public static function getCreneauxInscritsMembre($idMembre, $idEventSport) {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "
            SELECT ab.Id_creneau
            FROM aide_benevole ab
            INNER JOIN creneau_event ce ON ab.Id_creneau = ce.Id_creneau
            WHERE ab.Id_Membre = ? AND ce.Id_Event_sportif = ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idMembre, $idEventSport]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Id_creneau');
    }

    public static function getInscriptionEventAsso($idMembre, $idEventAsso) {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "
            SELECT Id_Membre, Id_Event_associatif, Paiement, nb_invites, Date_inscription
            FROM participer
            WHERE Id_Membre = ? AND Id_Event_associatif = ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idMembre, $idEventAsso]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getMesInscriptionsSport($idMembre) {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "
            SELECT
                es.Id_Event_sportif as id_event,
                es.Titre as titre,
                es.Date_cloture as date_cloture,
                es.Adresse as adresse,
                es.Ville as ville,
                ce.Id_creneau,
                ce.Type as type_creneau,
                ce.Date_creneau,
                ce.Heure_Debut,
                ce.Heure_Fin
            FROM aide_benevole ab
            INNER JOIN creneau_event ce ON ab.Id_creneau = ce.Id_creneau
            INNER JOIN event_sportif es ON ce.Id_Event_sportif = es.Id_Event_sportif
            WHERE ab.Id_Membre = ?
            ORDER BY ce.Date_creneau ASC, ce.Heure_Debut ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idMembre]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getMesInscriptionsAsso($idMembre) {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "
            SELECT
                ea.Id_Event_associatif as id_event,
                ea.Titre as titre,
                ea.Date_Evenement as date_event,
                ea.Date_Cloture as date_cloture,
                ea.Tarif as tarif,
                ea.Adresse as adresse,
                ea.Ville as ville,
                p.nb_invites,
                p.Paiement,
                p.Date_inscription
            FROM participer p
            INNER JOIN event_associatif ea ON p.Id_Event_associatif = ea.Id_Event_associatif
            WHERE p.Id_Membre = ?
            ORDER BY ea.Date_Evenement ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idMembre]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getMesEvenementsSportifsPasses($idMembre) {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "
            SELECT
                es.Id_Event_sportif,
                es.Titre,
                es.Adresse,
                es.Ville,
                cat.libelle as categorie,
                MAX(ce.Date_creneau) as date_evenement,
                COUNT(ab.Id_creneau) as nb_creneaux_effectues
            FROM aide_benevole ab
            INNER JOIN creneau_event ce ON ab.Id_creneau = ce.Id_creneau
            INNER JOIN event_sportif es ON ce.Id_Event_sportif = es.Id_Event_sportif
            LEFT JOIN categorie_evenement cat ON es.Id_Categorie_evenement = cat.Id_Categorie_evenement
            WHERE ab.Id_Membre = ?
            AND ab.Presence = 1
            GROUP BY es.Id_Event_sportif, es.Titre, es.Adresse, es.Ville, cat.libelle
            ORDER BY date_evenement DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idMembre]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function aParticipeEventSportifRecement($email) {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "
            SELECT COUNT(*) as nb_participations
            FROM aide_benevole ab
            INNER JOIN creneau_event ce ON ab.Id_creneau = ce.Id_creneau
            INNER JOIN membre m ON ab.Id_Membre = m.Id_Membre
            WHERE m.Mail = ?
            AND ab.Presence = 1
            AND ce.Date_creneau >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)($result['nb_participations'] ?? 0) > 0;
    }

    public static function getHistoriqueMembre($idMembre) {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "
            SELECT DISTINCT
                es.Id_Event_sportif,
                es.Titre,
                ce_min.Date_creneau AS Date_Evenement,
                cat.libelle AS Categorie
            FROM event_sportif es
            INNER JOIN creneau_event ce ON es.Id_Event_sportif = ce.Id_Event_sportif
            INNER JOIN aide_benevole ab ON ce.Id_creneau = ab.Id_creneau
            INNER JOIN categorie_evenement cat ON es.Id_Categorie_evenement = cat.Id_Categorie_evenement
            INNER JOIN (
                SELECT Id_Event_sportif, MIN(Date_creneau) AS Date_creneau
                FROM creneau_event
                GROUP BY Id_Event_sportif
            ) ce_min ON es.Id_Event_sportif = ce_min.Id_Event_sportif
            WHERE ab.Id_Membre = ?
            AND ab.Presence = 1
            ORDER BY ce_min.Date_creneau DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idMembre]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ajoute un accompagnateur pour un événement associatif
    public static function ajouterAccompagnateur($idMembre, $idEventAsso, $nom, $prenom, $email, $tarif) {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "INSERT INTO accompagnateur_event_asso (Id_Membre, Id_Event_associatif, Nom, Prenom, Email, Tarif)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$idMembre, $idEventAsso, $nom, $prenom, $email, $tarif]);
    }

    // Récupère les accompagnateurs d'un membre pour un événement
    public static function getAccompagnateurs($idMembre, $idEventAsso) {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "
            SELECT Id_Accompagnateur, Nom, Prenom, Email, Tarif, Date_ajout
            FROM accompagnateur_event_asso
            WHERE Id_Membre = ? AND Id_Event_associatif = ?
            ORDER BY Date_ajout ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idMembre, $idEventAsso]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Supprime tous les accompagnateurs d'un membre pour un événement
    public static function supprimerAccompagnateurs($idMembre, $idEventAsso) {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "DELETE FROM accompagnateur_event_asso WHERE Id_Membre = ? AND Id_Event_associatif = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$idMembre, $idEventAsso]);
    }

    // Sauvegarde les accompagnateurs (supprime les anciens et ajoute les nouveaux)
    public static function sauvegarderAccompagnateurs($idMembre, $idEventAsso, $accompagnateursData) {
        // supprime les anciens accompagnateurs
        self::supprimerAccompagnateurs($idMembre, $idEventAsso);

        // ajoute les nouveaux
        foreach ($accompagnateursData as $acc) {
            if (isset($acc['nom']) && isset($acc['prenom']) && isset($acc['email']) && isset($acc['tarif'])) {
                self::ajouterAccompagnateur(
                    $idMembre,
                    $idEventAsso,
                    $acc['nom'],
                    $acc['prenom'],
                    $acc['email'],
                    $acc['tarif']
                );
            }
        }

        // met a jour le nombre d'invites dans la table participer
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "UPDATE participer SET nb_invites = ? WHERE Id_Membre = ? AND Id_Event_associatif = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([count($accompagnateursData), $idMembre, $idEventAsso]);

        return true;
    }

    // Met à jour le statut de paiement d'un participant
    public static function updatePaiement($idMembre, $idEventAsso, $statut) {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "UPDATE participer SET Paiement = ? WHERE Id_Membre = ? AND Id_Event_associatif = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$statut, $idMembre, $idEventAsso]);
    }
}