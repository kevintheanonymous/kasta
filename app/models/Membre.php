<?php
require_once __DIR__ . '/BaseDeDonnees.php';

class Membre
{
    public static function ajouterMembre(array $data): array
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "INSERT INTO membre (
    Prenom, Nom, Sexe, Mail, Mot_de_passe, Telephone, Url_Photo_Profil,
    Taille_Teeshirt, Taille_Pull, Adherent, Url_Adhesion,
    Statut_compte, Date_statut_compte, Message_statut_compte, Commentaire_Alimentaire,
    regime_alimentaire_id, Gestionnaire
) VALUES (
    :prenom, :nom, :sexe, :mail, :mdp, :telephone, :url_photo,
    :taille_teeshirt, :taille_pull, :adherent, :url_adhesion,
    :statut, :date_statut, :message_statut, :commentaire_alim,
    :regime_id, 0
)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
    ':prenom'           => $data['prenom'],
    ':nom'              => $data['nom'],
    ':sexe'             => $data['sexe'] ?? null,
    ':mail'             => $data['mail'],
    ':mdp'              => password_hash($data['mdp'], PASSWORD_DEFAULT),
    ':telephone'        => $data['telephone'],
    ':url_photo'        => $data['url_photo'] ?? null,
    ':taille_teeshirt'  => $data['taille_teeshirt'] ?? null,
    ':taille_pull'      => $data['taille_pull'] ?? null,
    ':adherent'         => (int) ($data['adherent'] ?? 0),
    ':url_adhesion'     => $data['url_adhesion'] ?? '',
    ':statut'           => 'en_attente',
    ':date_statut'      => date('Y-m-d H:i:s'),
    ':message_statut'   => 'Votre demande a bien été enregistrée',
    ':commentaire_alim' => $data['commentaire_alim'] ?? '',
    ':regime_id'        => !empty($data['regime_id']) ? (int)$data['regime_id'] : null,
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
            $sql = "SELECT COUNT(*) FROM membre WHERE Mail = :mail";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':mail' => $mail]);
            return (bool) $stmt->fetchColumn();
        } catch (Throwable $e) {
            error_log('Membre::emailExiste error: ' . $e->getMessage());
            return true;
        }
    }

    /**
     * Vérifie si un email existe déjà pour un autre utilisateur
     * (utilisé lors de la modification de profil)
     */
    public static function emailExisteAutreUtilisateur(string $mail, int $userId): bool
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "SELECT COUNT(*) FROM membre WHERE Mail = :mail AND Id_Membre != :userId";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':mail' => $mail, ':userId' => $userId]);
            return (bool) $stmt->fetchColumn();
        } catch (Throwable $e) {
            error_log('Membre::emailExisteAutreUtilisateur error: ' . $e->getMessage());
            return true;
        }
    }

