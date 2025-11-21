<?php

require_once __DIR__ . '/../models/Membre.php';

class ControleurAdmin
{
    private static function ensurerSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private static function verifierAdmin(): void
    {
        self::ensurerSession();
        if (($_SESSION['user_type'] ?? '') !== 'admin') {
            $_SESSION['errors'] = ['Accès réservé aux administrateurs.'];
            self::redirect('/connexion');
        }
    }

    private static function redirect(string $path): void
    {
        header('Location: /kastasso/public/index.php?path=' . $path);
        exit();
    }

    public static function afficherDashboard(): void
    {
        self::verifierAdmin();
        $membresEnAttente = Membre::getMembresEnAttente();
        require __DIR__ . '/../vues/admin/tableau_de_bord.php';
    }

    public static function voirMembre(int $id): void
    {
        self::verifierAdmin();
        $membre = Membre::getMembreParId($id);

        if (!$membre) {
            $_SESSION['errors'] = ['Membre non trouvé'];
            self::redirect('/admin/tableau_de_bord');
        }

        require __DIR__ . '/../vues/admin/detail_membre.php';
    }

    public static function validerMembre(): void
    {
        self::verifierAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect('/admin/tableau_de_bord');
        }

        $id = (int)($_POST['id_membre'] ?? 0);

        if ($id === 0) {
            $_SESSION['errors'] = ['ID membre manquant'];
            self::redirect('/admin/tableau_de_bord');
        }

        if (Membre::validerMembre($id)) {
            $membre  = Membre::getMembreParId($id);
            $sujet   = "Votre compte Kast'asso a été validé";
            $message = "Bonjour {$membre['prenom']},\n\nVotre compte a été validé.\nVous pouvez maintenant vous connecter.\n\nCordialement,\nL'équipe Kast'asso";
            Membre::envoyerEmail($membre['mail'], $sujet, $message);
            $_SESSION['success'] = 'Membre validé avec succès';
        } else {
            $_SESSION['errors'] = ['Erreur lors de la validation'];
        }

        self::redirect('/admin/dashboard');
    }

    public static function refuserMembre(): void
{
    self::verifierAdmin();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        self::redirect('/admin/tableau_de_bord');
    }

    $id    = (int)($_POST['id_membre'] ?? 0);
    $motif = trim($_POST['motif_refus'] ?? '');

    if ($id === 0) {
        $_SESSION['errors'] = ['ID membre manquant'];
        self::redirect('/admin/tableau_de_bord');
    }

    if ($motif === '') {
    $motif = "Aucun motif fourni";
}

    if (Membre::refuserMembre($id, $motif)) {
        $membre  = Membre::getMembreParId($id);
        $sujet   = "Votre demande d'inscription Kast'asso";
        $message = "Bonjour {$membre['prenom']},\n\nVotre demande a été refusée pour le motif suivant :\n{$motif}\n\nCordialement,\nL'équipe Kast'asso";
        Membre::envoyerEmail($membre['mail'], $sujet, $message);
        $_SESSION['success'] = 'Membre refusé';
    } else {
        $_SESSION['errors'] = ['Erreur lors du refus'];
    }

    self::redirect('/admin/tableau_de_bord');
}

public static function deconnexion(): void
    {
        self::ensurerSession();
        session_destroy();
        header('Location: /kastasso/public/index.php?path=/connexion');
        exit();
    }

}
