<?php
// controleur pour les gestionnaires
// c'est comme l'admin mais avec moins de droits
// j'ai refactorié aussi pour pas dupliquer le code
require_once __DIR__ . '/../fonctions_evenements.php';
require_once __DIR__ . '/../models/BaseDeDonnees.php';
require_once __DIR__ . '/../models/Creneau.php';
require_once __DIR__ . '/../models/Participation.php';
require_once __DIR__ . '/../models/EvenementSport.php';
require_once __DIR__ . '/../models/EvenementAsso.php';
require_once __DIR__ . '/../models/Membre.php';

class ControleurGestionnaire
{
    // check les droits du gestionaire
    private static function verifierGestionnaire(): void
    {
        verifierAccesAdminOuGestionnaire();
    }

    // page d'accueil du gestionaire
    public static function afficherDashboard(): void
    {
        self::verifierGestionnaire();
        $countSport = count(EvenementSport::findAll());
        $countAsso = count(EvenementAsso::findAll(true));

        // Récupérer les événements pour permettre au gestionnaire de s'inscrire
        $userId = $_SESSION['user_id'] ?? 0;
        $isAdherent = false;
        if ($userId) {
            $membre = Membre::getMembreParId($userId);
            $isAdherent = ($membre && $membre['Adherent'] == 1);
        }
        $evenements_sport = EvenementSport::findAllPublic();
        $evenements_asso = EvenementAsso::findAll(false, $isAdherent, false);

        require __DIR__ . '/../vues/gestionnaire/tableau_de_bord.php';
    }

    // liste des events
    public static function afficherEvenements(): void
    {
        self::verifierGestionnaire();
        $type = $_GET['type'] ?? 'sport';
        $events = getEvenementsByType($type);

        // Pour les événements sportifs, on récupère le nombre de bénévoles inscrits
        if ($type === 'sport') {
            foreach ($events as &$event) {
                $event['nb_inscrits'] = Participation::obtenirNombreInscritsEvenement($event['id_event_sport']);
            }
            unset($event); // Libère la référence
        }

        // Pour les événements associatifs, on récupère le nombre de participants inscrits
        if ($type === 'asso') {
            foreach ($events as &$event) {
                $event['nb_inscrits'] = Participation::obtenirNombreInscritsEvenementAsso($event['id_event_asso']);
            }
            unset($event); // Libère la référence
        }

        $categories = Categorie::findAll();
        require __DIR__ . '/../vues/evenements/gestionnaire/liste.php';
    }

    // formulaire de creation
    public static function creerEvenement(): void
    {
        self::verifierGestionnaire();
        $type = $_GET['type'] ?? 'sport';
        // Sportifs : on passe au formulaire combiné événement + créneaux
        if ($type === 'sport') {
            rediriger('/gestionnaire/events/create-with-slots&type=sport');
        }

        // Associatifs : on garde le formulaire existant
        $categories = Categorie::findAll();
        require __DIR__ . '/../vues/evenements/gestionnaire/creer.php';
    }

    // formulaire combiné (événement sportif + créneaux)
    public static function creerEvenementAvecCreneaux(): void
    {
        self::verifierGestionnaire();
        $type = $_GET['type'] ?? 'sport';
        if ($type !== 'sport') {
            $_SESSION['errors'] = ["La création combinée n'est disponible que pour les événements sportifs."];
            rediriger('/gestionnaire/events&type=' . $type);
        }

        $categories = Categorie::findAll();
        $postes = Poste::findAll();
        $formAction = url('/gestionnaire/events/store-with-slots');
        $cancelUrl = url('/gestionnaire/events&type=' . $type);
        require __DIR__ . '/../vues/evenements/admin/creer_evenement_creneaux.php';
    }

    // enregistre l'event dans la bdd
    public static function enregistrerEvenement(): void
    {
        self::verifierGestionnaire();
        traiterCreationEvenement('/gestionnaire/events');
    }

    // traitement combiné événement sportif + créneaux pour gestionnaire
    public static function enregistrerEvenementAvecCreneaux(): void
    {
        self::verifierGestionnaire();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger('/gestionnaire/events/create-with-slots&type=sport');
        }

