<?php
require_once __DIR__ . '/../models/Membre.php';
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../models/EvenementSport.php';
require_once __DIR__ . '/../models/EvenementAsso.php';
require_once __DIR__ . '/../services/EmailService.php';
require_once __DIR__ . '/../services/FileUploadService.php';
require_once __DIR__ . '/../validators/InscriptionValidator.php';

class ControleurAuth {
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
        require __DIR__ . '/../vues/accueil.php';
    }
 

    public static function afficherInscription(): void
    {
        self::ensurerSession();
        
        if (isset($_SESSION['user_id'])) {
            self::redirigerVersTableauDeBord();
        }
        
        // recuperer la liste des regimes alimentaires pour le formulaire
        require_once __DIR__ . '/../models/RegimeAlimentaire.php';
        $regimesAlimentaires = RegimeAlimentaire::tous();
        
        require __DIR__ . '/../vues/authentification/inscription.php';
    }

    public static function traiterInscription(): void
    {
        self::ensurerSession();
        
        if (isset($_SESSION['user_id'])) {
            self::redirigerVersTableauDeBord();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifierTokenCSRF()) {
                $_SESSION['errors'] = ["Erreur de sécurité : requête invalide. Veuillez réessayer."];
                rediriger('/inscription');
            }

            $data = [
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

            $_SESSION['inscription'] = [
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'sexe' => $data['sexe'],
                'mail' => $data['email'],
                'telephone' => $data['telephone'],
                'taille_teeshirt' => $data['taille_teeshirt'],
                'taille_pull' => $data['taille_pull'],
                'commentaires' => $data['commentaires'],
                'regime_id' => $data['regime_id']
            ];

            $validation = InscriptionValidator::valider($data);

            if (!$validation['valid']) {
                $_SESSION['errors'] = $validation['errors'];
                rediriger('/inscription');
            }

            // gestion upload photo profil
            $url_photo = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = FileUploadService::uploadPhoto($_FILES['photo']);

                if ($uploadResult['success']) {
                    $url_photo = $uploadResult['path'];
                } else {
                    $_SESSION['errors'] = [$uploadResult['message']];
                    rediriger('/inscription');
                }
            }

            // gestion upload formulaire adhesion
            // on traite d'abord l'inscription pour avoir l'id membre, puis on uploade le fichier
            $url_adhesion = null;
            $formulaire_adhesion_present = isset($_FILES['formulaire_adhesion']) && $_FILES['formulaire_adhesion']['error'] === UPLOAD_ERR_OK;

            $membreData = [
                'prenom' => $data['prenom'],
                'nom' => $data['nom'],
                'sexe' => $data['sexe'],
                'mail' => $data['email'],
                'mdp' => $data['mdp'],
                'telephone' => $data['telephone'],
                'url_photo' => $url_photo,
                'taille_teeshirt' => $data['taille_teeshirt'],
                'taille_pull' => $data['taille_pull'],
                'adherent' => 0,
                'url_adhesion' => '',
                'commentaire_alim' => $data['commentaires'],
                'regime_id' => $data['regime_id']
            ];

            $result = Membre::ajouterMembre($membreData);

            if ($result['success']) {
                $idMembre = $result['id'];

                // si formulaire adhesion present, uploader et soumettre demande
                if ($formulaire_adhesion_present) {
                    $uploadAdhesionResult = FileUploadService::uploadFormulaireAdhesion($_FILES['formulaire_adhesion'], $idMembre);

                    if ($uploadAdhesionResult['success']) {
                        // soumettre la demande adhesion (met statut en_attente)
                        Membre::soumettreDemandeAdhesion($idMembre, $uploadAdhesionResult['path']);
                        $_SESSION['success'] = 'Inscription réussie ! Votre compte et votre demande d\'adhésion sont en attente de validation.';
                    } else {
                        // fichier adhesion invalide mais inscription ok
                        $_SESSION['success'] = 'Inscription réussie ! Votre compte est en attente de validation. Attention : ' . $uploadAdhesionResult['message'];
                    }
                } else {
                    $_SESSION['success'] = 'Inscription réussie ! Votre compte est en attente de validation.';
                }

                unset($_SESSION['inscription']);
                rediriger('/inscription');
            } else {
                $_SESSION['errors'] = [$result['message']];
                rediriger('/inscription');
            }
            exit();
        }
    }

    public static function afficherConnexion(): void
    {
        self::ensurerSession();
        
        if (isset($_SESSION['user_id'])) {
            self::redirigerVersTableauDeBord();
        }
        
        require __DIR__ . '/../vues/authentification/connexion.php';
    }

    public static function traiterConnexion(): void
    {
        self::ensurerSession();
        
        if (isset($_SESSION['user_id'])) {
            self::redirigerVersTableauDeBord();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifierTokenCSRF()) {
                $_SESSION['errors'] = ["Erreur de sécurité : requête invalide. Veuillez réessayer."];
                rediriger('/connexion');
            }
            
            $email = $_POST['email'] ?? '';
            $mdp = $_POST['mot_de_passe'] ?? '';

            if (empty($email) || empty($mdp)) {
                $_SESSION['errors'] = ["Veuillez remplir tous les champs."];
                rediriger('/connexion');
            }

            $result = Membre::connexion($email, $mdp);

            if ($result['success']) {
                $membre = $result['data'];
                
                // Vérification du statut du compte
                if ($membre['statut_compte'] !== 'valide') {
                    $_SESSION['errors'] = ["Votre compte est en attente de validation ou a été refusé."];
                    rediriger('/connexion');
                }

                // SÉCURITÉ: Régénérer l'ID de session pour prévenir la session fixation
                session_regenerate_id(true);

                // Connexion réussie
                $_SESSION['user'] = [
                    'id' => $membre['id_membre'],
                    'nom' => $membre['nom'],
                    'prenom' => $membre['prenom'],
                    'role' => $membre['gestionnaire'] ? 'admin' : 'membre'
                ];
                
                // on met l'id dans la session (pour les anciennes parties du code qui en ont besoin)
                $_SESSION['user_id'] = $membre['id_membre'];
                
                // le type d'user pour le controleur admin
                $_SESSION['user_type'] = $membre['gestionnaire'] ? 'gestionnaire' : 'membre';
                
                // le nom pour l'afficher dans l'interface
                $_SESSION['user_name'] = $membre['prenom'] . ' ' . $membre['nom'];
                
                $_SESSION['is_member'] = true;
                
                // on demarre le timer pour le timeout de session
                $_SESSION['derniere_activite'] = time();

                // on redirige selon le role
                if ($membre['gestionnaire']) {
                    rediriger('/gestionnaire/tableau_de_bord');
                } else {
                    rediriger('/membre/tableau_de_bord');
                }
                exit();
            } else {
                // si c'est pas un membre on essaye en tant qu'admin
                $resultAdmin = Admin::connexion($email, $mdp);
                
                if ($resultAdmin['success']) {
                    $admin = $resultAdmin['data'];
                    
                    // SÉCURITÉ: Régénérer l'ID de session pour prévenir la session fixation
                    session_regenerate_id(true);
                    
                    // c'est un admin qui se connecte
                    $_SESSION['user'] = [
                        'id' => $admin['id_admin'],
                        'nom' => $admin['identifiant'], // Ou autre champ pertinent
                        'prenom' => 'Admin',
                        'role' => 'admin'
                    ];
                    
                    $_SESSION['user_id'] = $admin['id_admin'];
                    $_SESSION['user_type'] = 'admin';
                    $_SESSION['user_name'] = 'Administrateur';
                    
                    // pareil on demarre le timer
                    $_SESSION['derniere_activite'] = time();

                    rediriger('/admin/tableau_de_bord');
                }

                $_SESSION['errors'] = [$result['message']]; // Ou un message générique
                rediriger('/connexion');
            }
        }
    }

    public static function deconnexion(): void
    {
        self::ensurerSession();
        session_destroy();
        rediriger('/connexion');
    }

    public static function afficherMotDePasseOublie(): void
    {
        self::ensurerSession();
        require __DIR__ . '/../vues/authentification/mot_de_passe_oublie.php';
    }

    public static function traiterMotDePasseOublie(): void
    {
        self::ensurerSession();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // on check le csrf
            if (!verifierTokenCSRF()) {
                $_SESSION['errors'] = ["Erreur de sécurité : requête invalide. Veuillez réessayer."];
                rediriger('/mot_de_passe_oublie');
            }
            
            $email = $_POST['email'] ?? '';

            if (empty($email)) {
                $_SESSION['errors'] = ["Veuillez saisir votre adresse email."];
                rediriger('/mot_de_passe_oublie');
            }

            // on regarde si c'est un membre ou un admin
            $isMembre = Membre::emailExiste($email);
            $isAdmin = Admin::emailExiste($email);
            
            $token = bin2hex(random_bytes(32));
            $userFound = false;
            $nomComplet = "Utilisateur";

            // d'abord on essaye avec les membres (priorité)
            if ($isMembre) {
                if (Membre::setResetToken($email, $token)) {
                    $userFound = true;
                    // on recupere le nom pour l'email
                    $membreInfo = Membre::getMembreByEmail($email);
                    if ($membreInfo) {
                        $nomComplet = $membreInfo['Prenom'] . ' ' . $membreInfo['Nom'];
                    }
                }
            } elseif ($isAdmin) {
                // sinon on essaye avec les admins
                if (Admin::setResetToken($email, $token)) {
                    $userFound = true;
                    $nomComplet = "Administrateur";
                }
            }

            // on envoie l'email seulement si l'user existe
            if ($userFound) {
                // on construit l'url avec le token
                // le & c'est parce que url() genere deja un ?
                $lien_reset = url('/reinitialisation_mdp') . '&token=' . $token;
                
                ob_start();
                include __DIR__ . '/../templates/email_reset_mdp.php';
                $messageHTML = ob_get_clean();

                $resultat = EmailService::envoyer($email, "Réinitialisation de mot de passe", $messageHTML, $nomComplet);
                if (!$resultat) {
                    error_log("Échec de l'envoi de l'email de réinitialisation à {$email}");
                }
            } else {
                error_log("Réinitialisation demandée pour email non trouvé: {$email}");
            }
            
            // SÉCURITÉ: Toujours afficher le même message pour éviter l'énumération d'utilisateurs
            $_SESSION['success'] = "Si cette adresse email est associée à un compte, un lien de réinitialisation vous a été envoyé.";

            rediriger('/mot_de_passe_oublie');
        }
    }

    public static function afficherReinitialisation(): void
    {
        self::ensurerSession();
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            $_SESSION['errors'] = ["Token manquant."];
            rediriger('/connexion');
        }

        // on check si le token est bon
        $membre = Membre::verifyResetToken($token);
        $admin = Admin::verifyResetToken($token);

        if (!$membre && !$admin) {
            $_SESSION['errors'] = ["Ce lien de réinitialisation est invalide ou a expiré."];
            rediriger('/connexion');
        }

        // on passe le token a la vue
        require __DIR__ . '/../vues/authentification/reinitialisation_mdp.php';
    }

    public static function traiterReinitialisation(): void
    {
        self::ensurerSession();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifierTokenCSRF()) {
                $_SESSION['errors'] = ["Erreur de sécurité : requête invalide. Veuillez réessayer."];
                rediriger('/connexion');
            }
            
            $token = $_POST['token'] ?? '';
            $mdp = $_POST['mot_de_passe'] ?? '';
            $confmdp = $_POST['confirmer_mdp'] ?? '';

            if (empty($token) || empty($mdp) || empty($confmdp)) {
                $_SESSION['errors'] = ["Tous les champs sont obligatoires."];
                rediriger('/reinitialisation_mdp&token=' . $token);
            }

            if ($mdp !== $confmdp) {
                $_SESSION['errors'] = ["Les mots de passe ne correspondent pas."];
                rediriger('/reinitialisation_mdp&token=' . $token);
            }

            // on regarde si c'est un membre ou un admin qui reset
            $membre = Membre::verifyResetToken($token);
            $admin = Admin::verifyResetToken($token);

            if ($membre) {
                if (Membre::updatePasswordByToken($token, $mdp)) {
                    $_SESSION['success'] = "Votre mot de passe a été réinitialisé avec succès.";
                    rediriger('/connexion');
                }
            } elseif ($admin) {
                if (Admin::updatePasswordByToken($token, $mdp)) {
                    $_SESSION['success'] = "Votre mot de passe a été réinitialisé avec succès.";
                    rediriger('/connexion');
                }
            }

            $_SESSION['errors'] = ["Une erreur est survenue ou le lien a expiré."];
            rediriger('/connexion');
        }
    }
}
