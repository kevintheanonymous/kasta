<?php

require_once __DIR__ . '/../models/Membre.php';
require_once __DIR__ . '/../models/EvenementSport.php';
require_once __DIR__ . '/../models/EvenementAsso.php';
require_once __DIR__ . '/../models/Creneau.php';
require_once __DIR__ . '/../models/Participation.php';
require_once __DIR__ . '/../services/FileUploadService.php';
require_once __DIR__ . '/../services/EmailService.php';
require_once __DIR__ . '/../validators/ProfilValidator.php';


class ControleurMembre
{
    private const ROUTE_CONNEXION = '/connexion';
    private const CSRF_ERR = 'Token de sécurité invalide. Veuillez réessayer.';
    private const ROUTE_PROFIL = '/membre/profil';
    private const ROUTE_PROFIL_EDIT = '/membre/profil/edit';
    private const ROUTE_DASHBOARD = '/membre/tableau_de_bord';
    private const ROUTE_INSCRIPTIONS_ASSO = '/membre/mes_inscriptions_asso';
    private const ROUTE_INSCRIPTIONS_SPORT = '/membre/mes_inscriptions_sport';
    private const ROUTE_INSCRIPTION_SPORT = '/membre/inscription/sport?id=';
    private const ROUTE_SECURITE = '/membre/securite';
    private const CONTENT_TYPE_JSON = 'Content-Type: application/json';
    private const ERR_EVENT = 'Événement non trouvé.';
    private const ERR_DESINSCRIPTION = 'Erreur lors de la désinscription.';

    private static function ensurerSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function afficherTableauDeBord(): void
    {
        self::verifierMembre();

        $userName = $_SESSION['user_name'] ?? 'Membre';
        $userId = $_SESSION['user_id'] ?? 0;

        // check si le membre est adherent
        $isAdherent = false;
        if ($userId) {
            $membre = Membre::getMembreParId($userId);
            $isAdherent = ($membre && $membre['Adherent'] == 1);
        }

        $evenements_sport = EvenementSport::findAllPublic();
        $evenements_asso = EvenementAsso::findAll(false, $isAdherent, false);

        // recup les inscriptions du membre
        $mes_inscriptions_sport = Participation::getMesInscriptionsSport($userId);
        $mes_inscriptions_asso = Participation::getMesInscriptionsAsso($userId);

        require_once __DIR__ . '/../vues/membre/tableau_de_bord.php';
    }

    public static function afficherEvenements(): void
    {
        self::verifierMembre();
        rediriger('/');
    }

    public static function afficherProfil(): void
    {
        self::verifierMembre();
        $id = $_SESSION['user_id'] ?? 0;

        if ($id) {
            $membre = Membre::getMembreParId($id);
            if (!$membre) {
                $_SESSION['errors'] = ['Profil introuvable.'];
                rediriger(self::ROUTE_CONNEXION);
                return;
            }
            require_once __DIR__ . '/../vues/membre/profil.php';
        } else {
            rediriger(self::ROUTE_CONNEXION);
        }
    }

    public static function devenirAdherent(): void
    {
        self::verifierMembre();

        // Vérification CSRF
        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = [self::CSRF_ERR];
            rediriger(self::ROUTE_PROFIL);
            return;
        }

        $id = $_SESSION['user_id'] ?? 0;

