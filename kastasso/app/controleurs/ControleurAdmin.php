<?php

require_once __DIR__ . '/../models/Membre.php';
require_once __DIR__ . '/../models/EvenementSport.php';
require_once __DIR__ . '/../models/EvenementAsso.php';
require_once __DIR__ . '/../services/EmailService.php';

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
        empecherMiseEnCache();
        if (!isset($_SESSION['user_type']) || ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'gestionnaire')) {
            $_SESSION['errors'] = ['Accès réservé aux administrateurs et gestionnaires.'];
            self::redirect('/connexion');
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
        require __DIR__ . '/../vues/admin/tableau_de_bord.php';
    }

    public static function afficherGestionMembres(): void
    {
        self::verifierAdmin();
        $membres = Membre::getTousLesMembres();
        require __DIR__ . '/../vues/admin/gestion_membres.php';
    }

    public static function rendreGestionnaire(): void
    {
        self::verifierAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect('/admin/membres');
        }

        // Vérification CSRF
        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = ['Token de sécurité invalide. Veuillez réessayer.'];
            self::redirect('/admin/membres');
        }

        $id = (int)($_POST['id_membre'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($id === 0) {
            $_SESSION['errors'] = ['ID membre manquant'];
            self::redirect('/admin/membres');
        }

        $valeur = ($action === 'ajouter') ? 1 : 0;
        
        if (Membre::mettreGestionnaire($id, $valeur)) {
            $_SESSION['success'] = ($valeur === 1) ? 'Membre promu gestionnaire' : 'Membre rétrogradé';
        } else {
            $_SESSION['errors'] = ['Erreur lors de la modification du rôle'];
        }

        self::redirect('/admin/membres');
    }


    public static function voirMembre(): void
    {
        self::verifierAdmin();
        $id = (int)($_GET['id'] ?? 0);
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

        // Vérification CSRF
        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = ['Token de sécurité invalide. Veuillez réessayer.'];
            self::redirect('/admin/tableau_de_bord');
        }

        $id = (int)($_POST['id_membre'] ?? 0);

        if ($id === 0) {
            $_SESSION['errors'] = ['ID membre manquant'];
            self::redirect('/admin/tableau_de_bord');
        }

        if (Membre::validerMembre($id)) {
            $membre = Membre::getMembreParId($id);

            // Générer l'email HTML à partir du template
            $prenom = $membre['Prenom'];
            $nom = $membre['Nom'];
            $email = $membre['Mail'];
            $lien_connexion = url('/connexion');

            ob_start();
            include __DIR__ . '/../templates/email_compte_accepte.php';
            $messageHTML = ob_get_clean();

            $sujet = "Votre compte KAST'ASSO a été validé";
            $nomComplet = $prenom . ' ' . $nom;

            // Envoi de l'email
            if (EmailService::envoyer($membre['Mail'], $sujet, $messageHTML, $nomComplet)) {
                $_SESSION['success'] = 'Membre validé avec succès et email envoyé';
            } else {
                $_SESSION['success'] = 'Membre validé mais erreur lors de l\'envoi de l\'email';
            }
        } else {
            $_SESSION['errors'] = ['Erreur lors de la validation'];
        }

        self::redirect('/admin/tableau_de_bord');
    }

    public static function refuserMembre(): void
    {
        self::verifierAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect('/admin/tableau_de_bord');
        }

        // Vérification CSRF
        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = ['Token de sécurité invalide. Veuillez réessayer.'];
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

        // 1 : on recupere les infos du membre AVANT de le supprimer
        // sinon on peut plus lui envoyer le mail lol
        $membre = Membre::getMembreParId($id);

        if ($membre) {
            // 2 : on SUPPRIME le membre de la bdd
            // comme ca il peut se reinscrire avec le meme email
            if (Membre::supprimerMembre($id)) {
                
                // 3 : on prepare le mail
                $prenom = $membre['Prenom'];
                $nom = $membre['Nom'];
                $lien_inscription = url('/inscription');

                ob_start();
                // faut que ce template existe et utilise $motif
                include __DIR__ . '/../templates/email_compte_refuse.php';
                $messageHTML = ob_get_clean();

                $sujet = "Réponse à votre demande d'inscription KAST'ASSO";
                $nomComplet = $prenom . ' ' . $nom;

                // 4 : on envoie le mail
                if (EmailService::envoyer($membre['Mail'], $sujet, $messageHTML, $nomComplet)) {
                    $_SESSION['success'] = 'Inscription refusée, membre supprimé et email envoyé.';
                } else {
                    $_SESSION['success'] = 'Inscription refusée et membre supprimé, mais erreur lors de l\'envoi de l\'email.';
                }
            } else {
                $_SESSION['errors'] = ['Erreur lors de la suppression du membre.'];
            }
        } else {
            $_SESSION['errors'] = ['Membre introuvable.'];
        }

        self::redirect('/admin/tableau_de_bord');
    }

    public static function supprimerMembreAdmin(): void
    {
        self::verifierAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect('/admin/membres');
        }

        // Vérification CSRF
        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = ['Token de sécurité invalide. Veuillez réessayer.'];
            self::redirect('/admin/membres');
        }

        $id = (int)($_POST['id_membre'] ?? 0);

        if ($id === 0) {
            $_SESSION['errors'] = ['ID membre manquant'];
            self::redirect('/admin/membres');
        }

        // Récupérer les infos du membre avant suppression
        $membre = Membre::getMembreParId($id);

        if (!$membre) {
            $_SESSION['errors'] = ['Membre introuvable.'];
            self::redirect('/admin/membres');
        }

        // Empêcher la suppression d'un admin (sécurité)
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
            $_SESSION['errors'] = ['Vous ne pouvez pas supprimer votre propre compte.'];
            self::redirect('/admin/membres');
        }

        if (Membre::supprimerMembre($id)) {
            $_SESSION['success'] = 'Le membre ' . htmlspecialchars($membre['Prenom'] . ' ' . $membre['Nom']) . ' a été supprimé avec succès.';
        } else {
            $_SESSION['errors'] = ['Erreur lors de la suppression du membre.'];
        }

        self::redirect('/admin/membres');
    }

    // Modifier le statut adhérent d'un membre
    public static function modifierStatutAdherent(): void
    {
        self::verifierAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect('/admin/membres');
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = ['Token de sécurité invalide.'];
            self::redirect('/admin/membres');
        }

        $idMembre = (int)($_POST['id_membre'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($idMembre === 0) {
            $_SESSION['errors'] = ['ID membre manquant.'];
            self::redirect('/admin/membres');
        }

        $membre = Membre::getMembreParId($idMembre);
        if (!$membre) {
            $_SESSION['errors'] = ['Membre introuvable.'];
            self::redirect('/admin/membres');
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

    //Affiche l'historique des participations d'un membre aux evenements sportifs
    public static function afficherHistoriqueMembre(): void
    {
        self::verifierAdmin();

        // Recuperer l'ID du membre depuis l'URL
        $idMembre = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($idMembre <= 0) {
            $_SESSION['errors'] = ['ID membre invalide.'];
            self::redirect('/admin/membres');
        }

        // Recuperer les infos du membre
        $membre = Membre::getMembreParId($idMembre);

        if (!$membre) {
            $_SESSION['errors'] = ['Membre introuvable.'];
            self::redirect('/admin/membres');
        }

        // Recuperer l'historique des participations
        $historique = Participation::getHistoriqueMembre($idMembre);

        // Afficher la vue
        require_once __DIR__ . '/../vues/gabarits/en_tete.php';
        require_once __DIR__ . '/../vues/gabarits/barre_nav.php';
        require_once __DIR__ . '/../vues/admin/historique_membre.php';
        require_once __DIR__ . '/../vues/gabarits/pied_de_page.php';
    }

    // afficher demandes adhesion en attente
    public static function afficherDemandesAdhesion(): void
    {
        self::verifierAdmin();
        $demandesAdhesion = Membre::getDemandesAdhesionEnAttente();
        require __DIR__ . '/../vues/admin/demandes_adhesion.php';
    }

    // accepter demande adhesion
    public static function accepterAdhesion(): void
    {
        self::verifierAdmin();

        // determiner URL retour selon role
        $redirectUrl = ($_SESSION['user_type'] === 'gestionnaire') ? '/gestionnaire/adhesions' : '/admin/adhesions';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect($redirectUrl);
        }

        // check CSRF
        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = ['Token de sécurité invalide. Veuillez réessayer.'];
            self::redirect($redirectUrl);
        }

        $id = (int)($_POST['id_membre'] ?? 0);

        if ($id === 0) {
            $_SESSION['errors'] = ['ID membre manquant'];
            self::redirect($redirectUrl);
        }

        // recuperer infos membre avant validation
        $membre = Membre::getMembreParId($id);

        if (!$membre) {
            $_SESSION['errors'] = ['Membre introuvable'];
            self::redirect($redirectUrl);
        }

        // verifier que compte est valide
        if ($membre['Statut_compte'] === 'en_attente') {
            $_SESSION['errors'] = ['Ce compte est en attente de validation par l\'administrateur. Les demandes d\'adhésion ne peuvent pas encore être traitées pour ce membre.'];
            self::redirect($redirectUrl);
        }

        if ($membre['Statut_compte'] === 'refuse') {
            $_SESSION['errors'] = ['Ce compte a été refusé. Impossible de traiter l\'adhésion.'];
            self::redirect($redirectUrl);
        }

        if (Membre::accepterAdhesion($id)) {
            // preparer email
            $prenom = $membre['Prenom'];
            $nom = $membre['Nom'];
            $lien_connexion = url('/connexion');

            ob_start();
            include __DIR__ . '/../templates/email_adhesion_acceptee.php';
            $messageHTML = ob_get_clean();

            $sujet = "Votre adhésion à KAST'ASSO a été acceptée";
            $nomComplet = $prenom . ' ' . $nom;

            // envoyer email
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

    // refuser demande adhesion
    public static function refuserAdhesion(): void
    {
        self::verifierAdmin();

        // determiner URL retour selon role
        $redirectUrl = ($_SESSION['user_type'] === 'gestionnaire') ? '/gestionnaire/adhesions' : '/admin/adhesions';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect($redirectUrl);
        }

        // check CSRF
        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = ['Token de sécurité invalide. Veuillez réessayer.'];
            self::redirect($redirectUrl);
        }

        $id = (int)($_POST['id_membre'] ?? 0);
        $motif = trim($_POST['motif_refus'] ?? '');

        if ($id === 0) {
            $_SESSION['errors'] = ['ID membre manquant'];
            self::redirect($redirectUrl);
        }

        if (empty($motif)) {
            $_SESSION['errors'] = ['Le motif du refus est obligatoire'];
            self::redirect($redirectUrl);
        }

        // recuperer infos membre
        $membre = Membre::getMembreParId($id);

        if (!$membre) {
            $_SESSION['errors'] = ['Membre introuvable'];
            self::redirect($redirectUrl);
        }

        // verifier que compte est valide
        if ($membre['Statut_compte'] === 'en_attente') {
            $_SESSION['errors'] = ['Ce compte est en attente de validation par l\'administrateur. Les demandes d\'adhésion ne peuvent pas encore être traitées pour ce membre.'];
            self::redirect($redirectUrl);
        }

        if ($membre['Statut_compte'] === 'refuse') {
            $_SESSION['errors'] = ['Ce compte a été refusé. Impossible de traiter l\'adhésion.'];
            self::redirect($redirectUrl);
        }

        if (Membre::refuserAdhesion($id, $motif)) {
            // preparer email
            $prenom = $membre['Prenom'];
            $nom = $membre['Nom'];
            $lien_connexion = url('/connexion');

            ob_start();
            include __DIR__ . '/../templates/email_adhesion_refusee.php';
            $messageHTML = ob_get_clean();

            $sujet = "Réponse à votre demande d'adhésion KAST'ASSO";
            $nomComplet = $prenom . ' ' . $nom;

            // envoyer email
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
            self::redirect('/connexion');
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

        require __DIR__ . '/../vues/admin/template_adhesion.php';
    }

    public static function uploadTemplateAdhesion(): void
    {
        self::verifierAdminOnly();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect('/admin/template-adhesion');
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = ['Token de sécurité invalide.'];
            self::redirect('/admin/template-adhesion');
        }

        // Vérifier le fichier
        if (!isset($_FILES['template_pdf']) || $_FILES['template_pdf']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['errors'] = ['Erreur lors du téléversement du fichier.'];
            self::redirect('/admin/template-adhesion');
        }

        $file = $_FILES['template_pdf'];

        // Vérifier le type MIME
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if ($mimeType !== 'application/pdf') {
            $_SESSION['errors'] = ['Le fichier doit être un PDF valide.'];
            self::redirect('/admin/template-adhesion');
        }

        // Vérifier la taille (max 5 Mo)
        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['errors'] = ['Le fichier est trop volumineux (max 5 Mo).'];
            self::redirect('/admin/template-adhesion');
        }

        // Créer le dossier si nécessaire
        $templateDir = self::getTemplateAdhesionPath();
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }

        // Supprimer l'ancien template s'il existe
        $oldTemplate = self::getCustomTemplatePath();
        if ($oldTemplate && file_exists($oldTemplate)) {
            unlink($oldTemplate);
        }

        // Enregistrer le nouveau fichier
        $newFileName = 'adhesion_template_' . date('Y-m-d_H-i-s') . '.pdf';
        $newFilePath = $templateDir . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $newFilePath)) {
            $_SESSION['success'] = 'Template personnalisé téléversé avec succès !';
        } else {
            $_SESSION['errors'] = ['Erreur lors de l\'enregistrement du fichier.'];
        }

        self::redirect('/admin/template-adhesion');
    }

    public static function deleteTemplateAdhesion(): void
    {
        self::verifierAdminOnly();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect('/admin/template-adhesion');
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = ['Token de sécurité invalide.'];
            self::redirect('/admin/template-adhesion');
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

        self::redirect('/admin/template-adhesion');
    }

    public static function previewTemplateAdhesion(): void
    {
        self::verifierAdminOnly();

        $customTemplate = self::getCustomTemplatePath();

        if ($customTemplate && file_exists($customTemplate)) {
            // Envoyer le PDF personnalisé
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="template_adhesion.pdf"');
            header('Content-Length: ' . filesize($customTemplate));
            readfile($customTemplate);
            exit;
        } else {
            // Générer le PDF par défaut
            require_once __DIR__ . '/../services/PDFService.php';
            PDFService::genererFormulaireAdhesionPreview();
        }
    }

}
