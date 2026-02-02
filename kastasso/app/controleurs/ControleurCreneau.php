<?php

require_once __DIR__ . '/../models/Creneau.php';
require_once __DIR__ . '/../models/EvenementSport.php';
require_once __DIR__ . '/../models/Participation.php';
require_once __DIR__ . '/../models/Poste.php';
require_once __DIR__ . '/../models/CreneauPoste.php';

class ControleurCreneau {
    
    private static function verifierAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_type']) || ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'gestionnaire')) {
            rediriger('/connexion');
            exit;
        }
    }

    public static function index() {
        self::verifierAdmin();
        $idEvent = $_GET['id_event'] ?? null;
        
        if (!$idEvent) {
            rediriger('/admin/events');
            exit;
        }

        $event = EvenementSport::findById($idEvent);
        if (!$event) {
            $_SESSION['errors'] = ["Événement introuvable"];
            rediriger('/admin/events');
            exit;
        }

        $creneaux = Creneau::findByEvent($idEvent);
        $postes = Poste::findAll(); // on charge les postes pour le formulaire d'ajout inline
        require __DIR__ . '/../vues/admin/creneaux/liste.php';
    }

    public static function create() {
        self::verifierAdmin();
        $idEvent = $_GET['id_event'] ?? null;

        if (!$idEvent) {
            rediriger('/admin/events');
            exit;
        }

        $event = EvenementSport::findById($idEvent);
        if (!$event) {
            $_SESSION['errors'] = ["Événement introuvable"];
            rediriger('/admin/events');
            exit;
        }

        // on charge tous les postes disponibles
        $postes = Poste::findAll();

        require __DIR__ . '/../vues/admin/creneaux/creer.php';
    }

    public static function store() {
        self::verifierAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérification CSRF
            if (!verifierTokenCSRF()) {
                $_SESSION['errors'] = ['Token de sécurité invalide. Veuillez réessayer.'];
                $idEvent = $_POST['id_event_sportif'] ?? '';
                rediriger("/admin/creneaux&id_event=$idEvent");
                exit;
            }
            
            $idEvent = $_POST['id_event_sportif'];
            
            // Récupération de l'événement pour validation des dates
            $event = EvenementSport::findById($idEvent);
            if (!$event) {
                $_SESSION['errors'] = ["Événement introuvable"];
                rediriger('/admin/events');
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

            // check : le creneau doit commencer apres la cloture des inscriptions
            $creneauDebut = $data['date_creneau'] . ' ' . $data['heure_debut'];
            // on rajoute les secondes si y'en a pas
            if (strlen($data['heure_debut']) === 5) {
                $creneauDebut .= ':00';
            }

            // heure debut < heure fin
            $creneauFin = $data['date_creneau'] . ' ' . $data['heure_fin'];
            if (strlen($data['heure_fin']) === 5) {
                $creneauFin .= ':00';
            }

            if ($creneauDebut >= $creneauFin) {
                $_SESSION['errors'] = ["L'heure de début doit être strictement inférieure à l'heure de fin."];
                rediriger("/admin/creneaux&id_event=$idEvent");
                exit;
            }

            if ($creneauDebut < $event['date_cloture']) {
                $_SESSION['errors'] = ["Le créneau (début : $creneauDebut) doit commencer après la date de clôture des inscriptions ({$event['date_cloture']})."];
                rediriger("/admin/creneaux&id_event=$idEvent");
                exit;
            }

            $idCreneau = Creneau::create($data);
            if ($idCreneau) {
                // on lie les postes au creneau si y'en a
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
    }

    public static function edit() {
        self::verifierAdmin();
        $id = $_GET['id'] ?? null;
        if (!$id) {
            rediriger('/admin/events');
            exit;
        }

        $creneau = Creneau::findById($id);
        if (!$creneau) {
            rediriger('/admin/events');
            exit;
        }

        $event = EvenementSport::findById($creneau['Id_Event_sportif']);

        // on charge tous les postes dispo
        $postes = Poste::findAll();

        // on recupere les postes actuellement lies a ce creneau
        $postesActuels = CreneauPoste::getPostesPourCreneau($id);
        $postesActuelsIds = array_column($postesActuels, 'Id_Poste');

        require __DIR__ . '/../vues/admin/creneaux/modifier.php';
    }

    public static function update() {
        self::verifierAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_creneau'];
            $idEvent = $_POST['id_event_sportif'];
            
            // Vérification CSRF
            if (!verifierTokenCSRF()) {
                $_SESSION['errors'] = ['Token de sécurité invalide. Veuillez réessayer.'];
                rediriger("/admin/creneaux/edit&id=$id");
                exit;
            }
            
            // Récupération de l'événement pour validation des dates
            $event = EvenementSport::findById($idEvent);
            if (!$event) {
                $_SESSION['errors'] = ["Événement introuvable"];
                rediriger('/admin/events');
                exit;
            }

            $data = [
                'type' => $_POST['type'],
                'commentaire' => $_POST['commentaire'] ?? '',
                'date_creneau' => $_POST['date_creneau'],
                'heure_debut' => $_POST['heure_debut'],
                'heure_fin' => $_POST['heure_fin']
            ];

            // pareil, le creneau doit etre apres la cloture
            $creneauDebut = $data['date_creneau'] . ' ' . $data['heure_debut'];
            if (strlen($data['heure_debut']) === 5) {
                $creneauDebut .= ':00';
            }

            // heure debut < heure fin
            $creneauFin = $data['date_creneau'] . ' ' . $data['heure_fin'];
            if (strlen($data['heure_fin']) === 5) {
                $creneauFin .= ':00';
            }

            if ($creneauDebut >= $creneauFin) {
                $_SESSION['errors'] = ["L'heure de début doit être strictement inférieure à l'heure de fin."];
                rediriger("/admin/creneaux/edit&id=$id");
                exit;
            }

            if ($creneauDebut < $event['date_cloture']) {
                $_SESSION['errors'] = ["Le créneau (début : $creneauDebut) doit commencer après la date de clôture des inscriptions ({$event['date_cloture']})."];
                rediriger("/admin/creneaux/edit&id=$id");
                exit;
            }

            if (Creneau::update($id, $data)) {
                // ensuite on met a jour les postes lies au creneau
                $postesSelectionnes = $_POST['postes'] ?? [];
                CreneauPoste::lierPostesACreneau($id, $postesSelectionnes);

                $_SESSION['success'] = "Créneau mis à jour avec succès";
            } else {
                $_SESSION['errors'] = ["Erreur lors de la mise à jour du créneau"];
            }

            rediriger("/admin/creneaux&id_event=$idEvent");
            exit;
        }
    }

    public static function delete() {
        self::verifierAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérification CSRF
            if (!verifierTokenCSRF()) {
                $_SESSION['errors'] = ['Token de sécurité invalide. Veuillez réessayer.'];
                rediriger('/admin/events');
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
            
            rediriger('/admin/events');
            exit;
        }
    }

    public static function inscrits() {
        self::verifierAdmin();
        $id = $_GET['id'] ?? null;
        if (!$id) {
            rediriger('/admin/events');
            exit;
        }

        $creneau = Creneau::findById($id);
        if (!$creneau) {
            rediriger('/admin/events');
            exit;
        }

        $event = EvenementSport::findById($creneau['Id_Event_sportif']);
        $inscrits = Participation::getInscritsCreneaux($id);

        require __DIR__ . '/../vues/admin/creneaux/inscrits.php';
    }

    //Marque les présences des bénévoles pour un ou plusieurs créneaux
    //Traite un formulaire avec des checkboxes pour chaque bénévole
    public static function marquerPresences() {
        self::verifierAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger('/admin/events');
            exit;
        }

        // Vérification CSRF
        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = ['Token de sécurité invalide. Veuillez réessayer.'];
            rediriger('/admin/events');
            exit;
        }

        $idCreneau = (int)($_POST['id_creneau'] ?? 0);
        $idEvent = (int)($_POST['id_event'] ?? 0);
        $presences = $_POST['presences'] ?? [];
        $retourUrl = $_POST['retour_url'] ?? '/admin/events';

        if ($idCreneau === 0) {
            $_SESSION['errors'] = ['ID créneau manquant'];
            rediriger($retourUrl);
            exit;
        }

        // Vérifier que le créneau existe
        $creneau = Creneau::findById($idCreneau);
        if (!$creneau) {
            $_SESSION['errors'] = ['Créneau introuvable'];
            rediriger($retourUrl);
            exit;
        }

        // Récupérer tous les inscrits du créneau
        $inscrits = Participation::getInscritsCreneaux($idCreneau);

        // Construire le tableau de présences : 1 si coché, 0 sinon
        $presencesAMarquer = [];
        foreach ($inscrits as $inscrit) {
            $idMembre = $inscrit['Id_Membre'];
            // Si la checkbox est cochée, elle sera dans le tableau $presences
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
        exit;
    }
}
