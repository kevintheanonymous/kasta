<?php

require_once __DIR__ . '/../models/Evenement.php';

class ControleurEvenement {
    
    // Vérifier que l'utilisateur est admin ou gestionnaire
    private function verifierAcces() {
        if (!isset($_SESSION['user_type']) || 
            ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'gestionnaire')) {
            $_SESSION['erreurs'] = ['Accès refusé'];
            header('Location: index.php?path=/connexion');
            exit;
        }
    }
    
    // Afficher le formulaire événement sportif
    public function afficherCreationSportif() {
        $this->verifierAcces();
        
        $eventModel = new Evenement();
        $categories = $eventModel->getCategories();
        
        require __DIR__ . '/../vues/evenements/creer_sportif.php';
    }
    
    // Créer un événement sportif
    public function traiterCreationSportif() {
        $this->verifierAcces();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?path=/admin_event_sportif');
            exit;
        }
        
        $erreurs = [];
        
        // Validation des champs
        if (empty($_POST['titre'])) $erreurs[] = "Le titre est obligatoire";
        if (empty($_POST['adresse'])) $erreurs[] = "L'adresse est obligatoire";
        if (empty($_POST['lien_maps'])) $erreurs[] = "Le lien Maps est obligatoire";
        if (empty($_POST['id_categorie'])) $erreurs[] = "La catégorie est obligatoire";
        
        // Validation des dates
        if (!empty($_POST['date_visibilite']) && !empty($_POST['date_cloture'])) {
            $dateVisible = new DateTime($_POST['date_visibilite']);
            $dateCloture = new DateTime($_POST['date_cloture']);
            
            if ($dateCloture <= $dateVisible) {
                $erreurs[] = "La date de clôture doit être après la date de visibilité";
            }
        }
        
        if (!empty($erreurs)) {
            $_SESSION['erreurs'] = $erreurs;
            $_SESSION['form_data'] = $_POST;
            header('Location: index.php?path=/admin_event_sportif');
            exit;
        }
        
        // Upload image
        $url_image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif'];
            
            if (in_array($ext, $allowed)) {
                $newFileName = uniqid('event_', true) . '.' . $ext;
                $destPath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $destPath)) {
                    $url_image = 'uploads/' . $newFileName;
                }
            }
        }
        
        $data = [
            'titre' => $_POST['titre'],
            'descriptif' => $_POST['descriptif'] ?? '',
            'url_image' => $url_image,
            'adresse' => $_POST['adresse'],
            'lien_maps' => $_POST['lien_maps'],
            'date_visibilite' => $_POST['date_visibilite'] ?? date('Y-m-d H:i:s'),
            'date_cloture' => $_POST['date_cloture'] ?? null,
            'id_categorie' => $_POST['id_categorie']
        ];
        
        $eventModel = new Evenement();
        $result = $eventModel->creerEventSportif($data);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            header('Location: index.php?path=/admin/tableau_de_bord');
        } else {
            $_SESSION['erreurs'] = [$result['message']];
            $_SESSION['form_data'] = $_POST;
            header('Location: index.php?path=/admin_event_sportif');
        }
        exit;
    }
    
    // Afficher le formulaire événement associatif
    public function afficherCreationAssociatif() {
        $this->verifierAcces();
        
        require __DIR__ . '/../vues/evenements/creer_associatif.php';
    }
    
    // Créer un événement associatif
    public function traiterCreationAssociatif() {
        $this->verifierAcces();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?path=/admin_event_associatif');
            exit;
        }
        
        $erreurs = [];
        
        // Validation
        if (empty($_POST['titre'])) $erreurs[] = "Le titre est obligatoire";
        if (empty($_POST['adresse'])) $erreurs[] = "L'adresse est obligatoire";
        if (empty($_POST['date_evenement'])) $erreurs[] = "La date de l'événement est obligatoire";
        
        // Validation des dates
        if (!empty($_POST['date_visibilite']) && !empty($_POST['date_cloture'])) {
            $dateVisible = new DateTime($_POST['date_visibilite']);
            $dateCloture = new DateTime($_POST['date_cloture']);
            
            if ($dateCloture <= $dateVisible) {
                $erreurs[] = "La date de clôture doit être après la date de visibilité";
            }
        }
        
        // Vérifier que la date de l'événement est dans le futur
        if (!empty($_POST['date_evenement'])) {
            $dateEvent = new DateTime($_POST['date_evenement']);
            $aujourdhui = new DateTime();
            
            if ($dateEvent < $aujourdhui) {
                $erreurs[] = "La date de l'événement doit être dans le futur";
            }
        }
        
        if (!empty($erreurs)) {
            $_SESSION['erreurs'] = $erreurs;
            $_SESSION['form_data'] = $_POST;
            header('Location: index.php?path=/admin_event_associatif');
            exit;
        }
        
        // Upload image
        $url_image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif'];
            
            if (in_array($ext, $allowed)) {
                $newFileName = uniqid('event_', true) . '.' . $ext;
                $destPath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $destPath)) {
                    $url_image = 'uploads/' . $newFileName;
                }
            }
        }
        
        $data = [
            'titre' => $_POST['titre'],
            'descriptif' => $_POST['descriptif'] ?? '',
            'url_image' => $url_image,
            'adresse' => $_POST['adresse'],
            'lien_maps' => $_POST['lien_maps'] ?? '',
            'date_visibilite' => $_POST['date_visibilite'] ?? date('Y-m-d H:i:s'),
            'date_cloture' => $_POST['date_cloture'] ?? null,
            'tarif' => $_POST['tarif'] ?? 9.99,
            'url_helloasso' => $_POST['url_helloasso'] ?? '',
            'prive' => isset($_POST['prive']) ? 1 : 0,
            'date_evenement' => $_POST['date_evenement']
        ];
        
        $eventModel = new Evenement();
        $result = $eventModel->creerEventAssociatif($data);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            header('Location: index.php?path=/admin/tableau_de_bord');
        } else {
            $_SESSION['erreurs'] = [$result['message']];
            $_SESSION['form_data'] = $_POST;
            header('Location: index.php?path=/admin_event_associatif');
        }
        exit;
    }
}
?>
