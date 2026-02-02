<?php

require_once __DIR__ . '/../models/Poste.php';

class ControleurPoste {

    private static function verifierAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_type']) || ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'gestionnaire')) {
            rediriger('/connexion');
        }
    }

    public static function index() {
        self::verifierAdmin();
        $postes = Poste::findAll();
        require __DIR__ . '/../vues/admin/postes/liste.php';
    }

    public static function create() {
        self::verifierAdmin();
        require __DIR__ . '/../vues/admin/postes/creer.php';
    }

    public static function store() {
        self::verifierAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifierTokenCSRF()) {
                $_SESSION['errors'] = ['Token de sécurité invalide. Veuillez réessayer.'];
                rediriger('/admin/postes/create');
                exit;
            }

            $libelle = trim($_POST['libelle'] ?? '');
            $niveau = (int)($_POST['niveau'] ?? 1);

            if ($niveau < 1 || $niveau > 3) {
                $_SESSION['errors'] = ['Le niveau doit être compris entre 1 et 3'];
                rediriger('/admin/postes/create');
                exit;
            }

            if (!empty($libelle)) {
                if (Poste::create($libelle, $niveau)) {
                    $_SESSION['success'] = "Poste créé avec succès";
                } else {
                    $_SESSION['errors'] = ["Erreur lors de la création"];
                }
            } else {
                $_SESSION['errors'] = ["Le libellé est obligatoire"];
            }
            rediriger('/admin/postes');
        }
    }

    public static function edit() {
        self::verifierAdmin();
        $id = $_GET['id'] ?? null;
        if (!$id) {
            rediriger('/admin/postes');
        }
        $poste = Poste::findById($id);
        require __DIR__ . '/../vues/admin/postes/modifier.php';
    }

    public static function update() {
        self::verifierAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_poste'];

            if (!verifierTokenCSRF()) {
                $_SESSION['errors'] = ['Token de sécurité invalide. Veuillez réessayer.'];
                rediriger("/admin/postes/edit&id=$id");
                exit;
            }

            $libelle = trim($_POST['libelle'] ?? '');
            $niveau = (int)($_POST['niveau'] ?? 1);

            if ($niveau < 1 || $niveau > 3) {
                $_SESSION['errors'] = ['Le niveau doit être compris entre 1 et 3'];
                rediriger("/admin/postes/edit&id=$id");
                exit;
            }

            if (!empty($libelle)) {
                if (Poste::update($id, $libelle, $niveau)) {
                    $_SESSION['success'] = "Poste mis à jour avec succès";
                } else {
                    $_SESSION['errors'] = ["Erreur lors de la mise à jour"];
                }
            } else {
                $_SESSION['errors'] = ["Le libellé est obligatoire"];
            }
            rediriger('/admin/postes');
        }
    }

    public static function delete() {
        self::verifierAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifierTokenCSRF()) {
                $_SESSION['errors'] = ['Token de sécurité invalide. Veuillez réessayer.'];
                rediriger('/admin/postes');
                exit;
            }

            $id = $_POST['id_poste'];

            $countPreferences = Poste::countPreferences($id);
            $countCreneaux = Poste::countCreneauLinks($id);
            $totalUsages = $countPreferences + $countCreneaux;

            if ($totalUsages > 0) {
                $_SESSION['errors'] = ["Impossible de supprimer : ce poste est utilisé $totalUsages fois (préférences membres ou créneaux)"];
            } else {
                if (Poste::delete($id)) {
                    $_SESSION['success'] = "Poste supprimé avec succès";
                } else {
                    $_SESSION['errors'] = ["Erreur lors de la suppression"];
                }
            }
            rediriger('/admin/postes');
        }
    }
}
