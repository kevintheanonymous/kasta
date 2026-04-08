<?php
require_once __DIR__ . '/../models/Membre.php';
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../models/EvenementSport.php';
require_once __DIR__ . '/../models/EvenementAsso.php';
require_once __DIR__ . '/../services/EmailService.php';
require_once __DIR__ . '/../services/FileUploadService.php';
require_once __DIR__ . '/../validators/InscriptionValidator.php';

class ControleurAuth {

    private const CSRF_ERR = 'Erreur de sécurité : requête invalide. Veuillez réessayer.';
    private const ROUTE_INSCRIPTION = '/inscription';
    private const ROUTE_CONNEXION = '/connexion';
    private const ROUTE_MDP_OUBLIE = '/mot_de_passe_oublie';
    private const ROUTE_REINIT_MDP = '/reinitialisation_mdp';
    private const TOKEN_PARAM = '?token=';

    private static function ensurerSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private static function redirigerVersTableauDeBord(): void
    {
        $userType = $_SESSION['user_type'] ?? '';

        switch ($userType) {
            case 'admin':
                rediriger('/admin/tableau_de_bord');
                break;
            case 'gestionnaire':
                rediriger('/gestionnaire/tableau_de_bord');
                break;
            case 'membre':
            default:
                rediriger('/membre/tableau_de_bord');
                break;
        }
    }

    public static function afficherAccueil(): void
    {
        self::ensurerSession();
        $evenements_sport = EvenementSport::findAllPublic();
        $evenements_asso = EvenementAsso::findAllPublic();
        require_once __DIR__ . '/../vues/accueil.php';
    }

    public static function afficherInscription(): void
    {
        self::ensurerSession();

        if (isset($_SESSION['user_id'])) {
            self::redirigerVersTableauDeBord();
        }

        require_once __DIR__ . '/../models/RegimeAlimentaire.php';
        $regimesAlimentaires = RegimeAlimentaire::tous();

        require_once __DIR__ . '/../vues/authentification/inscription.php';
    }

    public static function traiterInscription(): void
    {
        self::ensurerSession();

        if (isset($_SESSION['user_id'])) {
            self::redirigerVersTableauDeBord();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = [self::CSRF_ERR];
            rediriger(self::ROUTE_INSCRIPTION);
        }

        $data = self::extraireDataInscription();
        self::sauvegarderSessionInscription($data);

        $validation = InscriptionValidator::valider($data);
        if (!$validation['valid']) {
            $_SESSION['errors'] = $validation['errors'];
            rediriger(self::ROUTE_INSCRIPTION);
        }

        $url_photo = self::traiterUploadPhoto();
        $formulaire_adhesion_present = isset($_FILES['formulaire_adhesion']) && $_FILES['formulaire_adhesion']['error'] === UPLOAD_ERR_OK;

        $membreData = [
            'prenom' => $data['prenom'], 'nom' => $data['nom'], 'sexe' => $data['sexe'],
            'mail' => $data['email'], 'mdp' => $data['mdp'], 'telephone' => $data['telephone'],
            'url_photo' => $url_photo, 'taille_teeshirt' => $data['taille_teeshirt'],
            'taille_pull' => $data['taille_pull'], 'adherent' => 0, 'url_adhesion' => '',
            'commentaire_alim' => $data['commentaires'], 'regime_id' => $data['regime_id']
        ];

        $result = Membre::ajouterMembre($membreData);

        if (!$result['success']) {
            $_SESSION['errors'] = [$result['message']];
            rediriger(self::ROUTE_INSCRIPTION);
        }

        self::traiterAdhesionApresInscription($result['id'], $formulaire_adhesion_present);
        unset($_SESSION['inscription']);
        rediriger(self::ROUTE_INSCRIPTION);
        exit();
    }

    private static function extraireDataInscription(): array
    {
        return [
            'nom' => $_POST['nom'] ?? '',
            'prenom' => $_POST['prenom'] ?? '',
            'sexe' => $_POST['sexe'] ?? '',
            'email' => strtolower(trim($_POST['email'] ?? '')),
            'telephone' => $_POST['telephone'] ?? '',
            'mdp' => $_POST['mot_de_passe'] ?? '',
            'confmdp' => $_POST['confirmer_mdp'] ?? '',
            'taille_teeshirt' => $_POST['taille_teeshirt'] ?? '',
            'taille_pull' => $_POST['taille_pull'] ?? '',
            'commentaires' => $_POST['commentaires'] ?? '',
            'regime_id' => $_POST['regime_alimentaire'] ?? ''
        ];
    }

