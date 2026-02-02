<?php
/**
 * Contrôleur pour la gestion des régimes alimentaires
 * Accessible uniquement aux administrateurs et gestionnaires
 */

require_once __DIR__ . '/../models/RegimeAlimentaire.php';

class ControleurRegimeAlimentaire
{
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
        
        require __DIR__ . '/../vues/admin/regimes_alimentaires.php';
    }

    /**
     * Traite l'ajout d'un nouveau régime
     */
    public static function ajouter(): void
    {
        self::verifierAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger('/admin/regimes-alimentaires');
        }
        
        // Vérification CSRF
        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = ['Token de sécurité invalide. Veuillez réessayer.'];
            rediriger('/admin/regimes-alimentaires');
        }
        
        $nom = trim($_POST['nom'] ?? '');
        
        if (empty($nom)) {
            $_SESSION['errors'] = ['Le nom du régime alimentaire est obligatoire.'];
            rediriger('/admin/regimes-alimentaires');
        }
        
        // Vérifier si le régime existe déjà
        if (RegimeAlimentaire::existeParNom($nom)) {
            $_SESSION['errors'] = ['Ce régime alimentaire existe déjà.'];
            rediriger('/admin/regimes-alimentaires');
        }
        
        if (RegimeAlimentaire::creer($nom)) {
            $_SESSION['success'] = 'Régime alimentaire "' . htmlspecialchars($nom) . '" ajouté avec succès.';
        } else {
            $_SESSION['errors'] = ['Erreur lors de l\'ajout du régime alimentaire.'];
        }
        
        rediriger('/admin/regimes-alimentaires');
    }

    /**
     * Traite la modification d'un régime
     */
    public static function modifier(): void
    {
        self::verifierAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger('/admin/regimes-alimentaires');
        }
        
        // Vérification CSRF
        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = ['Token de sécurité invalide. Veuillez réessayer.'];
            rediriger('/admin/regimes-alimentaires');
        }
        
        $id = (int) ($_POST['id'] ?? 0);
        $nom = trim($_POST['nom'] ?? '');
        
        if ($id <= 0) {
            $_SESSION['errors'] = ['ID de régime invalide.'];
            rediriger('/admin/regimes-alimentaires');
        }
        
        if (empty($nom)) {
            $_SESSION['errors'] = ['Le nom du régime alimentaire est obligatoire.'];
            rediriger('/admin/regimes-alimentaires');
        }
        
        // Vérifier si le régime existe
        $regime = RegimeAlimentaire::trouverParId($id);
        if (!$regime) {
            $_SESSION['errors'] = ['Régime alimentaire introuvable.'];
            rediriger('/admin/regimes-alimentaires');
        }
        
        // Vérifier si un autre régime avec ce nom existe
        if (RegimeAlimentaire::existeParNomSaufId($nom, $id)) {
            $_SESSION['errors'] = ['Un autre régime alimentaire avec ce nom existe déjà.'];
            rediriger('/admin/regimes-alimentaires');
        }
        
        if (RegimeAlimentaire::modifier($id, $nom)) {
            $_SESSION['success'] = 'Régime alimentaire modifié avec succès.';
        } else {
            $_SESSION['errors'] = ['Erreur lors de la modification du régime alimentaire.'];
        }
        
        rediriger('/admin/regimes-alimentaires');
    }

    /**
     * Traite la suppression d'un régime
     */
    public static function supprimer(): void
    {
        self::verifierAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger('/admin/regimes-alimentaires');
        }
        
        // Vérification CSRF
        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = ['Token de sécurité invalide. Veuillez réessayer.'];
            rediriger('/admin/regimes-alimentaires');
        }
        
        $id = (int) ($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            $_SESSION['errors'] = ['ID de régime invalide.'];
            rediriger('/admin/regimes-alimentaires');
        }
        
        // Vérifier si le régime existe
        $regime = RegimeAlimentaire::trouverParId($id);
        if (!$regime) {
            $_SESSION['errors'] = ['Régime alimentaire introuvable.'];
            rediriger('/admin/regimes-alimentaires');
        }
        
        // Vérifier si des membres utilisent ce régime
        $nbMembres = RegimeAlimentaire::compterMembres($id);
        if ($nbMembres > 0) {
            $_SESSION['errors'] = ['Impossible de supprimer ce régime : ' . $nbMembres . ' membre(s) l\'utilisent encore.'];
            rediriger('/admin/regimes-alimentaires');
        }
        
        if (RegimeAlimentaire::supprimer($id)) {
            $_SESSION['success'] = 'Régime alimentaire "' . htmlspecialchars($regime['nom']) . '" supprimé avec succès.';
        } else {
            $_SESSION['errors'] = ['Erreur lors de la suppression du régime alimentaire.'];
        }
        
        rediriger('/admin/regimes-alimentaires');
    }
}
