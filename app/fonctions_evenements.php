<?php
// fonctions pour gerer les evenements
// c'est utilisé par le controleur evenement et le controleur gestionaire
// j'ai mis tout en commun pour pas dupliquer le code
require_once __DIR__ . '/fonctions_commun.php';
require_once __DIR__ . '/models/EvenementSport.php';
require_once __DIR__ . '/models/EvenementAsso.php';
require_once __DIR__ . '/models/Categorie.php';

// recupere les evenements selon le type (sport ou asso)
function getEvenementsByType(string $type): array
{
    if ($type === 'sport') {
        return EvenementSport::findAll();
    }
    return EvenementAsso::findAll(true);
}

// recupere un evenement par son id et son type
// retourne null si on le trouve pas
function getEvenementById(int $id, string $type): ?array
{
    if ($type === 'sport') {
        return EvenementSport::findById($id) ?: null;
    }
    return EvenementAsso::findById($id) ?: null;
}

// recupere les donnees du formulaire d'evenement
// les champs communs a tout les evenements
function extraireDonneesEvenementBase(): array
{
    return [
        'titre' => trim($_POST['titre'] ?? ''),
        'descriptif' => trim($_POST['description'] ?? ''),
        'adresse' => trim($_POST['adresse'] ?? ''),
        'code_postal' => preg_replace('/[^0-9]/', '', $_POST['code_postal'] ?? ''),  // Que des chiffres
        'ville' => trim($_POST['ville'] ?? ''),
        'lieu_maps' => trim($_POST['lieu_maps'] ?? ''),
        'date_visible' => trim($_POST['date_visible'] ?? ''),
        'date_cloture' => trim($_POST['date_cloture'] ?? '')
    ];
}

// verifie que les dates sont coherentes
// genre la date de visibilité doit etre avant la cloture
function validerDatesEvenement(array $data): array
{
    // la date visible doit etre avant ou egale a la date de cloture
    if ($data['date_visible'] > $data['date_cloture']) {
        return [
            'valid' => false,
            'error' => "La date de visibilité doit être antérieure ou égale à la date de clôture."
        ];
    }
    
    return ['valid' => true, 'error' => null];
}

// pareil mais pour les evenements associatifs
// y'a des regles en plus genre la cloture avant l'event
function validerDatesEvenementAsso(array $data): array
{
    // d'abord on fait la validation de base
    $validationBase = validerDatesEvenement($data);
    if (!$validationBase['valid']) {
        return $validationBase;
    }
    
    // la cloture doit etre avant l'evenement sinon ca sert a rien
    if (isset($data['date_event_asso']) && $data['date_cloture'] > $data['date_event_asso']) {
        return [
            'valid' => false,
            'error' => "La date de clôture doit être antérieure ou égale à la date de l'événement."
        ];
    }
    
    // si le tarif est > 0, le lien HelloAsso est obligatoire
    $tarif = isset($data['tarif']) ? (float)$data['tarif'] : 0;
    $urlHelloasso = trim($data['url_helloasso'] ?? '');
    if ($tarif > 0 && empty($urlHelloasso)) {
        return [
            'valid' => false,
            'error' => "Le lien HelloAsso est obligatoire lorsque le tarif est supérieur à 0€."
        ];
    }
    
    return ['valid' => true, 'error' => null];
}

// prepare les donnees pour un event sportif
// on rajoute juste la categorie
function preparerDonneesEvenementSport(array $data): array
{
    $data['id_cat_event'] = $_POST['id_categorie'] ?? null;
    return $data;
}

// prepare les donnees pour un event asso
// y'a plus de trucs a gerer (tarif, helloasso, etc)
function preparerDonneesEvenementAsso(array $data): array
{
    $dateEventAsso = $_POST['date_event_asso'] ?? '';
    // faut convertir le format du input datetime-local en mysql
    $data['date_event_asso'] = str_replace('T', ' ', $dateEventAsso) . ':00';
    $data['tarif'] = $_POST['tarif'] ?? 0;
    $data['url_helloasso'] = $_POST['url_helloasso'] ?? '';
    $data['prive'] = isset($_POST['prive']) ? 1 : 0;
    return $data;
}

// cree un evenement selon le type
// appelle le bon model
function creerEvenement(string $type, array $data): bool
{
    if ($type === 'sport') {
        return EvenementSport::create($data);
    }
    return EvenementAsso::create($data);
}

// modifie un evenement existant
function mettreAJourEvenement(int $id, string $type, array $data): bool
{
    if ($type === 'sport') {
        return EvenementSport::update($id, $data);
    }
    return EvenementAsso::update($id, $data);
}

// supprime un evenement (sport ou asso)
function supprimerEvenement(int $id, string $type): bool
{
    if ($type === 'sport') {
        return EvenementSport::delete($id);
    }
    return EvenementAsso::delete($id);
}

// nettoie et restructure le tableau de créneaux venant du POST
function normaliserCreneauxDepuisPost(array $input): array
{
    $result = [];
    foreach ($input as $creneau) {
        $result[] = [
            'type' => $creneau['type'] ?? '',
            'commentaire' => $creneau['commentaire'] ?? '',
            'date_creneau' => $creneau['date_creneau'] ?? '',
            'heure_debut' => $creneau['heure_debut'] ?? '',
            'heure_fin' => $creneau['heure_fin'] ?? '',
            'postes' => $creneau['postes'] ?? []
        ];
    }
    return $result;
}

