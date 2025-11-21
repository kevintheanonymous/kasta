<?php
require_once __DIR__ . '/../models/Membre.php';
require_once __DIR__ . '/../models/Admin.php';

class ControleurAuth {
    private static function ensurerSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function afficherAccueil(): void
    {
        self::ensurerSession();
        require __DIR__ . '/../vues/accueil.php';
    }

    public static function afficherInscriptionPartie1(): void
    {
        self::ensurerSession();
        require __DIR__ . '/../vues/authentification/inscription.php';
    }

    public static function traiterInscriptionPartie1(): void
    {
        self::ensurerSession();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_SESSION['inscription']['nom']       = $_POST['nom'] ?? '';
            $_SESSION['inscription']['prenom']    = $_POST['prenom'] ?? '';
            $_SESSION['inscription']['mail']      = $_POST['email'] ?? '';
            $_SESSION['inscription']['telephone'] = $_POST['telephone'] ?? '';
            $_SESSION['inscription']['mdp']       = $_POST['mot_de_passe'] ?? '';

            header('Location: /kastasso/public/index.php?path=/inscription-partie2');
            exit();
        }
    }

    public static function afficherInscriptionPartie2(): void
    {
        self::ensurerSession();
        require __DIR__ . '/../vues/authentification/inscription_partie2.php';
    }

    public static function traiterInscriptionPartie2(): void
    {
        self::ensurerSession();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_SESSION['inscription']['taille_teeshirt']   = $_POST['taille_teeshirt'] ?? '';
            $_SESSION['inscription']['taille_pull']       = $_POST['taille_pull'] ?? '';
            $_SESSION['inscription']['commentaire_alim']  = $_POST['commentaires'] ?? '';

            header('Location: /kastasso/public/index.php?path=/inscription-partie3');
            exit();
        }
    }

    public static function afficherInscriptionPartie3(): void
    {
        self::ensurerSession();
        require __DIR__ . '/../vues/authentification/inscription_partie3.php';
    }

    public static function traiterInscriptionPartie3(): void
    {
        self::ensurerSession();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'Annuler') {
                unset($_SESSION['inscription']);
                header('Location: /kastasso/public/index.php?path=/inscription');
                exit();
            }
            
            if ($action === "Confirmer l'inscription") {
                $_SESSION['inscription']['nom']              = $_POST['nom'] ?? $_SESSION['inscription']['nom'];
                $_SESSION['inscription']['prenom']           = $_POST['prenom'] ?? $_SESSION['inscription']['prenom'];
                $_SESSION['inscription']['mail']             = $_POST['email'] ?? $_SESSION['inscription']['mail'];
                $_SESSION['inscription']['telephone']        = $_POST['telephone'] ?? $_SESSION['inscription']['telephone'];
                $_SESSION['inscription']['taille_teeshirt']  = $_POST['taille_teeshirt'] ?? $_SESSION['inscription']['taille_teeshirt'];
                $_SESSION['inscription']['taille_pull']      = $_POST['taille_pull'] ?? $_SESSION['inscription']['taille_pull'];
                $_SESSION['inscription']['commentaire_alim'] = $_POST['commentaires'] ?? $_SESSION['inscription']['commentaire_alim'];
                $_SESSION['inscription']['adherent']         = isset($_POST['adherent']) ? 1 : 0;

                $url_photo = null;
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../../uploads/';
                    $ext       = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                    $allowed   = ['jpg', 'jpeg', 'png', 'gif'];

                    if (in_array($ext, $allowed, true)) {
                        $newFileName = uniqid('img_', true) . '.' . $ext;
                        $destPath    = $uploadDir . $newFileName;

                        if (move_uploaded_file($_FILES['photo']['tmp_name'], $destPath)) {
                            $url_photo = '/uploads/' . $newFileName;
                        }
                    }
                }

                $plainPassword = $_SESSION['inscription']['mdp'];

                $data = [
                    'prenom'          => $_SESSION['inscription']['prenom'],
                    'nom'             => $_SESSION['inscription']['nom'],
                    'mail'            => $_SESSION['inscription']['mail'],
                    'mdp'             => $plainPassword,
                    'telephone'       => $_SESSION['inscription']['telephone'],
                    'url_photo'       => $url_photo,
                    'taille_teeshirt' => $_SESSION['inscription']['taille_teeshirt'],
                    'taille_pull'     => $_SESSION['inscription']['taille_pull'],
                    'adherent'        => $_SESSION['inscription']['adherent'],
                    'url_adhesion'    => isset($_POST['signature_adhesion']) ? 'signed' : null,
                    'commentaire_alim'=> $_SESSION['inscription']['commentaire_alim']
                ];

                if (Membre::emailExiste($data['mail'])) {
                    $_SESSION['errors'] = ["Un compte avec cet email existe déjà"];
                    header('Location: /kastasso/public/index.php?path=/inscription-partie3');
                    exit();
                }
                $result = Membre::ajouterMembre($data);
            
                if ($result['success']) {
                    
                    $_SESSION['success'] = 'Inscription réussie ! Votre compte est en attente de validation.';
                    unset($_SESSION['inscription']);
                    header('Location: /kastasso/public/index.php?path=/connexion');
                } else {
                    $_SESSION['errors'] = [$result['message']];
                    header('Location: /kastasso/public/index.php?path=/inscription-partie3');
                }
                
            }
        }
    }

    public static function afficherConnexion(): void
    {
        self::ensurerSession();
        require __DIR__ . '/../vues/authentification/connexion.php';
    }

    public static function traiterConnexion(): void
    {
        self::ensurerSession();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $mail = trim($_POST['email'] ?? '');
        $mdp  = trim($_POST['mot_de_passe'] ?? '');

        if ($mail === '' || $mdp === '') {
            $_SESSION['errors'] = ['Tous les champs sont requis.'];
            header('Location: /kastasso/public/index.php?path=/connexion');
            exit();
        }

        $admin = Admin::connexion($mail, $mdp);
        if ($admin['success']) {
            $_SESSION['user_id']        = $admin['data']['id_admin'];
            $_SESSION['user_type']      = 'admin';
            $_SESSION['user_identifiant'] = $admin['data']['identifiant'];
            $_SESSION['user_name']      = $admin['data']['identifiant'];
            header('Location: /kastasso/public/index.php?path=/admin/tableau_de_bord');
            exit();
        }

        $membre = Membre::connexion($mail, $mdp);
        if ($membre['success']) {
            $data = $membre['data'];

            if ($data['statut_compte'] !== 'VALIDE') {
                $_SESSION['errors'] = $data['statut_compte'] === 'ATTENTE'
                    ? ['Compte en attente de validation.']
                    : ['Compte refusé.'];
                header('Location: /kastasso/public/index.php?path=/connexion');
                exit();
            }

            $_SESSION['user_id']   = $data['id_membre'];
            $_SESSION['user_type'] = $data['gestionnaire'] ? 'gestionnaire' : 'membre';
            $_SESSION['user_name'] = $data['prenom'] . ' ' . $data['nom'];
            header('Location: /kastasso/public/index.php?path=/membre/tableau_de_bord');
            exit();
        }

        $_SESSION['errors'] = ['Identifiants incorrects.'];
        header('Location: /kastasso/public/index.php?path=/connexion');
        exit();
    }

    public static function deconnexion(): void
{
    self::ensurerSession();
    session_destroy();
    header('Location: /kastasso/public/index.php?path=/connexion');
    exit();
}

}