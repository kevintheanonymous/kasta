<?php

require_once __DIR__ . '/../fonctions_evenements.php';
require_once __DIR__ . '/../models/Creneau.php';
require_once __DIR__ . '/../models/EvenementSport.php';
require_once __DIR__ . '/../models/Participation.php';
require_once __DIR__ . '/../models/Poste.php';
require_once __DIR__ . '/../models/CreneauPoste.php';

class ControleurCreneau {

    private const ROUTE_EVENTS = '/admin/events';
    private const ERR_EVENEMENT = 'Événement introuvable';
    private const CSRF_ERR = 'Token de sécurité invalide. Veuillez réessayer.';

    private static function verifierAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'gestionnaire') {
            rediriger('/connexion');
            exit;
        }
    }

    public static function index() {
        self::verifierAdmin();
        $idEvent = $_GET['id_event'] ?? null;

        if (!$idEvent) {
            rediriger(self::ROUTE_EVENTS);
            exit;
        }

        $event = EvenementSport::findById($idEvent);
        if (!$event) {
            $_SESSION['errors'] = [self::ERR_EVENEMENT];
            rediriger(self::ROUTE_EVENTS);
            exit;
        }

        $creneaux = Creneau::findByEvent($idEvent);
        $postes = Poste::findAll();
        require_once __DIR__ . '/../vues/admin/creneaux/liste.php';
    }

    public static function create() {
        self::verifierAdmin();
        $idEvent = $_GET['id_event'] ?? null;

        if (!$idEvent) {
            rediriger(self::ROUTE_EVENTS);
            exit;
        }

        $event = EvenementSport::findById($idEvent);
        if (!$event) {
            $_SESSION['errors'] = [self::ERR_EVENEMENT];
            rediriger(self::ROUTE_EVENTS);
            exit;
        }

        $postes = Poste::findAll();

        require_once __DIR__ . '/../vues/admin/creneaux/creer.php';
    }

    public static function store() {
        self::verifierAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = [self::CSRF_ERR];
            $idEvent = $_POST['id_event_sportif'] ?? '';
            rediriger("/admin/creneaux&id_event=$idEvent");
            exit;
        }

        $idEvent = $_POST['id_event_sportif'];
        $event = EvenementSport::findById($idEvent);
        if (!$event) {
            $_SESSION['errors'] = [self::ERR_EVENEMENT];
            rediriger(self::ROUTE_EVENTS);
            exit;
        }

        $data = [
            'id_event_sportif' => $idEvent,
            'type' => $_POST['type'],
            'commentaire' => $_POST['commentaire'] ?? '',
            'date_creneau' => $_POST['date_creneau'],
            'heure_debut' => $_POST['heure_debut'],
            'heure_fin' => $_POST['heure_fin']
        ];

        $erreur = self::validerHorairesCreneau($data, $event);
        if ($erreur !== null) {
            $_SESSION['errors'] = [$erreur];
            rediriger("/admin/creneaux&id_event=$idEvent");
            exit;
        }

        $idCreneau = Creneau::create($data);
        if ($idCreneau) {
            $postesSelectionnes = $_POST['postes'] ?? [];
            if (!empty($postesSelectionnes)) {
                CreneauPoste::lierPostesACreneau($idCreneau, $postesSelectionnes);
            }
            $_SESSION['success'] = "Créneau créé avec succès";
        } else {
            $_SESSION['errors'] = ["Erreur lors de la création du créneau"];
        }

        rediriger("/admin/creneaux&id_event=$idEvent");
        exit;
    }

    public static function edit() {
        self::verifierAdmin();
        $id = $_GET['id'] ?? null;
        if (!$id) {
            rediriger(self::ROUTE_EVENTS);
            exit;
        }

        $creneau = Creneau::findById($id);
        if (!$creneau) {
            rediriger(self::ROUTE_EVENTS);
            exit;
        }

        $event = EvenementSport::findById($creneau['Id_Event_sportif']);
        $postes = Poste::findAll();
        $postesActuels = CreneauPoste::getPostesPourCreneau($id);
        $postesActuelsIds = array_column($postesActuels, 'Id_Poste');

        require_once __DIR__ . '/../vues/admin/creneaux/modifier.php';
    }

    public static function update() {
        self::verifierAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $id = $_POST['id_creneau'];
        $idEvent = $_POST['id_event_sportif'];

        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = [self::CSRF_ERR];
            rediriger("/admin/creneaux/edit&id=$id");
            exit;
        }

        $event = EvenementSport::findById($idEvent);
        if (!$event) {
            $_SESSION['errors'] = [self::ERR_EVENEMENT];
            rediriger(self::ROUTE_EVENTS);
            exit;
        }

        $data = [
            'type' => $_POST['type'],
            'commentaire' => $_POST['commentaire'] ?? '',
            'date_creneau' => $_POST['date_creneau'],
            'heure_debut' => $_POST['heure_debut'],
            'heure_fin' => $_POST['heure_fin']
        ];

        $erreur = self::validerHorairesCreneau($data, $event);
        if ($erreur !== null) {
            $_SESSION['errors'] = [$erreur];
            rediriger("/admin/creneaux/edit&id=$id");
            exit;
        }

        if (Creneau::update($id, $data)) {
            CreneauPoste::lierPostesACreneau($id, $_POST['postes'] ?? []);
            $_SESSION['success'] = "Créneau mis à jour avec succès";
        } else {
            $_SESSION['errors'] = ["Erreur lors de la mise à jour du créneau"];
        }

        rediriger("/admin/creneaux&id_event=$idEvent");
        exit;
    }

    private static function validerHorairesCreneau(array $data, array $event): ?string
    {
        $debut = $data['date_creneau'] . ' ' . $data['heure_debut'];
        if (strlen($data['heure_debut']) === 5) {
            $debut .= ':00';
        }
        $fin = $data['date_creneau'] . ' ' . $data['heure_fin'];
        if (strlen($data['heure_fin']) === 5) {
            $fin .= ':00';
        }
        if ($debut >= $fin) {
            return "L'heure de début doit être strictement inférieure à l'heure de fin.";
        }
        if ($debut < $event['date_cloture']) {
            return "Le créneau (début : $debut) doit commencer après la date de clôture des inscriptions ({$event['date_cloture']}).";
        }
        return null;
    }

    public static function delete() {
        self::verifierAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifierTokenCSRF()) {
                $_SESSION['errors'] = [self::CSRF_ERR];
                rediriger(self::ROUTE_EVENTS);
                exit;
            }

            $id = $_POST['id_creneau'];
            $creneau = Creneau::findById($id);

            if ($creneau) {
                $idEvent = $creneau['Id_Event_sportif'];
                if (Creneau::delete($id)) {
                    $_SESSION['success'] = "Créneau supprimé avec succès";
                } else {
                    $_SESSION['errors'] = ["Erreur lors de la suppression"];
                }
                rediriger("/admin/creneaux&id_event=$idEvent");
                exit;
            }

            rediriger(self::ROUTE_EVENTS);
            exit;
        }
    }

    public static function inscrits() {
        self::verifierAdmin();
        $id = $_GET['id'] ?? null;
        if (!$id) {
            rediriger(self::ROUTE_EVENTS);
            exit;
        }

        $creneau = Creneau::findById($id);
        if (!$creneau) {
            rediriger(self::ROUTE_EVENTS);
            exit;
        }

        $event = EvenementSport::findById($creneau['Id_Event_sportif']);
        $inscrits = Participation::getInscritsCreneaux($id);

        require_once __DIR__ . '/../vues/admin/creneaux/inscrits.php';
    }

    public static function marquerPresences() {
        self::verifierAdmin();
        traiterMarquerPresences(self::ROUTE_EVENTS);
    }
}