// valide les créneaux côté serveur
function validerCreneaux(array $creneaux, string $dateCloture): array
{
    $errors = [];

    if (empty($creneaux)) {
        $errors[] = "Ajoutez au moins un créneau.";
        return $errors;
    }

    $cloture = new DateTime($dateCloture);

    foreach ($creneaux as $index => $c) {
        $prefix = "Créneau " . ($index + 1) . " : ";

        if (!$c['date_creneau'] || !$c['heure_debut'] || !$c['heure_fin'] || !$c['type']) {
            $errors[] = $prefix . "tous les champs sont obligatoires (type, date, heures).";
            continue;
        }

        try {
            $debut = new DateTime($c['date_creneau'] . ' ' . $c['heure_debut']);
            $fin = new DateTime($c['date_creneau'] . ' ' . $c['heure_fin']);
        } catch (Throwable $e) {
            $errors[] = $prefix . "format de date/heure invalide.";
            continue;
        }

        if ($debut >= $fin) {
            $errors[] = $prefix . "l'heure de début doit être avant l'heure de fin.";
        }

        if ($debut < $cloture) {
            $errors[] = $prefix . "le début doit être après la date de clôture des inscriptions (" . $cloture->format('Y-m-d H:i:s') . ").";
        }
    }

    return $errors;
}

// gere toute la creation d'un evenement
// validation + insertion en bdd
// ca evite de repeter le code dans chaque controleur
function traiterCreationEvenement(string $redirectUrlBase): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    
    // check csrf
    validerCSRFOuRediriger($redirectUrlBase);
    
    $type = $_POST['type'] ?? 'sport';
    $data = extraireDonneesEvenementBase();
    
    // URL de création pour rediriger en cas d'erreur (conserver les données)
    $createUrl = $redirectUrlBase . '/create&type=' . $type;
    
    // Validation des dates de base
    $validation = validerDatesEvenement($data);
    if (!$validation['valid']) {
        $_SESSION['errors'] = [$validation['error']];
        $_SESSION['form_data'] = $_POST; // Conserver les données du formulaire
        rediriger($createUrl);
    }
    
    // Préparation des données selon le type
    if ($type === 'sport') {
        $data = preparerDonneesEvenementSport($data);
    } else {
        $data = preparerDonneesEvenementAsso($data);
        
        // Validation spécifique asso
        $validationAsso = validerDatesEvenementAsso($data);
        if (!$validationAsso['valid']) {
            $_SESSION['errors'] = [$validationAsso['error']];
            $_SESSION['form_data'] = $_POST; // Conserver les données du formulaire
            rediriger($createUrl);
        }
    }
    
    // on cree l'event
    if (creerEvenement($type, $data)) {
        $_SESSION['success'] = "Événement créé avec succès";
    } else {
        $_SESSION['errors'] = ["Erreur lors de la création de l'événement"];
        $_SESSION['form_data'] = $_POST;
        rediriger($createUrl);
    }
    
    rediriger($redirectUrlBase . '&type=' . $type);
}

// pareil mais pour la modification
// meme principe que la creation mais avec un update
function traiterMiseAJourEvenement(string $redirectUrlBase): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    
    // check csrf
    validerCSRFOuRediriger($redirectUrlBase);
    
    $type = $_POST['type'] ?? 'sport';
    $id = (int)($_POST['id_event'] ?? 0);
    $data = extraireDonneesEvenementBase();
    
    // Validation des dates de base
    $validation = validerDatesEvenement($data);
    if (!$validation['valid']) {
        $_SESSION['errors'] = [$validation['error']];
        rediriger($redirectUrlBase . '&type=' . $type);
    }
    
    // Préparation des données selon le type
    if ($type === 'sport') {
        $data = preparerDonneesEvenementSport($data);
    } else {
        $data = preparerDonneesEvenementAsso($data);
        
        // Validation spécifique asso
        $validationAsso = validerDatesEvenementAsso($data);
        if (!$validationAsso['valid']) {
            $_SESSION['errors'] = [$validationAsso['error']];
            rediriger($redirectUrlBase . '&type=' . $type);
        }
    }
    
    // on update dans la bdd
    if (mettreAJourEvenement($id, $type, $data)) {
        $_SESSION['success'] = "Événement modifié avec succès";
        
        // Notification des inscrits de la modification
        require_once __DIR__ . '/services/EmailService.php';
        $modifications = ["Les informations de l'événement ont été mises à jour"];
        $nbEmailsEnvoyes = EmailService::notifierModificationEvent($id, $type, $modifications);
        
        if ($nbEmailsEnvoyes > 0) {
            $_SESSION['success'] .= " - {$nbEmailsEnvoyes} email(s) de notification envoyé(s)";
        }
    } else {
        $_SESSION['errors'] = ["Erreur lors de la modification de l'événement"];
    }
    
    rediriger($redirectUrlBase . '&type=' . $type);
}

// gere la suppression d'un evenement
// on verifie le csrf et on delete
function traiterSuppressionEvenement(string $redirectUrlBase): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    
    // check csrf
    validerCSRFOuRediriger($redirectUrlBase);
    
    $type = $_POST['type'] ?? 'sport';
    $id = (int)($_POST['id_event'] ?? 0);
    $raison = $_POST['raison_suppression'] ?? '';
    
    // Notification des inscrits avant suppression
    require_once __DIR__ . '/services/EmailService.php';
    $nbEmailsEnvoyes = EmailService::notifierAnnulationEvent($id, $type, $raison);
    
    if (supprimerEvenement($id, $type)) {
        $_SESSION['success'] = "Événement supprimé avec succès";
        
        if ($nbEmailsEnvoyes > 0) {
            $_SESSION['success'] .= " - {$nbEmailsEnvoyes} email(s) d'annulation envoyé(s)";
        }
    } else {
        $_SESSION['errors'] = ["Erreur lors de la suppression de l'événement"];
    }
    
    rediriger($redirectUrlBase . '&type=' . $type);
}
