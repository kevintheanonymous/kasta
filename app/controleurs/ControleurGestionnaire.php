<?php
require_once __DIR__ . '/../fonctions_evenements.php';
require_once __DIR__ . '/../models/BaseDeDonnees.php';
require_once __DIR__ . '/../models/Creneau.php';
require_once __DIR__ . '/../models/Participation.php';
require_once __DIR__ . '/../models/EvenementSport.php';
require_once __DIR__ . '/../models/EvenementAsso.php';
require_once __DIR__ . '/../models/Membre.php';

class ControleurGestionnaire
{
    private const ROUTE_EVENTS = '/gestionnaire/events';
    private const ROUTE_CREATE_SPORT = '/gestionnaire/events/create-with-slots&type=sport';
    private const ROUTE_CREATE_TYPE = '/gestionnaire/events/create-with-slots&type=';
    private const ROUTE_LIST_TYPE = '/gestionnaire/events&type=';

    private static function verifierGestionnaire(): void
    {
        verifierAccesAdminOuGestionnaire();
    }

    public static function afficherDashboard(): void
    {
        self::verifierGestionnaire();
        $countSport = count(EvenementSport::findAll());
        $countAsso = count(EvenementAsso::findAll(true));

        $userId = $_SESSION['user_id'] ?? 0;
        $isAdherent = false;
        if ($userId) {
            $membre = Membre::getMembreParId($userId);
            $isAdherent = ($membre && $membre['Adherent'] == 1);
        }
        $evenements_sport = EvenementSport::findAllPublic();
        $evenements_asso = EvenementAsso::findAll(false, $isAdherent, false);

        require_once __DIR__ . '/../vues/gestionnaire/tableau_de_bord.php';
    }

    public static function afficherEvenements(): void
    {
        self::verifierGestionnaire();
        $type = $_GET['type'] ?? 'sport';
        $events = getEvenementsByType($type);

        if ($type === 'sport') {
            foreach ($events as &$event) {
                $event['nb_inscrits'] = Participation::obtenirNombreInscritsEvenement($event['id_event_sport']);
            }
            unset($event);
        }

        if ($type === 'asso') {
            foreach ($events as &$event) {
                $event['nb_inscrits'] = Participation::obtenirNombreInscritsEvenementAsso($event['id_event_asso']);
            }
            unset($event);
        }

        $categories = Categorie::findAll();
        require_once __DIR__ . '/../vues/evenements/gestionnaire/liste.php';
    }

    public static function creerEvenement(): void
    {
        self::verifierGestionnaire();
        $type = $_GET['type'] ?? 'sport';
        if ($type === 'sport') {
            rediriger(self::ROUTE_CREATE_SPORT);
        }
        $categories = Categorie::findAll();
        require_once __DIR__ . '/../vues/evenements/gestionnaire/creer.php';
    }

    public static function creerEvenementAvecCreneaux(): void
    {
        self::verifierGestionnaire();
        $type = $_GET['type'] ?? 'sport';
        if ($type !== 'sport') {
            $_SESSION['errors'] = ["La création combinée n'est disponible que pour les événements sportifs."];
            rediriger(self::ROUTE_LIST_TYPE . $type);
        }

        $categories = Categorie::findAll();
        $postes = Poste::findAll();
        $formAction = url('/gestionnaire/events/store-with-slots');
        $cancelUrl = url(self::ROUTE_LIST_TYPE . $type);
        require_once __DIR__ . '/../vues/evenements/admin/creer_evenement_creneaux.php';
    }

    public static function enregistrerEvenement(): void
    {
        self::verifierGestionnaire();
        traiterCreationEvenement(self::ROUTE_EVENTS);
    }

    public static function enregistrerEvenementAvecCreneaux(): void
    {
        self::verifierGestionnaire();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger(self::ROUTE_CREATE_SPORT);
        }

        validerCSRFOuRediriger(self::ROUTE_CREATE_SPORT);

        $type = $_POST['type'] ?? 'sport';
        if ($type !== 'sport') {
            $_SESSION['errors'] = ["La création combinée n'est disponible que pour les événements sportifs."];
            rediriger(self::ROUTE_LIST_TYPE . $type);
        }

        $data = extraireDonneesEvenementBase();
        $validation = validerDatesEvenement($data);
        if (!$validation['valid']) {
            $_SESSION['errors'] = [$validation['error']];
            rediriger(self::ROUTE_CREATE_TYPE . $type);
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
            rediriger(self::ROUTE_CREATE_TYPE . $type);
        }

        $pdo = BaseDeDonnees::getConnexion();

        try {
            $pdo->beginTransaction();

            $eventId = EvenementSport::createAndReturnId($data);
            if (!$eventId) {
                throw new RuntimeException("Échec de la création de l'événement.");
            }

            $okCreneaux = Creneau::createMany($eventId, $creneaux);
            if (!$okCreneaux) {
                throw new RuntimeException("Échec de la création des créneaux.");
            }

            $pdo->commit();
            $_SESSION['success'] = "Événement et créneaux créés avec succès.";
            rediriger('/admin/creneaux&id_event=' . $eventId);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('ControleurGestionnaire::enregistrerEvenementAvecCreneaux error: ' . $e->getMessage());
            $_SESSION['errors'] = ["Erreur lors de la création. Veuillez réessayer."];
            rediriger(self::ROUTE_CREATE_TYPE . $type);
        }
    }

    public static function modifierEvenement(): void
    {
        self::verifierGestionnaire();
        $type = $_GET['type'] ?? 'sport';
        $id = (int)($_GET['id'] ?? 0);

        $event = getEvenementById($id, $type);
        if (!$event) {
            $_SESSION['errors'] = ["Événement introuvable"];
            rediriger(self::ROUTE_LIST_TYPE . $type);
        }

        $categories = Categorie::findAll();
        require_once __DIR__ . '/../vues/evenements/gestionnaire/modifier.php';
    }

    public static function miseAJourEvenement(): void
    {
        self::verifierGestionnaire();
        traiterMiseAJourEvenement(self::ROUTE_EVENTS);
    }

    public static function supprimerEvenement(): void
    {
        self::verifierGestionnaire();
        traiterSuppressionEvenement(self::ROUTE_EVENTS);
    }

    public static function afficherBenevolesGestionnaire(): void
    {
        self::verifierGestionnaire();
        afficherBenevolesPourEvenement(self::ROUTE_EVENTS, 'evenements/gestionnaire/benevoles_inscrits.php');
    }

    public static function afficherParticipantsGestionnaire(): void
    {
        self::verifierGestionnaire();
        afficherParticipantsPourEvenement(self::ROUTE_EVENTS, 'evenements/gestionnaire/participants_inscrits.php');
    }

    public static function genererPDFParticipantsGestionnaire(): void
    {
        self::verifierGestionnaire();
        genererPDFParticipantsSport(self::ROUTE_EVENTS);
    }

    public static function genererPDFParticipantsAssoGestionnaire(): void
    {
        self::verifierGestionnaire();
        genererPDFParticipantsAsso(self::ROUTE_EVENTS);
    }

    public static function marquerPresences(): void
    {
        self::verifierGestionnaire();
        traiterMarquerPresences(self::ROUTE_EVENTS);
    }

    public static function modifierPaiement(): void
    {
        self::verifierGestionnaire();
        traiterModifierPaiement(self::ROUTE_EVENTS);
    }
}
