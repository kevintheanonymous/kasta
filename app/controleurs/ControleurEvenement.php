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
        require __DIR__ . '/../vues/evenements/admin/liste.php';
    }

    public static function adminCreate(): void
    {
        self::verifierAdmin();
        $type = $_GET['type'] ?? 'sport';
        if ($type === 'sport') {
            rediriger('/admin/events/create-with-slots&type=sport');
        }
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
        $creneaux = normaliserCreneauxDepuisPost($_POST['creneaux'] ?? []);

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
            error_log('ControleurEvenement::adminStoreWithSlots error: ' . $e->getMessage());
            $_SESSION['errors'] = ["Erreur lors de la création. Veuillez réessayer."];
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

    public static function adminUpdate(): void
    {
        self::verifierAdmin();
        traiterMiseAJourEvenement('/admin/events');
    }

    public static function adminDelete(): void
    {
        self::verifierAdmin();
        traiterSuppressionEvenement('/admin/events');
    }

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

    public static function afficherBenevolesAdmin(): void
    {
        self::verifierAdmin();
        afficherBenevolesPourEvenement('/admin/events', 'evenements/admin/benevoles_inscrits.php');
    }

    public static function afficherParticipantsAdmin(): void
    {
        self::verifierAdmin();
        afficherParticipantsPourEvenement('/admin/events', 'evenements/admin/participants_inscrits.php');
    }

    public static function genererPDFParticipantsAdmin(): void
    {
        self::verifierAdmin();
        genererPDFParticipantsSport('/admin/events');
    }

    public static function genererPDFParticipantsAssoAdmin(): void
    {
        self::verifierAdmin();
        genererPDFParticipantsAsso('/admin/events');
    }

    public static function modifierPaiement(): void
    {
        self::verifierAdmin();
        traiterModifierPaiement('/admin/events');
    }
}
