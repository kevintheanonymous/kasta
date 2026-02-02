<?php
require_once __DIR__ . '/../fonctions_evenements.php';
require_once __DIR__ . '/../models/BaseDeDonnees.php';
require_once __DIR__ . '/../models/Creneau.php';
require_once __DIR__ . '/../models/Participation.php';
require_once __DIR__ . '/../models/EvenementSport.php';
require_once __DIR__ . '/../models/EvenementAsso.php';

class ControleurEvenement {

    private static function verifierAdmin(): void
    {
        verifierAccesAdminOuGestionnaire();
    }

    public static function adminIndex(): void
    {
        self::verifierAdmin();
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
        require __DIR__ . '/../vues/evenements/admin/liste.php';
    }

    public static function adminCreate(): void
    {
        self::verifierAdmin();
        $type = $_GET['type'] ?? 'sport';
        // Pour les événements sportifs, on redirige vers le formulaire unifié (événement + créneaux)
        if ($type === 'sport') {
            rediriger('/admin/events/create-with-slots&type=sport');
        }

        // Pour les événements associatifs, on conserve le formulaire existant
        $categories = Categorie::findAll();
        require __DIR__ . '/../vues/evenements/admin/creer.php';
    }

    public static function adminCreateWithSlots(): void
    {
        self::verifierAdmin();
        $type = $_GET['type'] ?? 'sport';
        if ($type !== 'sport') {
            $_SESSION['errors'] = ["La création combinée n'est disponible que pour les événements sportifs."];
            rediriger('/admin/events&type=' . $type);
        }
        $categories = Categorie::findAll();
        $postes = Poste::findAll();
        $formAction = url('/admin/events/store-with-slots');
        $cancelUrl = url('/admin/events&type=' . $type);
        require __DIR__ . '/../vues/evenements/admin/creer_evenement_creneaux.php';
    }

    public static function adminStore(): void
    {
        self::verifierAdmin();
        traiterCreationEvenement('/admin/events');
    }

    public static function adminStoreWithSlots(): void
    {
        self::verifierAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger('/admin/events/create-with-slots&type=sport');
        }

        // CSRF
        validerCSRFOuRediriger('/admin/events/create-with-slots&type=sport');

        $type = $_POST['type'] ?? 'sport';
        if ($type !== 'sport') {
            $_SESSION['errors'] = ["La création combinée n'est disponible que pour les événements sportifs."];
            rediriger('/admin/events&type=' . $type);
        }

        $data = extraireDonneesEvenementBase();
        $validation = validerDatesEvenement($data);
        if (!$validation['valid']) {
            $_SESSION['errors'] = [$validation['error']];
            rediriger('/admin/events/create-with-slots&type=' . $type);
        }

        $data = preparerDonneesEvenementSport($data);

        $creneauxPost = $_POST['creneaux'] ?? [];
        $creneaux = normaliserCreneauxDepuisPost($creneauxPost);

        // s'assurer d'avoir un datetime cohérent pour la clôture
        $dateCloture = $data['date_cloture'];
        if (strlen($dateCloture) === 10) {
            $dateCloture .= ' 00:00:00';
        }

