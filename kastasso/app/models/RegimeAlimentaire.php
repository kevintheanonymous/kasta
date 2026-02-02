<?php
/**
 * Modèle RegimeAlimentaire
 * Gère les opérations CRUD sur les régimes alimentaires
 */

require_once __DIR__ . '/BaseDeDonnees.php';

class RegimeAlimentaire
{
    /**
     * Récupère tous les régimes alimentaires triés par nom
     */
    public static function tous(): array
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "SELECT id, nom, date_creation FROM regimes_alimentaires ORDER BY nom ASC";
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('RegimeAlimentaire::tous error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Crée un nouveau régime alimentaire
     */
    public static function creer(string $nom): bool
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "INSERT INTO regimes_alimentaires (nom) VALUES (:nom)";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([':nom' => trim($nom)]);
        } catch (Throwable $e) {
            error_log('RegimeAlimentaire::creer error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Compte le nombre de membres par régime alimentaire
     */
    public static function compterParRegime(): array
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "SELECT
                        ra.id,
                        ra.nom,
                        ra.date_creation,
                        COUNT(m.Id_Membre) as nb_membres
                    FROM regimes_alimentaires ra
                    LEFT JOIN membre m ON ra.id = m.regime_alimentaire_id
                    GROUP BY ra.id, ra.nom, ra.date_creation
                    ORDER BY ra.nom ASC";
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('RegimeAlimentaire::compterParRegime error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Vérifie si un régime existe déjà par son nom
     */
    public static function existeParNom(string $nom): bool
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "SELECT COUNT(*) FROM regimes_alimentaires WHERE LOWER(nom) = LOWER(:nom)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':nom' => trim($nom)]);
            return $stmt->fetchColumn() > 0;
        } catch (Throwable $e) {
            error_log('RegimeAlimentaire::existeParNom error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Trouve un régime par son ID
     */
    public static function trouverParId(int $id): ?array
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "SELECT id, nom, date_creation FROM regimes_alimentaires WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (Throwable $e) {
            error_log('RegimeAlimentaire::trouverParId error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Compte le nombre de membres utilisant un régime
     */
    public static function compterMembres(int $id): int
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "SELECT COUNT(*) FROM membre WHERE regime_alimentaire_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return (int) $stmt->fetchColumn();
        } catch (Throwable $e) {
            error_log('RegimeAlimentaire::compterMembres error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Modifie le nom d'un régime
     */
    public static function modifier(int $id, string $nouveauNom): bool
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "UPDATE regimes_alimentaires SET nom = :nom WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([':nom' => trim($nouveauNom), ':id' => $id]);
        } catch (Throwable $e) {
            error_log('RegimeAlimentaire::modifier error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un régime
     */
    public static function supprimer(int $id): bool
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "DELETE FROM regimes_alimentaires WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (Throwable $e) {
            error_log('RegimeAlimentaire::supprimer error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si un régime existe par nom sauf pour un ID donné (pour modification)
     */
    public static function existeParNomSaufId(string $nom, int $id): bool
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "SELECT COUNT(*) FROM regimes_alimentaires WHERE LOWER(nom) = LOWER(:nom) AND id != :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':nom' => trim($nom), ':id' => $id]);
            return $stmt->fetchColumn() > 0;
        } catch (Throwable $e) {
            error_log('RegimeAlimentaire::existeParNomSaufId error: ' . $e->getMessage());
            return false;
        }
    }
}