        if ($id) {
            if (Membre::devenirAdherent($id)) {
                // On pourrait ajouter un message flash ici si on avait un système de session flash
            }
            rediriger(self::ROUTE_PROFIL);
        } else {
            rediriger(self::ROUTE_CONNEXION);
        }
    }

    public static function soumettreAdhesion(): void
    {
        self::verifierMembre();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger(self::ROUTE_PROFIL);
            return;
        }

        // check CSRF
        if (!verifierTokenCSRF()) {
            $_SESSION['errors'] = [self::CSRF_ERR];
            rediriger(self::ROUTE_PROFIL);
            return;
        }

        $id = $_SESSION['user_id'] ?? 0;

        if (!$id) {
            rediriger(self::ROUTE_CONNEXION);
            return;
        }

        // check fichier
        if (!isset($_FILES['formulaire_adhesion']) || $_FILES['formulaire_adhesion']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['errors'] = ['Aucun fichier uploadé ou erreur lors de l\'upload'];
            rediriger(self::ROUTE_PROFIL);
            return;
        }

        // upload fichier via service
        $uploadResult = FileUploadService::uploadFormulaireAdhesion($_FILES['formulaire_adhesion'], $id);

        if (!$uploadResult['success']) {
            $_SESSION['errors'] = [$uploadResult['message']];
            rediriger(self::ROUTE_PROFIL);
            return;
        }

        // soumettre demande adhesion
        if (Membre::soumettreDemandeAdhesion($id, $uploadResult['path'])) {
            $_SESSION['success'] = 'Votre demande d\'adhésion a été soumise avec succès';
        } else {
            $_SESSION['errors'] = ['Erreur lors de la soumission de la demande'];
        }

        rediriger(self::ROUTE_PROFIL);
    }
    public static function afficherEditionProfil(): void
    {
        self::verifierMembre();
        $id = $_SESSION['user_id'];
        $membre = Membre::getMembreParId($id);
        if (!$membre) {
            $_SESSION['errors'] = ['Profil introuvable.'];
            rediriger(self::ROUTE_CONNEXION);
            return;
        }

        // recuperer la liste des regimes alimentaires pour le formulaire
        require_once __DIR__ . '/../models/RegimeAlimentaire.php';
        $regimesAlimentaires = RegimeAlimentaire::tous();

        require_once __DIR__ . '/../vues/membre/edition_profil.php';
    }

    public static function traiterEditionProfil(): void
    {
        self::verifierMembre();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérification CSRF
            if (!verifierTokenCSRF()) {
                $_SESSION['errors'] = [self::CSRF_ERR];
                rediriger(self::ROUTE_PROFIL_EDIT);
                return;
            }

            $id = $_SESSION['user_id'];

            // Préparer les données pour validation
            $dataValidation = [
                'nom' => trim($_POST['nom'] ?? ''),
                'prenom' => trim($_POST['prenom'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'telephone' => trim($_POST['telephone'] ?? ''),
                'taille_teeshirt' => $_POST['taille_teeshirt'] ?? '',
                'taille_pull' => $_POST['taille_pull'] ?? '',
                'commentaires' => trim($_POST['commentaires'] ?? ''),
                'regime_id' => $_POST['regime_alimentaire'] ?? ''
            ];

            // Validation des données
            $validation = ProfilValidator::valider($dataValidation, $id);
            if (!$validation['valid']) {
                $_SESSION['errors'] = $validation['errors'];
                rediriger(self::ROUTE_PROFIL_EDIT);
                return;
            }

            // Handle Photo Upload via service
            $url_photo = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = FileUploadService::uploadPhoto($_FILES['photo']);

                if ($uploadResult['success']) {
                    $url_photo = $uploadResult['path'];
                } else {
                    $_SESSION['errors'] = [$uploadResult['message']];
                    rediriger(self::ROUTE_PROFIL_EDIT);
                    return;
                }
            }

            $data = [
                'nom' => $dataValidation['nom'],
                'prenom' => $dataValidation['prenom'],
                'mail' => $dataValidation['email'],
                'telephone' => $dataValidation['telephone'],
                'taille_teeshirt' => $dataValidation['taille_teeshirt'],
                'taille_pull' => $dataValidation['taille_pull'],
                'commentaire_alim' => $dataValidation['commentaires'],
                'regime_id' => $dataValidation['regime_id'],
                'url_photo' => $url_photo
            ];

            if (Membre::updateMembre($id, $data)) {
                // Update session name if changed
                $_SESSION['user_name'] = $data['prenom'] . ' ' . $data['nom'];
                $_SESSION['success'] = 'Profil mis à jour avec succès.';
                rediriger(self::ROUTE_PROFIL);
            } else {
                $_SESSION['errors'] = ['Erreur lors de la mise à jour.'];
                rediriger(self::ROUTE_PROFIL_EDIT);
            }
        }
    }

    public static function supprimerCompte(): void
    {
        self::verifierMembre();

        // Vérification CSRF
        if (!verifierTokenCSRF()) {
            $_SESSION['error'] = self::CSRF_ERR;
            rediriger(self::ROUTE_PROFIL);
            return;
        }

        $id = $_SESSION['user_id'];

        if (Membre::supprimerMembre($id)) {
            session_destroy();
            rediriger('/');
        } else {
            rediriger(self::ROUTE_PROFIL);
        }
    }
    private static function verifierMembre(): void
    {
        self::ensurerSession();
        if (!estConnecte()) {
            rediriger(self::ROUTE_CONNEXION);
        }
        // Un admin (table admin) n'est pas un membre, il doit aller sur son espace
        if (($_SESSION['user_type'] ?? '') === 'admin') {
            $_SESSION['errors'] = ['Les administrateurs doivent utiliser leur espace dédié.'];
            rediriger('/admin/tableau_de_bord');
        }
        empecherMiseEnCache();
    }

    // inscription aux events sportifs et asso

    public static function afficherInscriptionSport(): void
    {
        self::verifierMembre();

        $idEvent = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$idEvent) {
            $_SESSION['error'] = self::ERR_EVENT;
            rediriger(self::ROUTE_DASHBOARD);
            return;
        }

        $evenement = EvenementSport::findById($idEvent);
        if (!$evenement) {
            $_SESSION['error'] = self::ERR_EVENT;
            rediriger(self::ROUTE_DASHBOARD);
            return;
        }

        $userId = $_SESSION['user_id'];
        $creneaux = Creneau::findByEvent($idEvent);
        $creneauxInscrits = Participation::getCreneauxInscritsMembre($userId, $idEvent);

        // Pour chaque creneau, on recupere les postes disponibles
        foreach ($creneaux as &$creneau) {
            $creneau['postes_disponibles'] = CreneauPoste::getPostesPourCreneau($creneau['Id_creneau']);
        }
        unset($creneau); // libere la reference

        // Vérifier si les inscriptions sont closes
        $inscriptionsClosed = strtotime($evenement['date_cloture']) < time();

        require_once __DIR__ . '/../vues/membre/inscription_sport.php';
    }

    public static function traiterInscriptionSport(): void
    {
        self::verifierMembre();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger(self::ROUTE_DASHBOARD);
            return;
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['error'] = self::CSRF_ERR;
            rediriger(self::ROUTE_DASHBOARD);
            return;
        }

        $idEvent = isset($_POST['id_event']) ? (int)$_POST['id_event'] : 0;
        $creneauxSelectionnes = $_POST['creneaux'] ?? [];
        // Récupérer les préférences de postes (peut être plusieurs)
        $preferencesPostes = $_POST['preferences_postes'] ?? [];
        // Nettoyer et convertir en tableau d'entiers
        $preferencesPostes = array_filter(array_map('intval', $preferencesPostes));
        $userId = $_SESSION['user_id'];

        if (!$idEvent) {
            $_SESSION['error'] = self::ERR_EVENT;
            rediriger(self::ROUTE_DASHBOARD);
            return;
        }

        // Vérifier que l'événement existe
        $evenement = EvenementSport::findById($idEvent);
        if (!$evenement) {
            $_SESSION['error'] = self::ERR_EVENT;
            rediriger(self::ROUTE_DASHBOARD);
            return;
        }

        // Vérifier que les inscriptions ne sont pas closes
        if (strtotime($evenement['date_cloture']) < time()) {
            $_SESSION['error'] = "Les inscriptions sont closes pour cet événement.";
            rediriger(self::ROUTE_INSCRIPTION_SPORT . $idEvent);
            return;
        }

        $nbInscrits = 0;
        $erreurs = [];

        foreach ($creneauxSelectionnes as $idCreneau) {
            $idCreneau = (int)$idCreneau;

            // Vérifier que le créneau appartient bien à cet événement
            $creneau = Creneau::findById($idCreneau);
            if (!$creneau || $creneau['Id_Event_sportif'] != $idEvent) {
                $erreurs[] = "Créneau invalide.";
                continue;
            }

            // Inscrire au créneau avec les preferences de postes si elles ont ete choisies
            if (Participation::inscrireCreneau($idCreneau, $userId, $preferencesPostes)) {
                $nbInscrits++;
            }
        }

        if ($nbInscrits > 0) {
            $_SESSION['success'] = "Vous avez été inscrit à $nbInscrits créneau(x) avec succès. Vous pouvez vous inscrire à d'autres créneaux ci-dessous.";
            rediriger(self::ROUTE_INSCRIPTION_SPORT . $idEvent);
        } elseif (empty($erreurs)) {
            $_SESSION['info'] = "Aucun nouveau créneau sélectionné ou vous êtes déjà inscrit.";
            rediriger(self::ROUTE_INSCRIPTION_SPORT . $idEvent);
        } else {
            $_SESSION['error'] = implode(' ', $erreurs);
            rediriger(self::ROUTE_INSCRIPTION_SPORT . $idEvent);
        }
    }

    public static function traiterDesinscriptionSport(): void
    {
        self::verifierMembre();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger(self::ROUTE_DASHBOARD);
            return;
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['error'] = self::CSRF_ERR;
            rediriger(self::ROUTE_DASHBOARD);
            return;
        }

        $idCreneau = isset($_POST['id_creneau']) ? (int)$_POST['id_creneau'] : 0;
        $idEvent = isset($_POST['id_event']) ? (int)$_POST['id_event'] : 0;
        $userId = $_SESSION['user_id'];

        if (!$idCreneau) {
            $_SESSION['error'] = "Créneau non trouvé.";
            rediriger(self::ROUTE_DASHBOARD);
            return;
        }

        // Désinscrire du créneau
        if (Participation::desinscrireCreneau($idCreneau, $userId)) {
            $_SESSION['success'] = "Vous avez été désinscrit du créneau avec succès.";
        } else {
            $_SESSION['error'] = self::ERR_DESINSCRIPTION;
        }

        rediriger(self::ROUTE_INSCRIPTION_SPORT . $idEvent);
    }

    public static function afficherInscriptionAsso(): void
    {
        self::verifierMembre();

        $idEvent = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$idEvent) {
            $_SESSION['error'] = self::ERR_EVENT;
            rediriger(self::ROUTE_DASHBOARD);
            return;
        }

        $evenement = EvenementAsso::findById($idEvent);
        if (!$evenement) {
            $_SESSION['error'] = self::ERR_EVENT;
            rediriger(self::ROUTE_DASHBOARD);
            return;
        }

        // Vérifier l'accès aux événements privés
        $userId = $_SESSION['user_id'];
        $membre = Membre::getMembreParId($userId);
        $isAdherent = ($membre && $membre['Adherent'] == 1);

        if ($evenement['prive'] && !$isAdherent) {
            $_SESSION['error'] = "Cet événement est réservé aux adhérents.";
            rediriger(self::ROUTE_DASHBOARD);
            return;
        }

        // Récupérer l'inscription existante si elle existe
        $inscription = Participation::getInscriptionEventAsso($userId, $idEvent);

        // Vérifier si les inscriptions sont closes
        $inscriptionsClosed = strtotime($evenement['date_cloture']) < time();

        // Récupérer les infos du membre pour le formulaire
        $userEmail = $membre['Mail'] ?? '';
        $userNom = $membre['Nom'] ?? '';
        $userPrenom = $membre['Prenom'] ?? '';

        // detecter le mode (creation ou edition)
        $mode = isset($_GET['mode']) ? $_GET['mode'] : 'creation';
        $accompagnateurs = [];
        $tarifMembre = 0;

        // si mode edition, charger les accompagnateurs existants
        if ($mode === 'edition' && $inscription) {
            $accompagnateurs = Participation::getAccompagnateurs($userId, $idEvent);

            // calculer le tarif du membre
            $aParticipe = Participation::aParticipeEventSportifRecement($userEmail);
            $tarifMembre = $aParticipe ? 0 : (float)$evenement['tarif'];
        }

        require_once __DIR__ . '/../vues/membre/inscription_asso.php';
    }

    public static function traiterInscriptionAsso(): void
    {
        self::verifierMembre();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger(self::ROUTE_DASHBOARD);
            return;
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['error'] = self::CSRF_ERR;
            rediriger(self::ROUTE_DASHBOARD);
            return;
        }

        $idEvent = isset($_POST['id_event']) ? (int)$_POST['id_event'] : 0;
        $nbInvites = isset($_POST['nb_invites']) ? max(0, (int)$_POST['nb_invites']) : 0;
        $userId = $_SESSION['user_id'];

        if (!$idEvent) {
            $_SESSION['error'] = self::ERR_EVENT;
            rediriger(self::ROUTE_DASHBOARD);
            return;
        }

        $evenement = EvenementAsso::findById($idEvent);
        if (!$evenement) {
            $_SESSION['error'] = self::ERR_EVENT;
            rediriger(self::ROUTE_DASHBOARD);
            return;
        }

        if (!self::verifierAccesEvenementAsso($evenement, $userId, $idEvent)) {
            return;
        }

        $accompagnateursData = self::extraireAccompagnateurs($idEvent);
        self::enregistrerInscriptionAsso($userId, $idEvent, $nbInvites, $accompagnateursData);
    }

    private static function verifierAccesEvenementAsso(array $evenement, int $userId, int $idEvent): bool
    {
        if (strtotime($evenement['date_cloture']) < time()) {
            $_SESSION['error'] = "Les inscriptions sont closes pour cet événement.";
            rediriger('/membre/inscription/asso?id=' . $idEvent);
            return false;
        }
        $membre = Membre::getMembreParId($userId);
        if ($evenement['prive'] && !($membre && $membre['Adherent'] == 1)) {
            $_SESSION['error'] = "Cet événement est réservé aux adhérents.";
            rediriger(self::ROUTE_DASHBOARD);
            return false;
        }
        return true;
    }

    private static function extraireAccompagnateurs(int $idEvent): array
    {
        if (empty($_POST['accompagnateurs_data'])) {
            return [];
        }
        $data = json_decode($_POST['accompagnateurs_data'], true);
        if (!is_array($data)) {
            return [];
        }
        foreach ($data as &$acc) {
            $acc['nom'] = trim(htmlspecialchars($acc['nom'] ?? '', ENT_QUOTES, 'UTF-8'));
            $acc['prenom'] = trim(htmlspecialchars($acc['prenom'] ?? '', ENT_QUOTES, 'UTF-8'));
            $acc['email'] = filter_var($acc['email'] ?? '', FILTER_SANITIZE_EMAIL);
            $acc['tarif'] = isset($acc['tarif']) ? max(0, (float)$acc['tarif']) : 0;
            if (empty($acc['nom']) || empty($acc['prenom'])) {
                $_SESSION['errors'] = ["Chaque accompagnateur doit avoir un nom et un prénom."];
                rediriger('/membre/inscription_asso&id=' . $idEvent);
            }
        }
        unset($acc);
        return $data;
    }

    private static function enregistrerInscriptionAsso(int $userId, int $idEvent, int $nbInvites, array $accompagnateursData): void
    {
        if (Participation::getInscriptionEventAsso($userId, $idEvent)) {
            Participation::sauvegarderAccompagnateurs($userId, $idEvent, $accompagnateursData);
            $_SESSION['success'] = 'Votre inscription a été mise à jour avec succès.';
            rediriger(self::ROUTE_INSCRIPTIONS_ASSO);
            return;
        }
        if (Participation::inscrireEvenementAsso($userId, $idEvent, $nbInvites)) {
            Participation::sauvegarderAccompagnateurs($userId, $idEvent, $accompagnateursData);
            $_SESSION['success'] = "Vous avez été inscrit à l'événement avec succès.";
            rediriger(self::ROUTE_INSCRIPTIONS_ASSO);
        } else {
            $_SESSION['error'] = "Vous êtes déjà inscrit à cet événement.";
            rediriger('/membre/inscription/asso?id=' . $idEvent);
        }
    }

    public static function traiterDesinscriptionAsso(): void
    {
        self::verifierMembre();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger(self::ROUTE_DASHBOARD);
            return;
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['error'] = self::CSRF_ERR;
            rediriger(self::ROUTE_DASHBOARD);
            return;
        }

        $idEvent = isset($_POST['id_event']) ? (int)$_POST['id_event'] : 0;
        $userId = $_SESSION['user_id'];

        if (!$idEvent) {
            $_SESSION['error'] = self::ERR_EVENT;
            rediriger(self::ROUTE_DASHBOARD);
            return;
        }

        // Désinscrire de l'événement
        if (Participation::desinscrireEvenementAsso($userId, $idEvent)) {
            $_SESSION['success'] = "Vous avez été désinscrit de l'événement avec succès.";
        } else {
            $_SESSION['error'] = self::ERR_DESINSCRIPTION;
        }

        rediriger(self::ROUTE_INSCRIPTIONS_ASSO);
    }

    // gestion des inscriptions sportives

    public static function afficherMesInscriptionsSport(): void
    {
        self::verifierMembre();

        $userId = $_SESSION['user_id'];
        $mes_inscriptions_sport = Participation::getMesInscriptionsSport($userId);

        // on regroupe par event
        $evenements = [];
        foreach ($mes_inscriptions_sport as $insc) {
            $idEvent = $insc['id_event'];
            if (!isset($evenements[$idEvent])) {
                $evenements[$idEvent] = [
                    'id_event' => $idEvent,
                    'titre' => $insc['titre'],
                    'date_cloture' => $insc['date_cloture'],
                    'adresse' => $insc['adresse'],
                    'ville' => $insc['ville'],
                    'creneaux' => []
                ];
            }
            $evenements[$idEvent]['creneaux'][] = $insc;
        }

        require_once __DIR__ . '/../vues/membre/mes_inscriptions_sport.php';
    }

    public static function traiterDesinscriptionSportComplet(): void
    {
        self::verifierMembre();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger(self::ROUTE_INSCRIPTIONS_SPORT);
            return;
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['error'] = self::CSRF_ERR;
            rediriger(self::ROUTE_INSCRIPTIONS_SPORT);
            return;
        }

        $idEvent = isset($_POST['id_event']) ? (int)$_POST['id_event'] : 0;
        $userId = $_SESSION['user_id'];

        if (!$idEvent) {
            $_SESSION['error'] = self::ERR_EVENT;
            rediriger(self::ROUTE_INSCRIPTIONS_SPORT);
            return;
        }

        // desinscrit de tous les creneaux de cet event
        if (Participation::desinscrireEvenementSportifComplet($userId, $idEvent)) {
            $_SESSION['success'] = "Vous avez été désinscrit de tous les créneaux de cet événement.";
        } else {
            $_SESSION['error'] = self::ERR_DESINSCRIPTION;
        }

        rediriger(self::ROUTE_INSCRIPTIONS_SPORT);
    }

    // gestion des inscriptions associatives

    public static function afficherMesInscriptionsAsso(): void
    {
        self::verifierMembre();

        $userId = $_SESSION['user_id'];
        $mes_inscriptions_asso = Participation::getMesInscriptionsAsso($userId);

        require_once __DIR__ . '/../vues/membre/mes_inscriptions_asso.php';
    }

    public static function traiterModificationAccompagnateurs(): void
    {
        self::verifierMembre();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger(self::ROUTE_INSCRIPTIONS_ASSO);
            return;
        }

        if (!verifierTokenCSRF()) {
            $_SESSION['error'] = self::CSRF_ERR;
            rediriger(self::ROUTE_INSCRIPTIONS_ASSO);
            return;
        }

        $idEvent = isset($_POST['id_event']) ? (int)$_POST['id_event'] : 0;
        $nbInvites = isset($_POST['nb_invites']) ? max(0, (int)$_POST['nb_invites']) : 0;
        $userId = $_SESSION['user_id'];

        if (!$idEvent) {
            $_SESSION['error'] = self::ERR_EVENT;
            rediriger(self::ROUTE_INSCRIPTIONS_ASSO);
            return;
        }

        // modifier le nombre d'accompagnateurs
        if (Participation::modifierAccompagnateurs($userId, $idEvent, $nbInvites)) {
            $_SESSION['success'] = "Le nombre d'accompagnateurs a été modifié avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la modification.";
        }

        rediriger(self::ROUTE_INSCRIPTIONS_ASSO);
    }

    public static function afficherSecurite(): void
    {
        self::verifierMembre();
        $id = $_SESSION['user_id'] ?? 0;

        if ($id) {
            require_once __DIR__ . '/../vues/membre/securite.php';
        } else {
            rediriger(self::ROUTE_CONNEXION);
        }
    }

    public static function traiterChangementMotDePasse(): void
    {
        self::verifierMembre();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger(self::ROUTE_SECURITE);
            return;
        }

        // Vérification CSRF
        if (!verifierTokenCSRF()) {
            $_SESSION['error'] = self::CSRF_ERR;
            rediriger(self::ROUTE_SECURITE);
            return;
        }

        $id = $_SESSION['user_id'];
        $mdpActuel = $_POST['mdp_actuel'] ?? '';
        $nouveauMdp = $_POST['nouveau_mdp'] ?? '';
        $confirmMdp = $_POST['confirm_mdp'] ?? '';

        // Récupérer le membre
        $membre = Membre::getMembreParId($id);
        if (!$membre) {
            $_SESSION['error'] = 'Erreur : membre introuvable.';
            rediriger(self::ROUTE_SECURITE);
            return;
        }

        // Vérifier le mot de passe actuel
        if (!password_verify($mdpActuel, $membre['Mot_de_passe'])) {
            $_SESSION['error'] = 'Le mot de passe actuel est incorrect.';
            rediriger(self::ROUTE_SECURITE);
            return;
        }

        // Vérifier que le nouveau mot de passe n'est pas vide
        if (empty($nouveauMdp)) {
            $_SESSION['error'] = 'Le nouveau mot de passe ne peut pas être vide.';
            rediriger(self::ROUTE_SECURITE);
            return;
        }

        // Vérifier que les deux mots de passe correspondent
        if ($nouveauMdp !== $confirmMdp) {
            $_SESSION['error'] = 'Les deux mots de passe ne correspondent pas.';
            rediriger(self::ROUTE_SECURITE);
            return;
        }

        // Validation du mot de passe (mêmes règles que l'inscription)
        $mdpRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        if (!preg_match($mdpRegex, $nouveauMdp)) {
            $_SESSION['error'] = 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.';
            rediriger(self::ROUTE_SECURITE);
            return;
        }

        // Mettre à jour le mot de passe
        if (Membre::changerMotDePasse($id, $nouveauMdp)) {
            $_SESSION['success'] = 'Votre mot de passe a été modifié avec succès.';
        } else {
            $_SESSION['error'] = 'Erreur lors de la modification du mot de passe.';
        }

        rediriger(self::ROUTE_SECURITE);
    }

    public static function afficherMesEvenementsPasses(): void
    {
        self::verifierMembre();

        $userId = $_SESSION['user_id'] ?? 0;

        if (!$userId) {
            rediriger(self::ROUTE_CONNEXION);
            return;
        }

        // recup les events sportifs ou le membre a ete marque present
        $evenementsPasses = Participation::getMesEvenementsSportifsPasses($userId);

        require_once __DIR__ . '/../vues/membre/mes_evenements_passes.php';
    }

    // calcule le tarif pour une personne selon sa participation aux events sportifs des 12 derniers mois
    public static function calculerTarifAsso(): void
    {
        self::verifierMembre();

        // Verification que la requete vient du meme domaine
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (empty($origin) && empty($referer)) {
            http_response_code(403);
            echo json_encode(['error' => 'Requête non autorisée']);
            exit;
        }
        if (!empty($origin) && parse_url($origin, PHP_URL_HOST) !== $host) {
            http_response_code(403);
            echo json_encode(['error' => 'Requête non autorisée']);
            exit;
        }

        // lit les donnees JSON envoyees
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!isset($data['email']) || empty($data['email'])) {
            header(self::CONTENT_TYPE_JSON);
            echo json_encode(['success' => false, 'message' => 'Email manquant']);
            exit;
        }

        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header(self::CONTENT_TYPE_JSON);
            echo json_encode(['success' => false, 'message' => 'Email invalide']);
            exit;
        }

        // recupere le tarif de l'evenement depuis les donnees JSON
        $tarifEvent = isset($data['tarif_event']) ? (float)$data['tarif_event'] : 0;

        // verifie si l'email a participe a un event sportif avec Presence=1 dans les 12 derniers mois
        $aParticipe = Participation::aParticipeEventSportifRecement($email);

        // calcul du tarif et message explicatif
        if ($aParticipe) {
            $tarif = 0;
            $raison = "Vous avez été bénévole au cours des 12 derniers mois";
        } else {
            $tarif = $tarifEvent;
            $raison = "Aucune participation bénévole dans les 12 derniers mois";
        }

        header(self::CONTENT_TYPE_JSON);
        echo json_encode([
            'success' => true,
            'tarif' => $tarif,
            'aParticipe' => $aParticipe,
            'raison' => $raison
        ]);
        exit;
    }
    // In kastasso/app/controleurs/ControleurMembre.php