        $errors = validerCreneaux($creneaux, $dateCloture);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            rediriger('/admin/events/create-with-slots&type=' . $type);
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
            rediriger('/admin/events/create-with-slots&type=' . $type);
        }
    }

    public static function adminEdit(): void
    {
        self::verifierAdmin();
        $type = $_GET['type'] ?? 'sport';
        $id = (int)($_GET['id'] ?? 0);
        
        $event = getEvenementById($id, $type);
        if (!$event) {
            $_SESSION['errors'] = ["Événement introuvable"];
            rediriger('/admin/events&type=' . $type);
        }
        
        $categories = Categorie::findAll();
        require __DIR__ . '/../vues/evenements/admin/modifier.php';
    }

    // traite la modif
    public static function adminUpdate(): void
    {
        self::verifierAdmin();
        traiterMiseAJourEvenement('/admin/events');
    }

    // supprime un event
    public static function adminDelete(): void
    {
        self::verifierAdmin();
        traiterSuppressionEvenement('/admin/events');
    }

    // page de detail d'un event (pour tout le monde)
    public static function detail(): void
    {
        $type = $_GET['type'] ?? 'sport';
        $id = (int)($_GET['id'] ?? 0);

        if (!$id) {
            rediriger('/');
        }

        $event = getEvenementById($id, $type);

        if (!$event) {
            rediriger('/');
        }

        require __DIR__ . '/../vues/evenements/detail.php';
    }

    //Affiche la liste complète des bénévoles inscrits à un événement sportif (Admin)
    //Accessible uniquement aux administrateurs
    //Affiche tous les créneaux et leurs bénévoles inscrits
    public static function afficherBenevolesAdmin(): void
    {
        self::verifierAdmin();

        // Récupération de l'ID de l'événement
        $idEvent = (int)($_GET['id'] ?? 0);

        if ($idEvent === 0) {
            $_SESSION['errors'] = ["ID événement manquant"];
            rediriger('/admin/events&type=sport');
        }

        // Récupération de l'événement
        $event = EvenementSport::findById($idEvent);

        if (!$event) {
            $_SESSION['errors'] = ["Événement sportif introuvable"];
            rediriger('/admin/events&type=sport');
        }

        // Récupération du nombre total d'inscrits (bénévoles uniques)
        $nombreInscrits = Participation::obtenirNombreInscritsEvenement($idEvent);

        // Récupération de tous les inscrits avec leurs créneaux
        $inscritsData = Participation::obtenirInscritsPourEvenement($idEvent);

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

        require __DIR__ . '/../vues/evenements/admin/benevoles_inscrits.php';
    }

    //Affiche la liste complète des participants inscrits à un événement associatif (Admin)
    //Accessible uniquement aux administrateurs
    //Affiche tous les participants avec leurs informations
    public static function afficherParticipantsAdmin(): void
    {
        self::verifierAdmin();

        // Récupération de l'ID de l'événement
        $idEvent = (int)($_GET['id'] ?? 0);

        if ($idEvent === 0) {
            $_SESSION['errors'] = ["ID événement manquant"];
            rediriger('/admin/events&type=asso');
        }

        // Récupération de l'événement
        $event = EvenementAsso::findById($idEvent);

        if (!$event) {
            $_SESSION['errors'] = ["Événement associatif introuvable"];
            rediriger('/admin/events&type=asso');
        }

        // Récupération du nombre total d'inscrits
        $nombreInscrits = Participation::obtenirNombreInscritsEvenementAsso($idEvent);

        // Récupération de tous les participants
        $participants = Participation::obtenirInscritsPourEvenementAsso($idEvent);

        require __DIR__ . '/../vues/evenements/admin/participants_inscrits.php';
    }

    //Génère et télécharge un PDF listant tous les participants d'un événement sportif
    //avec leurs régimes alimentaires (restrictions et préférences)
    //Accessible uniquement aux administrateurs
    //
    public static function genererPDFParticipantsAdmin(): void
    {
        self::verifierAdmin();

        // Inclusion du service PDF
        require_once __DIR__ . '/../services/PDFService.php';

        // Récupération de l'ID de l'événement
        $idEvent = (int)($_GET['id'] ?? 0);

        if ($idEvent === 0) {
            $_SESSION['errors'] = ["ID événement manquant"];
            rediriger('/admin/events&type=sport');
        }

        // Récupération de l'événement
        $evenement = EvenementSport::findById($idEvent);

        if (!$evenement) {
            $_SESSION['errors'] = ["Événement sportif introuvable"];
            rediriger('/admin/events&type=sport');
        }

        // Récupération des participants avec leurs régimes alimentaires
        $participants = EvenementSport::obtenirParticipantsAvecRegimes($idEvent);

        // Génération et envoi du PDF
        PDFService::genererPDFParticipants($evenement, $participants);
    }

    
    //Génère et télécharge un PDF listant tous les participants d'un événement associatif
    //avec leurs régimes alimentaires (restrictions et préférences)
    // Accessible uniquement aux administrateurs
    public static function genererPDFParticipantsAssoAdmin(): void
    {
        self::verifierAdmin();

        // Inclusion du service PDF
        require_once __DIR__ . '/../services/PDFService.php';

        // Récupération de l'ID de l'événement
        $idEvent = (int)($_GET['id'] ?? 0);

        if ($idEvent === 0) {
            $_SESSION['errors'] = ["ID événement manquant"];
            rediriger('/admin/events&type=asso');
        }

        // Récupération de l'événement
        $evenement = EvenementAsso::findById($idEvent);

        if (!$evenement) {
            $_SESSION['errors'] = ["Événement associatif introuvable"];
            rediriger('/admin/events&type=asso');
        }

        // Récupération des participants avec leurs régimes alimentaires
        $participants = EvenementAsso::obtenirParticipantsAvecRegimes($idEvent);

        // Génération et envoi du PDF
        PDFService::genererPDFParticipantsAsso($evenement, $participants);
    }

    // Modifie le statut de paiement d'un participant (Admin)
    public static function modifierPaiement(): void
    {
        self::verifierAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger('/admin/events');
        }

        $idMembre = (int)($_POST['id_membre'] ?? 0);
        $idEvent = (int)($_POST['id_event'] ?? 0);
        $nouveauStatut = (int)($_POST['nouveau_statut'] ?? 0);

        if ($idMembre && $idEvent) {
             Participation::updatePaiement($idMembre, $idEvent, $nouveauStatut);
             $_SESSION['success'] = "Statut de paiement mis à jour.";
        }

        rediriger('/admin/events/participants&id=' . $idEvent);
    }
}