    private static function sauvegarderSessionInscription(array $data): void
    {
        $_SESSION['inscription'] = [
            'nom' => $data['nom'], 'prenom' => $data['prenom'], 'sexe' => $data['sexe'],
            'mail' => $data['email'], 'telephone' => $data['telephone'],
            'taille_teeshirt' => $data['taille_teeshirt'], 'taille_pull' => $data['taille_pull'],
            'commentaires' => $data['commentaires'], 'regime_id' => $data['regime_id']
        ];
    }

    private static function traiterUploadPhoto(): ?string
    {
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $uploadResult = FileUploadService::uploadPhoto($_FILES['photo']);
        if ($uploadResult['success']) {
            return $uploadResult['path'];
        }
        $_SESSION['errors'] = [$uploadResult['message']];
        rediriger(self::ROUTE_INSCRIPTION);
        return null;
    }

    private static function traiterAdhesionApresInscription(int $idMembre, bool $formulairePresent): void
    {
        if (!$formulairePresent) {
            $_SESSION['success'] = 'Inscription réussie ! Votre compte est en attente de validation.';
            return;
        }
        $uploadResult = FileUploadService::uploadFormulaireAdhesion($_FILES['formulaire_adhesion'], $idMembre);
        if ($uploadResult['success']) {
            Membre::soumettreDemandeAdhesion($idMembre, $uploadResult['path']);
            $_SESSION['success'] = 'Inscription réussie ! Votre compte et votre demande d\'adhésion sont en attente de validation.';
        } else {
            $_SESSION['success'] = 'Inscription réussie ! Votre compte est en attente de validation. Attention : ' . $uploadResult['message'];
        }
    }

    public static function afficherConnexion(): void
    {
        self::ensurerSession();

        if (isset($_SESSION['user_id'])) {
            self::redirigerVersTableauDeBord();
        }

        require_once __DIR__ . '/../vues/authentification/connexion.php';
    }

    public static function traiterConnexion(): void
    {
        self::ensurerSession();

        if (isset($_SESSION['user_id'])) {
            self::redirigerVersTableauDeBord();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = [self::CSRF_ERR];
            rediriger(self::ROUTE_CONNEXION);
        }

        $email = $_POST['email'] ?? '';
        $mdp = $_POST['mot_de_passe'] ?? '';

        if (empty($email) || empty($mdp)) {
            $_SESSION['errors'] = ["Veuillez remplir tous les champs."];
            rediriger(self::ROUTE_CONNEXION);
        }

        if (!self::verifierRateLimit('login_' . $email)) {
            $_SESSION['errors'] = ["Trop de tentatives. Veuillez patienter avant de réessayer."];
            rediriger(self::ROUTE_CONNEXION);
        }

        // SECURITE: Toujours verifier les deux tables pour eviter les timing attacks
        $result = Membre::connexion($email, $mdp);
        $resultAdmin = Admin::connexion($email, $mdp);

        if ($result['success']) {
            self::ouvrirSessionMembre($result['data'], $email);
        } elseif ($resultAdmin['success']) {
            self::ouvrirSessionAdmin($resultAdmin['data'], $email);
        } else {
            self::incrementerRateLimit('login_' . $email);
            $_SESSION['errors'] = ['Identifiants incorrects'];
            rediriger(self::ROUTE_CONNEXION);
        }
    }