// ... (keep existing methods)

public static function afficherContact(): void
{
    // Ne pas exiger de connexion pour cette page
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $titrePage = "Nous contacter";
    require_once __DIR__ . '/../vues/membre/contact.php';
}

public static function traiterContact(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $redirectUrl = isset($_SESSION['user']) ? '/membre/contact' : '/contact';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    if (!verifierTokenCSRF()) {
        $_SESSION['errors'] = ["Erreur de sécurité. Veuillez réessayer."];
        rediriger($redirectUrl);
        return;
    }

    $sujet = trim($_POST['sujet'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $email = trim($_POST['email'] ?? '');

    $errors = self::validerChampContact($sujet, $message, $email);
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = ['sujet' => $sujet, 'message' => $message, 'email' => $email];
        rediriger($redirectUrl);
        return;
    }

    $nomMembre = isset($_SESSION['user'])
        ? htmlspecialchars($_SESSION['user']['prenom'] . ' ' . $_SESSION['user']['nom'])
        : 'Visiteur (non connecté)';

    $emailBody = self::construireEmailContact($nomMembre, htmlspecialchars($email), $sujet, $message);

    try {
        require_once __DIR__ . '/../services/EmailService.php';
        require_once __DIR__ . '/../../config/env.php';
        Env::load();

        $emailDestination = Env::get('CONTACT_EMAIL') ?: 'contact@kastasso.fr';
        $resultat = EmailService::envoyer($emailDestination, "[Kast'Asso Contact] " . $sujet, $emailBody, "Kast'Asso");

        if ($resultat) {
            $_SESSION['success'] = "Votre message a bien été envoyé ! Nous vous répondrons dans les plus brefs délais.";
        } else {
            $_SESSION['errors'] = ["Une erreur est survenue lors de l'envoi. Veuillez réessayer."];
        }
    } catch (Exception $e) {
        error_log("Erreur envoi contact : " . $e->getMessage());
        $_SESSION['errors'] = ["Une erreur est survenue lors de l'envoi du message."];
    }

    unset($_SESSION['form_data']);
    rediriger($redirectUrl);
}

private static function validerChampContact(string $sujet, string $message, string $email): array
{
    $errors = [];
    if (empty($sujet)) {
        $errors[] = "Veuillez sélectionner un sujet.";
    }
    if (empty($message)) {
        $errors[] = "Veuillez entrer votre message.";
    }
    if (empty($email)) {
        $errors[] = "Veuillez entrer votre adresse email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }
    return $errors;
}

private static function construireEmailContact(string $nomMembre, string $emailMembre, string $sujet, string $message): string
{
    return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%); padding: 20px; border-radius: 8px 8px 0 0;'>
                <h2 style='color: white; margin: 0;'>📩 Nouveau message de contact</h2>
            </div>
            <div style='background: #f8f9fa; padding: 25px; border: 1px solid #e9ecef;'>
                <p><strong>👤 Membre :</strong> {$nomMembre}</p>
                <p><strong>📧 Email :</strong> {$emailMembre}</p>
                <p><strong>🏷️ Sujet :</strong> " . htmlspecialchars($sujet) . "</p>
                <hr style='border: none; border-top: 1px solid #dee2e6; margin: 20px 0;'>
                <p><strong>💬 Message :</strong></p>
                <div style='background: white; padding: 15px; border-radius: 5px; border-left: 4px solid #3498db;'>
                    " . nl2br(htmlspecialchars($message)) . "
                </div>
            </div>
            <div style='background: #2c3e50; color: white; padding: 15px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px;'>
                Message envoyé depuis le formulaire de contact Kast'Asso
            </div>
        </div>
    ";
}
}