public static function connexion(string $mail, string $mdp): array
{
    try {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "SELECT Id_Membre,
                       Prenom,
                       Nom,
                       Mail,
                       Mot_de_passe,
                       Gestionnaire,
                       Statut_compte
                FROM membre
                WHERE Mail = :mail
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':mail' => $mail]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($mdp, $row['Mot_de_passe'])) {
            return ['success' => false, 'message' => 'Identifiants incorrects'];
        }

        return [
            'success' => true,
            'data' => [
                'id_membre'     => (int)$row['Id_Membre'],
                'prenom'        => $row['Prenom'],
                'nom'           => $row['Nom'],
                'mail'          => $row['Mail'],
                'gestionnaire'  => (bool)$row['Gestionnaire'],
                'statut_compte' => $row['Statut_compte'] ?? 'en_attente',
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
                    WHERE Statut_compte = 'en_attente'
                    ORDER BY Date_statut_compte DESC";
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
                    WHERE Id_Membre = :id
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
                    SET Statut_compte = 'valide',
                        Date_statut_compte = NOW(),
                        Message_statut_compte = 'Compte validé'
                    WHERE Id_Membre = :id";
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
                    SET Statut_compte = 'refuse',
                        Date_statut_compte = NOW(),
                        Message_statut_compte = :motif
                    WHERE Id_Membre = :id";
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

    public static function mettreGestionnaire(int $id, int $valeur): bool
{
    try {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "UPDATE membre SET Gestionnaire = :valeur WHERE Id_Membre = :id";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([':valeur' => $valeur, ':id' => $id]);
        return $success && $stmt->rowCount() === 1;
    } catch (Throwable $e) {
        error_log("Exception: " . $e->getMessage());
        return false;
    }
}
    public static function getTousLesMembres(): array
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "SELECT * FROM membre ORDER BY Nom, Prenom";
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll() ?: [];
        } catch (Throwable $e) {
            error_log('Membre::getTousLesMembres error: ' . $e->getMessage());
            return [];
        }
    }
    public static function devenirAdherent(int $id): bool
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "UPDATE membre SET Adherent = 1 WHERE Id_Membre = :id";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([':id' => $id]) && $stmt->rowCount() === 1;
        } catch (Throwable $e) {
            error_log('Membre::devenirAdherent error: ' . $e->getMessage());
            return false;
        }
    }

    public static function getMembreByEmail(string $email): ?array
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "SELECT * FROM membre WHERE Mail = :mail LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':mail' => $email]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (Throwable $e) {
            error_log('Membre::getMembreByEmail error: ' . $e->getMessage());
            return null;
        }
    }

    public static function setResetToken(string $email, string $token): bool
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $sql = "UPDATE membre 
                    SET token_reset = :token, 
                        token_reset_expires = :expires 
                    WHERE Mail = :email";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':token' => $token,
                ':expires' => $expires,
                ':email' => $email
            ]);

            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            error_log('Membre::setResetToken error: ' . $e->getMessage());
            return false;
        }
    }

    public static function verifyResetToken(string $token): ?array
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "SELECT * FROM membre 
                    WHERE token_reset = :token 
                    AND token_reset_expires > NOW()";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':token' => $token]);
            $user = $stmt->fetch();
            
            return $user ?: null;
        } catch (Throwable $e) {
            error_log('Membre::verifyResetToken error: ' . $e->getMessage());
            return null;
        }
    }

    public static function updatePasswordByToken(string $token, string $newPassword): bool
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "UPDATE membre 
                    SET Mot_de_passe = :mdp,
                        token_reset = NULL,
                        token_reset_expires = NULL
                    WHERE token_reset = :token";
            
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                ':mdp' => password_hash($newPassword, PASSWORD_DEFAULT),
                ':token' => $token
            ]);
        } catch (Throwable $e) {
            error_log('Membre::updatePasswordByToken error: ' . $e->getMessage());
            return false;
        }
    }
    public static function updateMembre(int $id, array $data): bool
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "UPDATE membre SET 
                    Nom = :nom, 
                    Prenom = :prenom, 
                    Mail = :mail, 
                    Telephone = :telephone,
                    Taille_Teeshirt = :taille_teeshirt,
                    Taille_Pull = :taille_pull,
                    Commentaire_Alimentaire = :commentaire_alim,
                    regime_alimentaire_id = :regime_id";
            
            $params = [
                ':nom' => $data['nom'],
                ':prenom' => $data['prenom'],
                ':mail' => $data['mail'],
                ':telephone' => $data['telephone'],
                ':taille_teeshirt' => $data['taille_teeshirt'],
                ':taille_pull' => $data['taille_pull'],
                ':commentaire_alim' => $data['commentaire_alim'],
                ':regime_id' => !empty($data['regime_id']) ? (int)$data['regime_id'] : null,
                ':id' => $id
            ];

            if (!empty($data['url_photo'])) {
                $sql .= ", Url_Photo_Profil = :url_photo";
                $params[':url_photo'] = $data['url_photo'];
            }

            $sql .= " WHERE Id_Membre = :id";
            
            $stmt = $pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (Throwable $e) {
            error_log('Error updating membre: ' . $e->getMessage());
            return false;
        }
    }
    
    public static function changerMotDePasse(int $id, string $nouveauMdp): bool
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $stmt = $pdo->prepare("UPDATE membre SET Mot_de_passe = ? WHERE Id_Membre = ?");
            $mdpHash = password_hash($nouveauMdp, PASSWORD_DEFAULT);
            return $stmt->execute([$mdpHash, $id]);
        } catch (Throwable $e) {
            error_log('Error changing password: ' . $e->getMessage());
            return false;
        }
    }
    
    public static function supprimerMembre(int $id): bool
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $stmt = $pdo->prepare("DELETE FROM membre WHERE Id_Membre = ?");
            return $stmt->execute([$id]);
        } catch (Throwable $e) {
            return false;
        }
    }

    // soumettre demande adhesion
    public static function soumettreDemandeAdhesion(int $id, string $urlAdhesion): bool
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "UPDATE membre
                    SET Url_Adhesion = :url,
                        Statut_adhesion = 'en_attente',
                        Date_demande_adhesion = NOW()
                    WHERE Id_Membre = :id";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                ':url' => $urlAdhesion,
                ':id' => $id
            ]) && $stmt->rowCount() === 1;
        } catch (Throwable $e) {
            error_log('Membre::soumettreDemandeAdhesion error: ' . $e->getMessage());
            return false;
        }
    }

    // recuperer demandes adhesion en attente (compte valide uniquement)
    public static function getDemandesAdhesionEnAttente(): array
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "SELECT *
                    FROM membre
                    WHERE Statut_adhesion = 'en_attente'
                    AND Statut_compte != 'refuse'
                    ORDER BY Date_demande_adhesion DESC";
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll() ?: [];
        } catch (Throwable $e) {
            error_log('Membre::getDemandesAdhesionEnAttente error: ' . $e->getMessage());
            return [];
        }
    }

    // accepter demande adhesion
    public static function accepterAdhesion(int $id): bool
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "UPDATE membre
                    SET Adherent = 1,
                        Statut_adhesion = 'accepte',
                        Date_validation_adhesion = NOW(),
                        Message_adhesion = 'Votre demande d adhésion a été acceptée'
                    WHERE Id_Membre = :id
                    AND Statut_compte = 'valide'
                    AND Statut_adhesion = 'en_attente'";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([':id' => $id]) && $stmt->rowCount() === 1;
        } catch (Throwable $e) {
            error_log('Membre::accepterAdhesion error: ' . $e->getMessage());
            return false;
        }
    }

    // refuser demande adhesion
    public static function refuserAdhesion(int $id, string $motif = ''): bool
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $message = !empty($motif) ? $motif : 'Votre demande d adhésion a été refusée';
            $sql = "UPDATE membre
                    SET Adherent = 0,
                        Statut_adhesion = 'refuse',
                        Date_validation_adhesion = NOW(),
                        Message_adhesion = :message
                    WHERE Id_Membre = :id
                    AND Statut_compte = 'valide'
                    AND Statut_adhesion = 'en_attente'";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                ':message' => $message,
                ':id' => $id
            ]) && $stmt->rowCount() === 1;
        } catch (Throwable $e) {
            error_log('Membre::refuserAdhesion error: ' . $e->getMessage());
            return false;
        }
    }

    // recupere le nom du regime alimentaire d'un membre
    public static function obtenirRegimeAlimentaire(int $idMembre): ?string
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "SELECT ra.nom
                    FROM membre m
                    LEFT JOIN regimes_alimentaires ra ON m.regime_alimentaire_id = ra.id
                    WHERE m.Id_Membre = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $idMembre]);
            $result = $stmt->fetch();
            return $result ? $result['nom'] : null;
        } catch (Throwable $e) {
            error_log('Membre::obtenirRegimeAlimentaire error: ' . $e->getMessage());
            return null;
        }
    }

    // modifier le statut adherent d'un membre (admin only)
    public static function modifierStatutAdherent(int $id, bool $adherent): bool
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "UPDATE membre
                    SET Adherent = :adherent,
                        Statut_adhesion = :statut,
                        Date_validation_adhesion = NOW()
                    WHERE Id_Membre = :id
                    AND Statut_compte = 'valide'";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                ':adherent' => $adherent ? 1 : 0,
                ':statut' => $adherent ? 'accepte' : 'refuse',
                ':id' => $id
            ]) && $stmt->rowCount() === 1;
        } catch (Throwable $e) {
            error_log('Membre::modifierStatutAdherent error: ' . $e->getMessage());
            return false;
        }
    }
}
