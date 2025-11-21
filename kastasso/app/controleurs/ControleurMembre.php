<?php

class ControleurMembre
{
    private static function ensurerSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function afficherTableauDeBord(): void
    {
        self::ensurerSession();
        $userName = $_SESSION['user_name'] ?? 'Membre';
        require __DIR__ . '/../vues/membre/tableau_de_bord.php';
    }

    public static function afficherEvenements(): void
    {
        self::ensurerSession();
        echo 'Liste des événements (à implémenter).';
    }

    public static function afficherProfil(): void
    {
        self::ensurerSession();
        echo 'Profil membre (à implémenter).';
    }

    public static function getMembresEnAttente(): array
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "SELECT *
                    FROM membre
                    WHERE statut = 'ATTENTE'
                    ORDER BY date_statut DESC";
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll() ?: [];
        } catch (Throwable $e) {
            error_log('Membre::getMembresEnAttente error: ' . $e->getMessage());
            return [];
        }
    }

    public static function getMembreParId(int $id): ?array
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "SELECT *
                    FROM membre
                    WHERE id_membre = :id
                    LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (Throwable $e) {
            error_log('Membre::getMembreParId error: ' . $e->getMessage());
            return null;
        }
    }

    public static function validerMembre(int $id): bool
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "UPDATE membre
                    SET statut = 'VALIDE',
                        date_statut = NOW(),
                        message_statut = 'Compte validé'
                    WHERE id_membre = :id";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([':id' => $id]) && $stmt->rowCount() === 1;
        } catch (Throwable $e) {
            error_log('Membre::validerMembre error: ' . $e->getMessage());
            return false;
        }
    }

    public static function refuserMembre(int $id, string $motif): bool
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "UPDATE membre
                    SET statut = 'REFUSE',
                        date_statut = NOW(),
                        message_statut = :motif
                    WHERE id_membre = :id";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                ':motif' => $motif,
                ':id'    => $id,
            ]) && $stmt->rowCount() === 1;
        } catch (Throwable $e) {
            error_log('Membre::refuserMembre error: ' . $e->getMessage());
            return false;
        }
    }

    public static function envoyerEmail(string $destinataire, string $sujet, string $message): bool
    {
        // Placeholder; integrate real mailer later.
        error_log("Email à {$destinataire} sujet '{$sujet}': {$message}");
        return true;
    }
}