    private static function ouvrirSessionMembre(array $membre, string $email): void
    {
        if ($membre['statut_compte'] !== 'valide') {
            $_SESSION['errors'] = ["Votre compte est en attente de validation ou a été refusé."];
            rediriger(self::ROUTE_CONNEXION);
        }

        self::resetRateLimit('login_' . $email);
        session_regenerate_id(true);

        $role = $membre['gestionnaire'] ? 'gestionnaire' : 'membre';
        $_SESSION['user'] = ['id' => $membre['id_membre'], 'nom' => $membre['nom'], 'prenom' => $membre['prenom'], 'role' => $role];
        $_SESSION['user_id'] = $membre['id_membre'];
        $_SESSION['user_type'] = $role;
        $_SESSION['user_name'] = $membre['prenom'] . ' ' . $membre['nom'];
        $_SESSION['is_member'] = true;
        $_SESSION['derniere_activite'] = time();

        rediriger($membre['gestionnaire'] ? '/gestionnaire/tableau_de_bord' : '/membre/tableau_de_bord');
        exit();
    }

    private static function ouvrirSessionAdmin(array $admin, string $email): void
    {
        self::resetRateLimit('login_' . $email);
        session_regenerate_id(true);

        $_SESSION['user'] = ['id' => $admin['id_admin'], 'nom' => $admin['identifiant'], 'prenom' => 'Admin', 'role' => 'admin'];
        $_SESSION['user_id'] = $admin['id_admin'];
        $_SESSION['user_type'] = 'admin';
        $_SESSION['user_name'] = 'Administrateur';
        $_SESSION['derniere_activite'] = time();

        rediriger('/admin/tableau_de_bord');
    }

    public static function deconnexion(): void
    {
        self::ensurerSession();
        session_destroy();
        rediriger(self::ROUTE_CONNEXION);
    }

    public static function afficherMotDePasseOublie(): void
    {
        self::ensurerSession();
        require_once __DIR__ . '/../vues/authentification/mot_de_passe_oublie.php';
    }

    public static function traiterMotDePasseOublie(): void
    {
        self::ensurerSession();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = [self::CSRF_ERR];
            rediriger(self::ROUTE_MDP_OUBLIE);
        }

        $email = $_POST['email'] ?? '';
        if (empty($email)) {
            $_SESSION['errors'] = ["Veuillez saisir votre adresse email."];
            rediriger(self::ROUTE_MDP_OUBLIE);
        }

        $token = bin2hex(random_bytes(32));
        [$userFound, $nomComplet] = self::setResetTokenPourUtilisateur($email, $token);

        if ($userFound) {
            self::envoyerEmailReset($email, $token, $nomComplet);
        } else {
            $safeEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
            error_log("Réinitialisation demandée pour email non trouvé: {$safeEmail}");
        }