        validerCSRFOuRediriger('/gestionnaire/events/create-with-slots&type=sport');

        $type = $_POST['type'] ?? 'sport';
        if ($type !== 'sport') {
            $_SESSION['errors'] = ["La création combinée n'est disponible que pour les événements sportifs."];
            rediriger('/gestionnaire/events&type=' . $type);
        }

        $data = extraireDonneesEvenementBase();
        $validation = validerDatesEvenement($data);
        if (!$validation['valid']) {
            $_SESSION['errors'] = [$validation['error']];
            rediriger('/gestionnaire/events/create-with-slots&type=' . $type);
        }

        $data = preparerDonneesEvenementSport($data);

        $creneaux = normaliserCreneauxDepuisPost($_POST['creneaux'] ?? []);
        $dateCloture = $data['date_cloture'];
        if (strlen($dateCloture) === 10) {
            $dateCloture .= ' 00:00:00';
        }

        $errors = validerCreneaux($creneaux, $dateCloture);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            rediriger('/gestionnaire/events/create-with-slots&type=' . $type);
        }

        $pdo = BaseDeDonnees::getConnexion();

        try {
            $pdo->beginTransaction();

            $eventId = EvenementSport::createAndReturnId($data);
            if (!$eventId) {
                throw new Exception("Échec de la création de l'événement.");
            }

            $okCreneaux = Creneau::createMany($eventId, $creneaux);
            if (!$okCreneaux) {
                throw new Exception("Échec de la création des créneaux.");
            }

            $pdo->commit();
            $_SESSION['success'] = "Événement et créneaux créés avec succès.";
            rediriger('/admin/creneaux&id_event=' . $eventId);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['errors'] = ["Erreur lors de la création : " . $e->getMessage()];
            rediriger('/gestionnaire/events/create-with-slots&type=' . $type);
        }
    }

    // formulaire pour modifier un event
    public static function modifierEvenement(): void
    {
        self::verifierGestionnaire();
        $type = $_GET['type'] ?? 'sport';
        $id = (int)($_GET['id'] ?? 0);
        
        $event = getEvenementById($id, $type);
        if (!$event) {
            $_SESSION['errors'] = ["Événement introuvable"];
            rediriger('/gestionnaire/events&type=' . $type);
        }
        
        $categories = Categorie::findAll();
        require __DIR__ . '/../vues/evenements/gestionnaire/modifier.php';
    }

    // update l'event
    public static function miseAJourEvenement(): void
    {
        self::verifierGestionnaire();
        traiterMiseAJourEvenement('/gestionnaire/events');
    }

    // delete un event
    public static function supprimerEvenement(): void
    {
        self::verifierGestionnaire();
        traiterSuppressionEvenement('/gestionnaire/events');
    }

    /**
     * Affiche la liste complète des bénévoles inscrits à un événement sportif (Gestionnaire)
     * Accessible aux gestionnaires pour tous les événements (pas de restriction par gestionnaire pour l'instant)
     * Affiche tous les créneaux et leurs bénévoles inscrits
     */
    public static function afficherBenevolesGestionnaire(): void
    {
        self::verifierGestionnaire();

        // Récupération de l'ID de l'événement
        $idEvent = (int)($_GET['id'] ?? 0);

        if ($idEvent === 0) {
            $_SESSION['errors'] = ["ID événement manquant"];
            rediriger('/gestionnaire/events&type=sport');
        }

        // Récupération de l'événement
        $event = EvenementSport::findById($idEvent);

        if (!$event) {
            $_SESSION['errors'] = ["Événement sportif introuvable"];
            rediriger('/gestionnaire/events&type=sport');
        }

        // NOTE: Pour l'instant, tous les gestionnaires peuvent voir tous les événements
        // Si besoin de restreindre par gestionnaire, ajouter une vérification ici

        // Récupération du nombre total d'inscrits (bénévoles uniques)
        $nombreInscrits = Participation::obtenirNombreInscritsEvenement($idEvent);

        // Récupération de tous les inscrits avec leurs créneaux
        $inscritsData = Participation::obtenirInscritsPourGestionnaire($idEvent);

        // Organisation des données par créneau
        $creneauxAvecInscrits = [];

        foreach ($inscritsData as $ligne) {
            $idCreneau = $ligne['Id_creneau'];

            // Si le créneau n'existe pas encore dans notre tableau, on le crée
            if (!isset($creneauxAvecInscrits[$idCreneau])) {
                $creneauxAvecInscrits[$idCreneau] = [
                    'id_creneau' => $idCreneau,
                    'type' => $ligne['type_creneau'],
                    'date' => $ligne['Date_creneau'],
                    'heure_debut' => $ligne['Heure_Debut'],
                    'heure_fin' => $ligne['Heure_Fin'],
                    'commentaire' => $ligne['commentaire_creneau'],
                    'benevoles' => []
                ];
            }

            // Si un bénévole est inscrit (Id_Membre n'est pas null), on l'ajoute
            if ($ligne['Id_Membre'] !== null) {
                // Récupérer les libellés des postes préférés
                $postesPreferences = Participation::getPostesLibellesFromJson($ligne['Preference_Poste']);
                
                $creneauxAvecInscrits[$idCreneau]['benevoles'][] = [
                    'id_membre' => $ligne['Id_Membre'],
                    'nom' => $ligne['Nom'],
                    'prenom' => $ligne['Prenom'],
                    'mail' => $ligne['Mail'],
                    'telephone' => $ligne['Telephone'],
                    'date_inscription' => $ligne['Date_inscription'],
                    'presence' => $ligne['Presence'],
                    'preferences_postes' => $postesPreferences
                ];
            }
        }

        // Conversion en tableau indexé pour la vue
        $creneaux = array_values($creneauxAvecInscrits);

        require __DIR__ . '/../vues/evenements/gestionnaire/benevoles_inscrits.php';
    }

    /**
     * Affiche la liste complète des participants inscrits à un événement associatif (Gestionnaire)
     * Accessible aux gestionnaires pour tous les événements
     * Affiche tous les participants avec leurs informations
     */
    public static function afficherParticipantsGestionnaire(): void
    {
        self::verifierGestionnaire();

        // Récupération de l'ID de l'événement
        $idEvent = (int)($_GET['id'] ?? 0);

        if ($idEvent === 0) {
            $_SESSION['errors'] = ["ID événement manquant"];
            rediriger('/gestionnaire/events&type=asso');
        }

        // Récupération de l'événement
        $event = EvenementAsso::findById($idEvent);

        if (!$event) {
            $_SESSION['errors'] = ["Événement associatif introuvable"];
            rediriger('/gestionnaire/events&type=asso');
        }

        // Récupération du nombre total d'inscrits
        $nombreInscrits = Participation::obtenirNombreInscritsEvenementAsso($idEvent);

        // Récupération de tous les participants
        $participants = Participation::obtenirInscritsPourEvenementAsso($idEvent);

        require __DIR__ . '/../vues/evenements/gestionnaire/participants_inscrits.php';
    }

    /**
     * Génère et télécharge un PDF listant tous les participants d'un événement sportif
     * avec leurs régimes alimentaires (restrictions et préférences)
     * Accessible aux gestionnaires pour tous les événements
     */
    public static function genererPDFParticipantsGestionnaire(): void
    {
        self::verifierGestionnaire();

        // Inclusion du service PDF
        require_once __DIR__ . '/../services/PDFService.php';

        // Récupération de l'ID de l'événement
        $idEvent = (int)($_GET['id'] ?? 0);

        if ($idEvent === 0) {
            $_SESSION['errors'] = ["ID événement manquant"];
            rediriger('/gestionnaire/events&type=sport');
        }

        // Récupération de l'événement
        $evenement = EvenementSport::findById($idEvent);

        if (!$evenement) {
            $_SESSION['errors'] = ["Événement sportif introuvable"];
            rediriger('/gestionnaire/events&type=sport');
        }

        // Récupération des participants avec leurs régimes alimentaires
        $participants = EvenementSport::obtenirParticipantsAvecRegimes($idEvent);

        // Génération et envoi du PDF
        PDFService::genererPDFParticipants($evenement, $participants);
    }

    /**
     * Génère et télécharge un PDF listant tous les participants d'un événement associatif
     * avec leurs régimes alimentaires (restrictions et préférences)
     * Accessible aux gestionnaires pour tous les événements
     */
    public static function genererPDFParticipantsAssoGestionnaire(): void
    {
        self::verifierGestionnaire();

        // Inclusion du service PDF
        require_once __DIR__ . '/../services/PDFService.php';

        // Récupération de l'ID de l'événement
        $idEvent = (int)($_GET['id'] ?? 0);

        if ($idEvent === 0) {
            $_SESSION['errors'] = ["ID événement manquant"];
            rediriger('/gestionnaire/events&type=asso');
        }

        // Récupération de l'événement
        $evenement = EvenementAsso::findById($idEvent);

        if (!$evenement) {
            $_SESSION['errors'] = ["Événement associatif introuvable"];
            rediriger('/gestionnaire/events&type=asso');
        }

        // Récupération des participants avec leurs régimes alimentaires
        $participants = EvenementAsso::obtenirParticipantsAvecRegimes($idEvent);

        // Génération et envoi du PDF
        PDFService::genererPDFParticipantsAsso($evenement, $participants);
    }

    /**
     * Marque les présences des bénévoles pour un ou plusieurs créneaux (Gestionnaire)
     * Identique à la fonction admin mais accessible aux gestionnaires
     */
    public static function marquerPresences(): void
    {
        self::verifierGestionnaire();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger('/gestionnaire/events');
        }

        // Vérification CSRF
        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = ['Token de sécurité invalide. Veuillez réessayer.'];
            rediriger('/gestionnaire/events');
        }

        $idCreneau = (int)($_POST['id_creneau'] ?? 0);
        $idEvent = (int)($_POST['id_event'] ?? 0);
        $presences = $_POST['presences'] ?? [];
        $retourUrl = $_POST['retour_url'] ?? '/gestionnaire/events';

        if ($idCreneau === 0) {
            $_SESSION['errors'] = ['ID créneau manquant'];
            rediriger($retourUrl);
        }

        // Vérifier que le créneau existe
        $creneau = Creneau::findById($idCreneau);
        if (!$creneau) {
            $_SESSION['errors'] = ['Créneau introuvable'];
            rediriger($retourUrl);
        }

        // Récupérer tous les inscrits du créneau
        $inscrits = Participation::getInscritsCreneaux($idCreneau);

        // Construire le tableau de présences : 1 si coché, 0 sinon
        $presencesAMarquer = [];
        foreach ($inscrits as $inscrit) {
            $idMembre = $inscrit['Id_Membre'];
            $presencesAMarquer[$idMembre] = isset($presences[$idMembre]) ? 1 : 0;
        }

        // Marquer les présences en masse
        $resultat = Participation::marquerPresencesMasse($idCreneau, $presencesAMarquer);

        if ($resultat['success']) {
            $_SESSION['success'] = "Présences enregistrées avec succès ({$resultat['updated']} mise(s) à jour)";
        } else {
            $_SESSION['errors'] = $resultat['errors'];
        }

        rediriger($retourUrl);
    }

    // Modifie le statut de paiement d'un participant (Gestionnaire)
    public static function modifierPaiement(): void
    {
        self::verifierGestionnaire();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger('/gestionnaire/events');
        }

        $idMembre = (int)($_POST['id_membre'] ?? 0);
        $idEvent = (int)($_POST['id_event'] ?? 0);
        $nouveauStatut = (int)($_POST['nouveau_statut'] ?? 0);

        if ($idMembre && $idEvent) {
             Participation::updatePaiement($idMembre, $idEvent, $nouveauStatut);
             $_SESSION['success'] = "Statut de paiement mis à jour.";
        }

        rediriger('/gestionnaire/events/participants&id=' . $idEvent);
    }
}