<?php

require_once __DIR__ . '/../models/Categorie.php';

class ControleurCategorie {

    private const ROUTE_CATEGORIES = '/admin/categories';
    private const CSRF_ERR = 'Token de sécurité invalide. Veuillez réessayer.';

    private static function verifierAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'gestionnaire') {
            rediriger('/connexion');
        }
    }

    public static function index() {
        self::verifierAdmin();
        $categories = Categorie::findAll();
        require_once __DIR__ . '/../vues/admin/categories/liste.php';
    }

    public static function create() {
        self::verifierAdmin();
        require_once __DIR__ . '/../vues/admin/categories/creer.php';
    }

    public static function store() {
        self::verifierAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifierTokenCSRF()) {
                $_SESSION['errors'] = [self::CSRF_ERR];
                rediriger('/admin/categories/create');
                exit;
            }

            $libelle = trim($_POST['libelle'] ?? '');

            if (!empty($libelle)) {
                if (Categorie::create($libelle)) {
                    $_SESSION['success'] = "Catégorie créée avec succès";
                } else {
                    $_SESSION['errors'] = ["Erreur lors de la création"];
                }
            } else {
                $_SESSION['errors'] = ["Le libellé est obligatoire"];
            }
            rediriger(self::ROUTE_CATEGORIES);
        }
    }

    public static function edit() {
        self::verifierAdmin();
        $id = $_GET['id'] ?? null;
        if (!$id) {
            rediriger(self::ROUTE_CATEGORIES);
        }
        $categorie = Categorie::findById($id);
        require_once __DIR__ . '/../vues/admin/categories/modifier.php';
    }

    public static function update() {
        self::verifierAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_categorie'];

            if (!verifierTokenCSRF()) {
                $_SESSION['errors'] = [self::CSRF_ERR];
                rediriger("/admin/categories/edit&id=$id");
                exit;
            }

            $libelle = trim($_POST['libelle'] ?? '');

            if (!empty($libelle)) {
                if (Categorie::update($id, $libelle)) {
                    $_SESSION['success'] = "Catégorie mise à jour avec succès";
                } else {
                    $_SESSION['errors'] = ["Erreur lors de la mise à jour"];
                }
            } else {
                $_SESSION['errors'] = ["Le libellé est obligatoire"];
            }
            rediriger(self::ROUTE_CATEGORIES);
        }
    }

    public static function delete() {
        self::verifierAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifierTokenCSRF()) {
                $_SESSION['errors'] = [self::CSRF_ERR];
                rediriger(self::ROUTE_CATEGORIES);
                exit;
            }

            $id = $_POST['id_categorie'];

            $count = Categorie::countEvents($id);
            if ($count > 0) {
                $_SESSION['errors'] = ["Impossible de supprimer : cette catégorie est utilisée par $count événement(s)"];
            } else {
                if (Categorie::delete($id)) {
                    $_SESSION['success'] = "Catégorie supprimée avec succès";
                } else {
                    $_SESSION['errors'] = ["Erreur lors de la suppression"];
                }
            }
            rediriger(self::ROUTE_CATEGORIES);
        }
    }
}
