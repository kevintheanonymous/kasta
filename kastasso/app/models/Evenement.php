<?php
// Gestion des événements sportifs et associatifs

require_once __DIR__ . '/BaseDeDonnees.php';

class Evenement {
    
    private $conn;
    
    public function __construct() {
        $database = new BaseDeDonnees();
        $this->conn = $database->getConnexion();
    }
    
    // Créer un événement sportif
    public function creerEventSportif($data) {
        try {
            $query = "INSERT INTO Event_sportif (
                        Titre, Descriptif, Url_Image, Adresse, Lien_Maps,
                        Date_Visibilite, Date_Cloture, Id_Categorie_evenement
                      ) VALUES (
                        :titre, :descriptif, :image, :adresse, :maps,
                        :date_visible, :date_cloture, :categorie
                      )";
            
            $stmt = $this->conn->prepare($query);
            
            $result = $stmt->execute([
                ':titre' => htmlspecialchars($data['titre']),
                ':descriptif' => htmlspecialchars($data['descriptif'] ?? ''),
                ':image' => $data['url_image'],
                ':adresse' => htmlspecialchars($data['adresse']),
                ':maps' => $data['lien_maps'],
                ':date_visible' => $data['date_visibilite'],
                ':date_cloture' => $data['date_cloture'],
                ':categorie' => $data['id_categorie']
            ]);
            
            if (!$result) {
                return ['success' => false, 'message' => 'Erreur SQL : ' . $stmt->errorInfo()[2]];
            }
            
            return [
                'success' => true,
                'message' => 'Événement sportif créé avec succès',
                'id' => $this->conn->lastInsertId()
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }
    
    // Créer un événement associatif
    public function creerEventAssociatif($data) {
        try {
            $query = "INSERT INTO Event_Associatif (
                        Titre, Descriptif, Url_Image, Adresse, Lien_Maps,
                        Date_Visibilite, Date_Cloture, Tarif, Url_HelloAsso,
                        Prive, Date_Evenement
                      ) VALUES (
                        :titre, :descriptif, :image, :adresse, :maps,
                        :date_visible, :date_cloture, :tarif, :helloasso,
                        :prive, :date_event
                      )";
            
            $stmt = $this->conn->prepare($query);
            
            $result = $stmt->execute([
                ':titre' => htmlspecialchars($data['titre']),
                ':descriptif' => htmlspecialchars($data['descriptif'] ?? ''),
                ':image' => $data['url_image'],
                ':adresse' => htmlspecialchars($data['adresse']),
                ':maps' => $data['lien_maps'] ?? '',
                ':date_visible' => $data['date_visibilite'],
                ':date_cloture' => $data['date_cloture'],
                ':tarif' => $data['tarif'] ?? 9.99,
                ':helloasso' => $data['url_helloasso'] ?? '',
                ':prive' => $data['prive'] ?? 0,
                ':date_event' => $data['date_evenement']
            ]);
            
            if (!$result) {
                return ['success' => false, 'message' => 'Erreur SQL : ' . $stmt->errorInfo()[2]];
            }
            
            return [
                'success' => true,
                'message' => 'Événement associatif créé avec succès',
                'id' => $this->conn->lastInsertId()
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }
    
    // Récupérer les catégories
    public function getCategories() {
        try {
            $query = "SELECT * FROM Categorie_evenement ORDER BY libelle";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Récupérer tous les événements visibles (pour l'accueil)
    public function getEvenementsVisibles() {
        try {
            $evenements = [];
            
            // Événements sportifs
            $querySportif = "SELECT es.Id_Event_sportif as id, es.Titre, es.Descriptif, 
                                   es.Url_Image, es.Adresse, es.Date_Visibilite, 
                                   es.Date_Cloture, ce.libelle as categorie, 'sportif' as type
                            FROM Event_sportif es
                            LEFT JOIN Categorie_evenement ce ON es.Id_Categorie_evenement = ce.Id_Categorie_evenement
                            WHERE es.Date_Visibilite <= NOW()
                            ORDER BY es.Date_Visibilite DESC";
            
            $stmt = $this->conn->prepare($querySportif);
            $stmt->execute();
            $evenements = array_merge($evenements, $stmt->fetchAll());
            
            // Événements associatifs
            $queryAssociatif = "SELECT Id_Event_Associatif as id, Titre, Descriptif, 
                                      Url_Image, Adresse, Date_Visibilite, 
                                      Date_Cloture, Date_Evenement, Tarif, 
                                      Prive, 'associatif' as type
                               FROM Event_Associatif
                               WHERE Date_Visibilite <= NOW()
                               ORDER BY Date_Visibilite DESC";
            
            $stmt = $this->conn->prepare($queryAssociatif);
            $stmt->execute();
            $evenements = array_merge($evenements, $stmt->fetchAll());
            
            return $evenements;
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Récupérer TOUS les événements (pour l'admin)
    public function getTousLesEvenements() {
        try {
            $evenements = [];
            
            // Événements sportifs
            $querySportif = "SELECT es.Id_Event_sportif as id, es.Titre, es.Descriptif, 
                                   es.Url_Image, es.Adresse, es.Date_Visibilite, 
                                   es.Date_Cloture, ce.libelle as categorie, 'sportif' as type
                            FROM Event_sportif es
                            LEFT JOIN Categorie_evenement ce ON es.Id_Categorie_evenement = ce.Id_Categorie_evenement
                            ORDER BY es.Date_Visibilite DESC";
            
            $stmt = $this->conn->prepare($querySportif);
            $stmt->execute();
            $evenements = array_merge($evenements, $stmt->fetchAll());
            
            // Événements associatifs
            $queryAssociatif = "SELECT Id_Event_Associatif as id, Titre, Descriptif, 
                                      Url_Image, Adresse, Date_Visibilite, 
                                      Date_Cloture, Date_Evenement, Tarif, 
                                      Prive, 'associatif' as type
                               FROM Event_Associatif
                               ORDER BY Date_Visibilite DESC";
            
            $stmt = $this->conn->prepare($queryAssociatif);
            $stmt->execute();
            $evenements = array_merge($evenements, $stmt->fetchAll());
            
            return $evenements;
            
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>
