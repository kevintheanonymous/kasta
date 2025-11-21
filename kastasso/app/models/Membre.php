<?php
require_once __DIR__ . '/BaseDeDonnees.php';

class Membre
{
    public static function ajouterMembre(array $data): array
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "INSERT INTO membre (
    prenom, nom, mail, mdp, telephone, url_photo,
    taille_teeshirt, taille_pull, adherent, url_adhesion,
    statut, date_statut, message_statut, commentaire_alim, gestionnaire_o_n_
) VALUES (
    :prenom, :nom, :mail, :mdp, :telephone, :url_photo,
    :taille_teeshirt, :taille_pull, :adherent, :url_adhesion,
    :statut, :date_statut, :message_statut, :commentaire_alim, 0
)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
    ':prenom'           => $data['prenom'],
    ':nom'              => $data['nom'],
    ':mail'             => $data['mail'],
    ':mdp'              => $data['mdp'],
    ':telephone'        => $data['telephone'],
    ':url_photo'        => $data['url_photo'] ?? null,
    ':taille_teeshirt'  => $data['taille_teeshirt'] ?? null,
    ':taille_pull'      => $data['taille_pull'] ?? null,
    ':adherent'         => (int) ($data['adherent'] ?? 0),
    ':url_adhesion'     => $data['url_adhesion'] ?? '',
    ':statut'           => 'ATTENTE',
    ':date_statut'      => date('Y-m-d H:i:s'),
    ':message_statut'   => 'Votre demamde a bien etait enregistrer',
    ':commentaire_alim' => $data['commentaire_alim'] ?? '',
]);


            return ['success' => true, 'id' => (int) $pdo->lastInsertId()];
        }catch (Throwable $e) {
    error_log('Membre::ajouterMembre error: ' . $e->getMessage());
    return ['success' => false, 'message' => $e->getMessage()]; 
}

    }

    public static function emailExiste(string $mail): bool
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "SELECT COUNT(*) FROM membre WHERE mail = :mail";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':mail' => $mail]);
            return (bool) $stmt->fetchColumn();
        } catch (Throwable $e) {
            error_log('Membre::emailExiste error: ' . $e->getMessage());
            return true;
        }
    }

public static function connexion(string $mail, string $mdp): array
{
    try {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "SELECT id_membre,
                       prenom,
                       nom,
                       mail,
                       mdp,
                       gestionnaire_o_n_ AS gestionnaire,
                       statut
                FROM membre
                WHERE mail = :mail
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':mail' => $mail]);
        $row = $stmt->fetch();

        if (!$row || $row['mdp'] !== $mdp) {
            return ['success' => false, 'message' => 'Identifiants incorrects'];
        }

        return [
            'success' => true,
            'data' => [
                'id_membre'     => (int)$row['id_membre'],
                'prenom'        => $row['prenom'],
                'nom'           => $row['nom'],
                'mail'          => $row['mail'],
                'gestionnaire'  => (bool)$row['gestionnaire'],
                'statut_compte' => $row['statut'] ?? 'ATTENTE',
            ],
        ];
    } catch (Throwable $e) {
        error_log('Membre::connexion error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur de connexion'];
    }
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
                    SET statut = 'REFUS',
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
        error_log("Email à {$destinataire} sujet '{$sujet}': {$message}");
        return true;
    }
    public static function mettreGestionnaire(int $id, int $valeur): bool
{
    try {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "UPDATE membre SET gestionnaire_o_n_ = :valeur WHERE id_membre = :id";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([':valeur' => $valeur, ':id' => $id]);
        echo "<pre>mettreGestionnaire: id=$id, value=$valeur, success=$success, rowCount=" . $stmt->rowCount() . "</pre>";
        // exit(); // Uncomment to see output
        return $success && $stmt->rowCount() === 1;
    } catch (Throwable $e) {
        echo "<pre>Exception: " . htmlspecialchars($e->getMessage()) . "</pre>";
        return false;
    }
}

}