        // SÉCURITÉ: Toujours afficher le même message pour éviter l'énumération d'utilisateurs
        $_SESSION['success'] = "Si cette adresse email est associée à un compte, un lien de réinitialisation vous a été envoyé.";
        rediriger(self::ROUTE_MDP_OUBLIE);
    }

    private static function setResetTokenPourUtilisateur(string $email, string $token): array
    {
        if (Membre::emailExiste($email) && Membre::setResetToken($email, $token)) {
            $membreInfo = Membre::getMembreByEmail($email);
            $nom = $membreInfo ? $membreInfo['Prenom'] . ' ' . $membreInfo['Nom'] : 'Utilisateur';
            return [true, $nom];
        }
        if (Admin::emailExiste($email) && Admin::setResetToken($email, $token)) {
            return [true, 'Administrateur'];
        }
        return [false, 'Utilisateur'];
    }

    private static function envoyerEmailReset(string $email, string $token, string $nomComplet): void
    {
        $lien_reset = url(self::ROUTE_REINIT_MDP) . '&token=' . $token;
        ob_start();
        include_once __DIR__ . '/../templates/email_reset_mdp.php';
        $messageHTML = ob_get_clean();
        $resultat = EmailService::envoyer($email, "Réinitialisation de mot de passe", $messageHTML, $nomComplet);
        if (!$resultat) {
            $safeEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
            error_log("Échec de l'envoi de l'email de réinitialisation à {$safeEmail}");
        }
    }

    public static function afficherReinitialisation(): void
    {
        self::ensurerSession();
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            $_SESSION['errors'] = ["Token manquant."];
            rediriger(self::ROUTE_CONNEXION);
        }

        $membre = Membre::verifyResetToken($token);
        $admin = Admin::verifyResetToken($token);

        if (!$membre && !$admin) {
            $_SESSION['errors'] = ["Ce lien de réinitialisation est invalide ou a expiré."];
            rediriger(self::ROUTE_CONNEXION);
        }

        require_once __DIR__ . '/../vues/authentification/reinitialisation_mdp.php';
    }

    public static function traiterReinitialisation(): void
    {
        self::ensurerSession();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = [self::CSRF_ERR];
            rediriger(self::ROUTE_CONNEXION);
        }

        $token = $_POST['token'] ?? '';
        $mdp = $_POST['mot_de_passe'] ?? '';
        $confmdp = $_POST['confirmer_mdp'] ?? '';
        $redirectToken = self::ROUTE_REINIT_MDP . self::TOKEN_PARAM . $token;

        if (empty($token) || empty($mdp) || empty($confmdp)) {
            $_SESSION['errors'] = ["Tous les champs sont obligatoires."];
            rediriger($redirectToken);
        }

        if ($mdp !== $confmdp) {
            $_SESSION['errors'] = ["Les mots de passe ne correspondent pas."];
            rediriger($redirectToken);
        }

        $erreursMdp = self::validerComplexiteMotDePasse($mdp);
        if (!empty($erreursMdp)) {
            $_SESSION['errors'] = $erreursMdp;
            rediriger($redirectToken);
        }

        if (self::reinitialiserMotDePasse($token, $mdp)) {
            $_SESSION['success'] = "Votre mot de passe a été réinitialisé avec succès.";
            rediriger(self::ROUTE_CONNEXION);
        }

        $_SESSION['errors'] = ["Une erreur est survenue ou le lien a expiré."];
        rediriger(self::ROUTE_CONNEXION);
    }

    private static function reinitialiserMotDePasse(string $token, string $mdp): bool
    {
        if (Membre::verifyResetToken($token)) {
            return Membre::updatePasswordByToken($token, $mdp);
        }
        if (Admin::verifyResetToken($token)) {
            return Admin::updatePasswordByToken($token, $mdp);
        }
        return false;
    }

    private static function validerComplexiteMotDePasse(string $mdp): array
    {
        $erreurs = [];
        if (strlen($mdp) < 8) {
            $erreurs[] = "Le mot de passe doit contenir au moins 8 caractères.";
        }
        if (!preg_match('/[A-Z]/', $mdp)) {
            $erreurs[] = "Le mot de passe doit contenir au moins une majuscule.";
        }
        if (!preg_match('/[a-z]/', $mdp)) {
            $erreurs[] = "Le mot de passe doit contenir au moins une minuscule.";
        }
        if (!preg_match('/\d/', $mdp)) {
            $erreurs[] = "Le mot de passe doit contenir au moins un chiffre.";
        }
        if (!preg_match('/[^A-Za-z0-9]/', $mdp)) {
            $erreurs[] = "Le mot de passe doit contenir au moins un caractère spécial.";
        }
        return $erreurs;
    }

    // Rate limiting via session
    private static function verifierRateLimit(string $cle): bool
    {
        $maxTentatives = (int) (Env::get('RATE_LIMIT_MAX_ATTEMPTS') ?: 5);
        $fenetre = (int) (Env::get('RATE_LIMIT_WINDOW_SECONDS') ?: 900);

        $rateLimits = $_SESSION['rate_limits'] ?? [];
        if (!isset($rateLimits[$cle])) {
            return true;
        }

        $entry = $rateLimits[$cle];
        if (time() - $entry['premier_essai'] > $fenetre) {
            unset($_SESSION['rate_limits'][$cle]);
            return true;
        }

        return $entry['tentatives'] < $maxTentatives;
    }

    private static function incrementerRateLimit(string $cle): void
    {
        if (!isset($_SESSION['rate_limits'])) {
            $_SESSION['rate_limits'] = [];
        }
        if (!isset($_SESSION['rate_limits'][$cle])) {
            $_SESSION['rate_limits'][$cle] = [
                'tentatives' => 0,
                'premier_essai' => time()
            ];
        }
        $_SESSION['rate_limits'][$cle]['tentatives']++;
    }

    private static function resetRateLimit(string $cle): void
    {
        unset($_SESSION['rate_limits'][$cle]);
    }
}
