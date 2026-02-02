<?php
/**
 * Script CRON : Envoi des rappels 24h avant chaque créneau
 * 
 * Ce script doit être exécuté quotidiennement (par exemple à 9h00 du matin)
 * via une tâche planifiée (cron Linux ou Tâche Planifiée Windows)
 * 
 * Exemple crontab Linux (exécution tous les jours à 9h00) :
 * 0 9 * * * php /chemin/vers/kastasso/scripts/envoi_rappels_24h.php
 * 
 * Exemple PowerShell Windows (à ajouter au Planificateur de tâches) :
 * php.exe "C:\xampp\htdocs\sae3.01\kastasso\scripts\envoi_rappels_24h.php"
 */

// Chargement de l'environnement
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../app/models/BaseDeDonnees.php';
require_once __DIR__ . '/../app/models/Creneau.php';
require_once __DIR__ . '/../app/services/EmailService.php';

// Log de démarrage
$dateDebut = date('Y-m-d H:i:s');
echo "[{$dateDebut}] Démarrage du script d'envoi de rappels 24h\n";

try {
    // Connexion à la base de données
    $pdo = BaseDeDonnees::getConnexion();

    // Calcul de la date cible : demain (J+1)
    $dateDemain = date('Y-m-d', strtotime('+1 day'));
    $dateApresdemain = date('Y-m-d', strtotime('+2 days'));

    echo "Recherche des créneaux pour le {$dateDemain}...\n";

    // Récupération des créneaux prévus demain (entre minuit demain et minuit après-demain)
    $sql = "
        SELECT DISTINCT c.Id_creneau, c.Type, c.Date_creneau, c.Heure_Debut, c.Heure_Fin,
               e.Titre, COUNT(ab.Id_Membre) as nb_inscrits
        FROM creneau_event c
        INNER JOIN event_sportif e ON c.Id_Event_sportif = e.Id_Event_sportif
        LEFT JOIN aide_benevole ab ON c.Id_creneau = ab.Id_creneau
        WHERE c.Date_creneau >= ? AND c.Date_creneau < ?
        GROUP BY c.Id_creneau
        HAVING nb_inscrits > 0
        ORDER BY c.Date_creneau, c.Heure_Debut
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$dateDemain, $dateApresdemain]);
    $creneaux = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $nbCreneaux = count($creneaux);
    echo "[OK] {$nbCreneaux} créneau(x) trouvé(s) avec des inscrits pour demain\n\n";

    if ($nbCreneaux === 0) {
        echo "Aucun rappel à envoyer aujourd'hui.\n";
        exit(0);
    }

    // Statistiques d'envoi
    $totalEmailsEnvoyes = 0;
    $creneauxTraites = 0;

    // Traitement de chaque créneau
    foreach ($creneaux as $creneau) {
        $idCreneau = $creneau['Id_creneau'];
        $titre = $creneau['Titre'];
        $type = $creneau['Type'];
        $date = date('d/m/Y', strtotime($creneau['Date_creneau']));
        $heureDebut = date('H:i', strtotime($creneau['Heure_Debut']));
        $heureFin = date('H:i', strtotime($creneau['Heure_Fin']));
        $nbInscrits = $creneau['nb_inscrits'];

        echo "---------------------------------------------------\n";
        echo "Créneau #{$idCreneau} : {$titre}\n";
        echo "Type : {$type}\n";
        echo "Date : {$date} de {$heureDebut} à {$heureFin}\n";
        echo "Inscrits : {$nbInscrits}\n";
        echo "Envoi des rappels...\n";

        // Envoi des emails de rappel
        $nbEnvoyes = EmailService::envoyerRappelCreneau($idCreneau);

        if ($nbEnvoyes > 0) {
            echo "[OK] {$nbEnvoyes} email(s) envoyé(s) avec succès\n";
            $totalEmailsEnvoyes += $nbEnvoyes;
            $creneauxTraites++;
        } else {
            echo "[ERREUR] Aucun email envoyé (erreur ou pas d'inscrits)\n";
        }
    }

    // Résumé final
    echo "\n===================================================\n";
    echo "RÉSUMÉ DE L'EXÉCUTION\n";
    echo "===================================================\n";
    echo "Date d'exécution : {$dateDebut}\n";
    echo "Créneaux ciblés (demain) : {$nbCreneaux}\n";
    echo "Créneaux traités avec succès : {$creneauxTraites}\n";
    echo "Total emails envoyés : {$totalEmailsEnvoyes}\n";
    echo "===================================================\n";

    // Log dans un fichier (optionnel)
    $logFile = __DIR__ . '/logs/rappels_cron.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logMessage = sprintf(
        "[%s] Rappels envoyés : %d créneaux traités, %d emails envoyés\n",
        $dateDebut,
        $creneauxTraites,
        $totalEmailsEnvoyes
    );
    file_put_contents($logFile, $logMessage, FILE_APPEND);

    echo "\n[OK] Script terminé avec succès\n";
    exit(0);

} catch (PDOException $e) {
    $erreur = "[ERREUR BDD] {$e->getMessage()}\n";
    echo $erreur;
    error_log($erreur);
    exit(1);

} catch (Throwable $e) {
    $erreur = "[ERREUR SCRIPT] {$e->getMessage()}\n{$e->getTraceAsString()}\n";
    echo $erreur;
    error_log($erreur);
    exit(1);
}
