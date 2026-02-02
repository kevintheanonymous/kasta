<?php
require_once __DIR__ . '/../app/models/BaseDeDonnees.php';

try {
    $pdo = BaseDeDonnees::getConnexion();
    
    // Supprimer la contrainte de clé étrangère
    $pdo->exec('ALTER TABLE aide_benevole DROP FOREIGN KEY FK_aide_benevole_poste');
    echo "Contrainte FK_aide_benevole_poste supprimée\n";
    
    // Modifier le type de colonne
    $pdo->exec('ALTER TABLE aide_benevole MODIFY COLUMN Preference_Poste VARCHAR(255) NULL');
    echo "Colonne Preference_Poste modifiée avec succès (INT -> VARCHAR(255))\n";
    
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
