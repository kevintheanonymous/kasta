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

        require __DIR__ . '/../vues/gestionnaire/tableau_de_bord.php';
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
        require __DIR__ . '/../vues/evenements/gestionnaire/liste.php';
    }

    public static function creerEvenement(): void
    {
        self::verifierGestionnaire();
        $type = $_GET['type'] ?? 'sport';
        if ($type === 'sport') {
            rediriger('/gestionnaire/events/create-with-slots&type=sport');
        }
        $categories = Categorie::findAll();
        require __DIR__ . '/../vues/evenements/gestionnaire/creer.php';
    }

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

    public static function enregistrerEvenement(): void
    {
        self::verifierGestionnaire();
        traiterCreationEvenement('/gestionnaire/events');
    }

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
            error_log('ControleurGestionnaire::enregistrerEvenementAvecCreneaux error: ' . $e->getMessage());
            $_SESSION['errors'] = ["Erreur lors de la création. Veuillez réessayer."];
            rediriger('/gestionnaire/events/create-with-slots&type=' . $type);
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
            rediriger('/gestionnaire/events&type=' . $type);
        }

        $categories = Categorie::findAll();
        require __DIR__ . '/../vues/evenements/gestionnaire/modifier.php';
    }

    public static function miseAJourEvenement(): void
    {
        self::verifierGestionnaire();
        traiterMiseAJourEvenement('/gestionnaire/events');
    }

    public static function supprimerEvenement(): void
    {
        self::verifierGestionnaire();
        traiterSuppressionEvenement('/gestionnaire/events');
    }

    public static function afficherBenevolesGestionnaire(): void
    {
        self::verifierGestionnaire();
        afficherBenevolesPourEvenement('/gestionnaire/events', 'evenements/gestionnaire/benevoles_inscrits.php');
    }

    public static function afficherParticipantsGestionnaire(): void
    {
        self::verifierGestionnaire();
        afficherParticipantsPourEvenement('/gestionnaire/events', 'evenements/gestionnaire/participants_inscrits.php');
    }

    public static function genererPDFParticipantsGestionnaire(): void
    {
        self::verifierGestionnaire();
        genererPDFParticipantsSport('/gestionnaire/events');
    }

    public static function genererPDFParticipantsAssoGestionnaire(): void
    {
        self::verifierGestionnaire();
        genererPDFParticipantsAsso('/gestionnaire/events');
    }

    public static function marquerPresences(): void
    {
        self::verifierGestionnaire();
        traiterMarquerPresences('/gestionnaire/events');
    }

    public static function modifierPaiement(): void
    {
        self::verifierGestionnaire();
        traiterModifierPaiement('/gestionnaire/events');
    }
}
