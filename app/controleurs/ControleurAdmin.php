<?php

require_once __DIR__ . '/../models/Membre.php';
require_once __DIR__ . '/../models/EvenementSport.php';
require_once __DIR__ . '/../models/EvenementAsso.php';
require_once __DIR__ . '/../services/EmailService.php';

class ControleurAdmin
{
    private const ROUTE_CONNEXION = '/connexion';
    private const ROUTE_MEMBRES = '/admin/membres';
    private const ROUTE_DASHBOARD = '/admin/tableau_de_bord';
    private const ROUTE_TEMPLATE_ADHESION = '/admin/template-adhesion';
    private const CSRF_ERR = 'Token de sécurité invalide. Veuillez réessayer.';
    private const TOKEN_ERR = 'Token de sécurité invalide.';
    private const ERR_ID_MEMBRE = 'ID membre manquant';
    private const ERR_MEMBRE_INTROUVABLE = 'Membre introuvable.';

    private static function ensurerSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private static function verifierAdmin(): void
    {
        self::ensurerSession();
        empecherMiseEnCache();
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'gestionnaire') {
            $_SESSION['errors'] = ['Accès réservé aux administrateurs et gestionnaires.'];
            self::redirect(self::ROUTE_CONNEXION);
        }
    }

    private static function redirect(string $path): void
    {
        rediriger($path);
        exit();
    }

    public static function afficherDashboard(): void
    {
        self::verifierAdmin();
        $membresEnAttente = Membre::getMembresEnAttente();
        $countSport = count(EvenementSport::findAll());
        $countAsso = count(EvenementAsso::findAll(true));
        require_once __DIR__ . '/../vues/admin/tableau_de_bord.php';
    }

    public static function afficherGestionMembres(): void
    {
        self::verifierAdmin();
        $membres = Membre::getTousLesMembres();
        require_once __DIR__ . '/../vues/admin/gestion_membres.php';
    }

    public static function rendreGestionnaire(): void
    {
        self::verifierAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect(self::ROUTE_MEMBRES);
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = [self::CSRF_ERR];
            self::redirect(self::ROUTE_MEMBRES);
        }

        $id = (int)($_POST['id_membre'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($id === 0) {
            $_SESSION['errors'] = [self::ERR_ID_MEMBRE];
            self::redirect(self::ROUTE_MEMBRES);
        }

        $valeur = ($action === 'ajouter') ? 1 : 0;

        if (Membre::mettreGestionnaire($id, $valeur)) {
            $_SESSION['success'] = ($valeur === 1) ? 'Membre promu gestionnaire' : 'Membre rétrogradé';
        } else {
            $_SESSION['errors'] = ['Erreur lors de la modification du rôle'];
        }

        self::redirect(self::ROUTE_MEMBRES);
    }

    public static function voirMembre(): void
    {
        self::verifierAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $membre = Membre::getMembreParId($id);

        if (!$membre) {
            $_SESSION['errors'] = ['Membre non trouvé'];
            self::redirect(self::ROUTE_DASHBOARD);
        }

        require_once __DIR__ . '/../vues/admin/detail_membre.php';
    }

    public static function validerMembre(): void
    {
        self::verifierAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect(self::ROUTE_DASHBOARD);
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = [self::CSRF_ERR];
            self::redirect(self::ROUTE_DASHBOARD);
        }

        $id = (int)($_POST['id_membre'] ?? 0);

        if ($id === 0) {
            $_SESSION['errors'] = [self::ERR_ID_MEMBRE];
            self::redirect(self::ROUTE_DASHBOARD);
        }

        if (Membre::validerMembre($id)) {
            $membre = Membre::getMembreParId($id);

            $prenom = $membre['Prenom'];
            $nom = $membre['Nom'];
            $lien_connexion = url(self::ROUTE_CONNEXION);

            ob_start();
            include_once __DIR__ . '/../templates/email_compte_accepte.php';
            $messageHTML = ob_get_clean();

            $sujet = "Votre compte KAST'ASSO a été validé";
            $nomComplet = $prenom . ' ' . $nom;

            if (EmailService::envoyer($membre['Mail'], $sujet, $messageHTML, $nomComplet)) {
                $_SESSION['success'] = 'Membre validé avec succès et email envoyé';
            } else {
                $_SESSION['success'] = 'Membre validé mais erreur lors de l\'envoi de l\'email';
            }
        } else {
            $_SESSION['errors'] = ['Erreur lors de la validation'];
        }

        self::redirect(self::ROUTE_DASHBOARD);
    }

    public static function refuserMembre(): void
    {
        self::verifierAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect(self::ROUTE_DASHBOARD);
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = [self::CSRF_ERR];
            self::redirect(self::ROUTE_DASHBOARD);
        }

        $id    = (int)($_POST['id_membre'] ?? 0);
        $motif = trim($_POST['motif_refus'] ?? '');

        if ($id === 0) {
            $_SESSION['errors'] = [self::ERR_ID_MEMBRE];
            self::redirect(self::ROUTE_DASHBOARD);
        }

        if ($motif === '') {
            $motif = "Aucun motif fourni";
        }

        $membre = Membre::getMembreParId($id);

        if ($membre) {
            if (Membre::supprimerMembre($id)) {
                $prenom = $membre['Prenom'];
                $nom = $membre['Nom'];
                $lien_inscription = url('/inscription');

                ob_start();
                include_once __DIR__ . '/../templates/email_compte_refuse.php';
                $messageHTML = ob_get_clean();

                $sujet = "Réponse à votre demande d'inscription KAST'ASSO";
                $nomComplet = $prenom . ' ' . $nom;

                if (EmailService::envoyer($membre['Mail'], $sujet, $messageHTML, $nomComplet)) {
                    $_SESSION['success'] = 'Inscription refusée, membre supprimé et email envoyé.';
                } else {
                    $_SESSION['success'] = 'Inscription refusée et membre supprimé, mais erreur lors de l\'envoi de l\'email.';
                }
            } else {
                $_SESSION['errors'] = ['Erreur lors de la suppression du membre.'];
            }
        } else {
            $_SESSION['errors'] = [self::ERR_MEMBRE_INTROUVABLE];
        }

        self::redirect(self::ROUTE_DASHBOARD);
    }

    public static function supprimerMembreAdmin(): void
    {
        self::verifierAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect(self::ROUTE_MEMBRES);
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = [self::CSRF_ERR];
            self::redirect(self::ROUTE_MEMBRES);
        }

        $id = (int)($_POST['id_membre'] ?? 0);

        if ($id === 0) {
            $_SESSION['errors'] = [self::ERR_ID_MEMBRE];
            self::redirect(self::ROUTE_MEMBRES);
        }

        $membre = Membre::getMembreParId($id);

        if (!$membre) {
            $_SESSION['errors'] = [self::ERR_MEMBRE_INTROUVABLE];
            self::redirect(self::ROUTE_MEMBRES);
        }

        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
            $_SESSION['errors'] = ['Vous ne pouvez pas supprimer votre propre compte.'];
            self::redirect(self::ROUTE_MEMBRES);
        }

        if (Membre::supprimerMembre($id)) {
            $_SESSION['success'] = 'Le membre ' . htmlspecialchars($membre['Prenom'] . ' ' . $membre['Nom']) . ' a été supprimé avec succès.';
        } else {
            $_SESSION['errors'] = ['Erreur lors de la suppression du membre.'];
        }

        self::redirect(self::ROUTE_MEMBRES);
    }

    public static function modifierStatutAdherent(): void
    {
        self::verifierAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect(self::ROUTE_MEMBRES);
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = [self::TOKEN_ERR];
            self::redirect(self::ROUTE_MEMBRES);
        }

        $idMembre = (int)($_POST['id_membre'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($idMembre === 0) {
            $_SESSION['errors'] = ['ID membre manquant.'];
            self::redirect(self::ROUTE_MEMBRES);
        }

        $membre = Membre::getMembreParId($idMembre);
        if (!$membre) {
            $_SESSION['errors'] = [self::ERR_MEMBRE_INTROUVABLE];
            self::redirect(self::ROUTE_MEMBRES);
        }

        if ($membre['Statut_compte'] !== 'valide') {
            $_SESSION['errors'] = ['Impossible de modifier le statut d\'adhérent pour un compte non validé.'];
            self::redirect('/admin/membre/detail&id=' . $idMembre);
        }

        $nouveauStatut = ($action === 'ajouter');

        if (Membre::modifierStatutAdherent($idMembre, $nouveauStatut)) {
            $message = $nouveauStatut
                ? 'Le membre est maintenant adhérent.'
                : 'Le statut d\'adhérent a été retiré.';
            $_SESSION['success'] = $message;
        } else {
            $_SESSION['errors'] = ['Erreur lors de la modification du statut d\'adhérent.'];
        }

        self::redirect('/admin/membre/detail&id=' . $idMembre);
    }

    public static function afficherHistoriqueMembre(): void
    {
        self::verifierAdmin();

        $idMembre = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($idMembre <= 0) {
            $_SESSION['errors'] = ['ID membre invalide.'];
            self::redirect(self::ROUTE_MEMBRES);
        }

        $membre = Membre::getMembreParId($idMembre);

        if (!$membre) {
            $_SESSION['errors'] = [self::ERR_MEMBRE_INTROUVABLE];
            self::redirect(self::ROUTE_MEMBRES);
        }

        $historique = Participation::getHistoriqueMembre($idMembre);

        require_once __DIR__ . '/../vues/gabarits/en_tete.php';
        require_once __DIR__ . '/../vues/gabarits/barre_nav.php';
        require_once __DIR__ . '/../vues/admin/historique_membre.php';
        require_once __DIR__ . '/../vues/gabarits/pied_de_page.php';
    }

    public static function afficherDemandesAdhesion(): void
    {
        self::verifierAdmin();
        $demandesAdhesion = Membre::getDemandesAdhesionEnAttente();
        require_once __DIR__ . '/../vues/admin/demandes_adhesion.php';
    }

    public static function accepterAdhesion(): void
    {
        self::verifierAdmin();

        $redirectUrl = ($_SESSION['user_type'] === 'gestionnaire') ? '/gestionnaire/adhesions' : '/admin/adhesions';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect($redirectUrl);
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = [self::CSRF_ERR];
            self::redirect($redirectUrl);
        }

        $id = (int)($_POST['id_membre'] ?? 0);

        if ($id === 0) {
            $_SESSION['errors'] = [self::ERR_ID_MEMBRE];
            self::redirect($redirectUrl);
        }

        $membre = Membre::getMembreParId($id);

        if (!$membre) {
            $_SESSION['errors'] = ['Membre introuvable'];
            self::redirect($redirectUrl);
        }

        if ($membre['Statut_compte'] === 'en_attente') {
            $_SESSION['errors'] = ['Ce compte est en attente de validation par l\'administrateur. Les demandes d\'adhésion ne peuvent pas encore être traitées pour ce membre.'];
            self::redirect($redirectUrl);
        }

        if ($membre['Statut_compte'] === 'refuse') {
            $_SESSION['errors'] = ['Ce compte a été refusé. Impossible de traiter l\'adhésion.'];
            self::redirect($redirectUrl);
        }

        if (Membre::accepterAdhesion($id)) {
            $prenom = $membre['Prenom'];
            $nom = $membre['Nom'];
            $lien_connexion = url(self::ROUTE_CONNEXION);

            ob_start();
            include_once __DIR__ . '/../templates/email_adhesion_acceptee.php';
            $messageHTML = ob_get_clean();

            $sujet = "Votre adhésion à KAST'ASSO a été acceptée";
            $nomComplet = $prenom . ' ' . $nom;

            if (EmailService::envoyer($membre['Mail'], $sujet, $messageHTML, $nomComplet)) {
                $_SESSION['success'] = 'Adhésion acceptée et email envoyé';
            } else {
                $_SESSION['success'] = 'Adhésion acceptée mais erreur lors de l\'envoi de l\'email';
            }
        } else {
            $_SESSION['errors'] = ['Erreur lors de l\'acceptation de l\'adhésion'];
        }

        self::redirect($redirectUrl);
    }

    public static function refuserAdhesion(): void
    {
        self::verifierAdmin();

        $redirectUrl = ($_SESSION['user_type'] === 'gestionnaire') ? '/gestionnaire/adhesions' : '/admin/adhesions';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect($redirectUrl);
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = [self::CSRF_ERR];
            self::redirect($redirectUrl);
        }

        $id = (int)($_POST['id_membre'] ?? 0);
        $motif = trim($_POST['motif_refus'] ?? '');

        if ($id === 0) {
            $_SESSION['errors'] = [self::ERR_ID_MEMBRE];
            self::redirect($redirectUrl);
        }

        if (empty($motif)) {
            $_SESSION['errors'] = ['Le motif du refus est obligatoire'];
            self::redirect($redirectUrl);
        }

        $membre = Membre::getMembreParId($id);

        if (!$membre) {
            $_SESSION['errors'] = ['Membre introuvable'];
            self::redirect($redirectUrl);
        }

        if ($membre['Statut_compte'] === 'en_attente') {
            $_SESSION['errors'] = ['Ce compte est en attente de validation par l\'administrateur. Les demandes d\'adhésion ne peuvent pas encore être traitées pour ce membre.'];
            self::redirect($redirectUrl);
        }

        if ($membre['Statut_compte'] === 'refuse') {
            $_SESSION['errors'] = ['Ce compte a été refusé. Impossible de traiter l\'adhésion.'];
            self::redirect($redirectUrl);
        }

        if (Membre::refuserAdhesion($id, $motif)) {
            $prenom = $membre['Prenom'];
            $nom = $membre['Nom'];
            $lien_connexion = url(self::ROUTE_CONNEXION);

            ob_start();
            include_once __DIR__ . '/../templates/email_adhesion_refusee.php';
            $messageHTML = ob_get_clean();

            $sujet = "Réponse à votre demande d'adhésion KAST'ASSO";
            $nomComplet = $prenom . ' ' . $nom;

            if (EmailService::envoyer($membre['Mail'], $sujet, $messageHTML, $nomComplet)) {
                $_SESSION['success'] = 'Adhésion refusée et email envoyé';
            } else {
                $_SESSION['success'] = 'Adhésion refusée mais erreur lors de l\'envoi de l\'email';
            }
        } else {
            $_SESSION['errors'] = ['Erreur lors du refus de l\'adhésion'];
        }

        self::redirect($redirectUrl);
    }

    // ============================================================================
    // GESTION DU TEMPLATE D'ADHESION
    // ============================================================================

    private static function verifierAdminOnly(): void
    {
        self::ensurerSession();
        empecherMiseEnCache();
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
            $_SESSION['errors'] = ['Accès réservé aux administrateurs uniquement.'];
            self::redirect(self::ROUTE_CONNEXION);
        }
    }

    private static function getTemplateAdhesionPath(): string
    {
        return __DIR__ . '/../../uploads/templates/';
    }

    private static function getCustomTemplatePath(): ?string
    {
        $templateDir = self::getTemplateAdhesionPath();
        if (!is_dir($templateDir)) {
            return null;
        }

        $files = glob($templateDir . 'adhesion_template_*.pdf');
        if (!empty($files)) {
            return $files[0];
        }

        return null;
    }

    public static function afficherTemplateAdhesion(): void
    {
        self::verifierAdminOnly();

        $customTemplate = self::getCustomTemplatePath();
        $hasCustomTemplate = $customTemplate !== null;
        $customTemplateName = $hasCustomTemplate ? basename($customTemplate) : null;

        require_once __DIR__ . '/../vues/admin/template_adhesion.php';
    }

    public static function uploadTemplateAdhesion(): void
    {
        self::verifierAdminOnly();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect(self::ROUTE_TEMPLATE_ADHESION);
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = [self::TOKEN_ERR];
            self::redirect(self::ROUTE_TEMPLATE_ADHESION);
        }

        if (!isset($_FILES['template_pdf']) || $_FILES['template_pdf']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['errors'] = ['Erreur lors du téléversement du fichier.'];
            self::redirect(self::ROUTE_TEMPLATE_ADHESION);
        }

        $file = $_FILES['template_pdf'];

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if ($mimeType !== 'application/pdf') {
            $_SESSION['errors'] = ['Le fichier doit être un PDF valide.'];
            self::redirect(self::ROUTE_TEMPLATE_ADHESION);
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['errors'] = ['Le fichier est trop volumineux (max 5 Mo).'];
            self::redirect(self::ROUTE_TEMPLATE_ADHESION);
        }

        $templateDir = self::getTemplateAdhesionPath();
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }

        $oldTemplate = self::getCustomTemplatePath();
        if ($oldTemplate && file_exists($oldTemplate)) {
            unlink($oldTemplate);
        }

        $newFileName = 'adhesion_template_' . date('Y-m-d_H-i-s') . '.pdf';
        $newFilePath = $templateDir . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $newFilePath)) {
            $_SESSION['success'] = 'Template personnalisé téléversé avec succès !';
        } else {
            $_SESSION['errors'] = ['Erreur lors de l\'enregistrement du fichier.'];
        }

        self::redirect(self::ROUTE_TEMPLATE_ADHESION);
    }

    public static function deleteTemplateAdhesion(): void
    {
        self::verifierAdminOnly();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect(self::ROUTE_TEMPLATE_ADHESION);
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = [self::TOKEN_ERR];
            self::redirect(self::ROUTE_TEMPLATE_ADHESION);
        }

        $customTemplate = self::getCustomTemplatePath();
        if ($customTemplate && file_exists($customTemplate)) {
            if (unlink($customTemplate)) {
                $_SESSION['success'] = 'Template personnalisé supprimé. Le template par défaut sera utilisé.';
            } else {
                $_SESSION['errors'] = ['Erreur lors de la suppression du template.'];
            }
        } else {
            $_SESSION['errors'] = ['Aucun template personnalisé à supprimer.'];
        }

        self::redirect(self::ROUTE_TEMPLATE_ADHESION);
    }

    public static function previewTemplateAdhesion(): void
    {
        self::verifierAdminOnly();

        $customTemplate = self::getCustomTemplatePath();

        if ($customTemplate && file_exists($customTemplate)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="template_adhesion.pdf"');
            header('Content-Length: ' . filesize($customTemplate));
            readfile($customTemplate);
            exit;
        } else {
            require_once __DIR__ . '/../services/PDFService.php';
            PDFService::genererFormulaireAdhesionPreview();
        }
    }
}
