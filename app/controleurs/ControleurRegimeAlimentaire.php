<?php
/**
 * Contrôleur pour la gestion des régimes alimentaires
 * Accessible uniquement aux administrateurs et gestionnaires
 */

require_once __DIR__ . '/../models/RegimeAlimentaire.php';

class ControleurRegimeAlimentaire
{
    private const ROUTE_REGIMES = '/admin/regimes-alimentaires';
    private const CSRF_ERR = 'Token de sécurité invalide. Veuillez réessayer.';

    /**
     * Vérifie que l'utilisateur est admin ou gestionnaire
     */
    private static function verifierAdmin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        empecherMiseEnCache();

        if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['admin', 'gestionnaire'])) {
            $_SESSION['errors'] = ['Accès réservé aux administrateurs et gestionnaires.'];
            rediriger('/connexion');
        }
    }

    /**
     * Affiche la page de gestion des régimes alimentaires
     */
    public static function afficherGestion(): void
    {
        self::verifierAdmin();

        $regimes = RegimeAlimentaire::compterParRegime();

        require_once __DIR__ . '/../vues/admin/regimes_alimentaires.php';
    }

    /**
     * Traite l'ajout d'un nouveau régime
     */
    public static function ajouter(): void
    {
        self::verifierAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger(self::ROUTE_REGIMES);
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = [self::CSRF_ERR];
            rediriger(self::ROUTE_REGIMES);
        }

        $nom = trim($_POST['nom'] ?? '');

        if (empty($nom)) {
            $_SESSION['errors'] = ['Le nom du régime alimentaire est obligatoire.'];
            rediriger(self::ROUTE_REGIMES);
        }

        if (RegimeAlimentaire::existeParNom($nom)) {
            $_SESSION['errors'] = ['Ce régime alimentaire existe déjà.'];
            rediriger(self::ROUTE_REGIMES);
        }

        if (RegimeAlimentaire::creer($nom)) {
            $_SESSION['success'] = 'Régime alimentaire "' . htmlspecialchars($nom) . '" ajouté avec succès.';
        } else {
            $_SESSION['errors'] = ['Erreur lors de l\'ajout du régime alimentaire.'];
        }

        rediriger(self::ROUTE_REGIMES);
    }

    /**
     * Traite la modification d'un régime
     */
    public static function modifier(): void
    {
        self::verifierAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger(self::ROUTE_REGIMES);
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = [self::CSRF_ERR];
            rediriger(self::ROUTE_REGIMES);
        }

        $id = (int) ($_POST['id'] ?? 0);
        $nom = trim($_POST['nom'] ?? '');

        if ($id <= 0) {
            $_SESSION['errors'] = ['ID de régime invalide.'];
            rediriger(self::ROUTE_REGIMES);
        }

        if (empty($nom)) {
            $_SESSION['errors'] = ['Le nom du régime alimentaire est obligatoire.'];
            rediriger(self::ROUTE_REGIMES);
        }

        $regime = RegimeAlimentaire::trouverParId($id);
        if (!$regime) {
            $_SESSION['errors'] = ['Régime alimentaire introuvable.'];
            rediriger(self::ROUTE_REGIMES);
        }

        if (RegimeAlimentaire::existeParNomSaufId($nom, $id)) {
            $_SESSION['errors'] = ['Un autre régime alimentaire avec ce nom existe déjà.'];
            rediriger(self::ROUTE_REGIMES);
        }

        if (RegimeAlimentaire::modifier($id, $nom)) {
            $_SESSION['success'] = 'Régime alimentaire modifié avec succès.';
        } else {
            $_SESSION['errors'] = ['Erreur lors de la modification du régime alimentaire.'];
        }

        rediriger(self::ROUTE_REGIMES);
    }

    /**
     * Traite la suppression d'un régime
     */
    public static function supprimer(): void
    {
        self::verifierAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger(self::ROUTE_REGIMES);
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = [self::CSRF_ERR];
            rediriger(self::ROUTE_REGIMES);
        }

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['errors'] = ['ID de régime invalide.'];
            rediriger(self::ROUTE_REGIMES);
        }

        $regime = RegimeAlimentaire::trouverParId($id);
        if (!$regime) {
            $_SESSION['errors'] = ['Régime alimentaire introuvable.'];
            rediriger(self::ROUTE_REGIMES);
        }

        $nbMembres = RegimeAlimentaire::compterMembres($id);
        if ($nbMembres > 0) {
            $_SESSION['errors'] = ['Impossible de supprimer ce régime : ' . $nbMembres . ' membre(s) l\'utilisent encore.'];
            rediriger(self::ROUTE_REGIMES);
        }

        if (RegimeAlimentaire::supprimer($id)) {
            $_SESSION['success'] = 'Régime alimentaire "' . htmlspecialchars($regime['nom']) . '" supprimé avec succès.';
        } else {
            $_SESSION['errors'] = ['Erreur lors de la suppression du régime alimentaire.'];
        }

        rediriger(self::ROUTE_REGIMES);
    }
}
