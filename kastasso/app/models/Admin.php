<?php

require_once __DIR__ . '/BaseDeDonnees.php';

class Admin
{
    public static function connexion(string $login, string $mdp): array
    {
        try {
            $pdo   = BaseDeDonnees::getConnexion();
            $login = trim($login);
            $mdp   = trim($mdp);

            $sql = "SELECT id_admin,
                           identifiant,
                           mail,
                           mot_de_passe
                    FROM admin
                    WHERE identifiant = :loginIdent OR mail = :loginMail
                    LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':loginIdent' => $login,
                ':loginMail'  => $login,
            ]);
            $row = $stmt->fetch();

            if (!$row || trim($row['mot_de_passe']) !== $mdp) {
                return ['success' => false, 'message' => 'Identifiants incorrects'];
            }

            return [
                'success' => true,
                'data' => [
                    'id_admin'    => (int) $row['id_admin'],
                    'identifiant' => $row['identifiant'],
                    'mail'        => $row['mail'],
                ],
            ];
        } catch (Throwable $e) {
            error_log('Admin::connexion error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur de connexion'];
        }
    }
    
}