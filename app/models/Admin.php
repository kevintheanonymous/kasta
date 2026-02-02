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

            $sql = "SELECT Id_Admin,
                           identifiant,
                           Mail,
                           Mot_de_passe
                    FROM admin
                    WHERE identifiant = :loginIdent OR Mail = :loginMail
                    LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':loginIdent' => $login,
                ':loginMail'  => $login,
            ]);
            $row = $stmt->fetch();

            if (!$row || !password_verify($mdp, $row['Mot_de_passe'])) {
                return ['success' => false, 'message' => 'Identifiants incorrects'];
            }

            return [
                'success' => true,
                'data' => [
                    'id_admin'    => (int) $row['Id_Admin'],
                    'identifiant' => $row['identifiant'],
                    'mail'        => $row['Mail'],
                ],
            ];
        } catch (Throwable $e) {
            error_log('Admin::connexion error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur de connexion'];
        }
    }

    public static function emailExiste(string $email): bool
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "SELECT COUNT(*) FROM admin WHERE Mail = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            return (bool) $stmt->fetchColumn();
        } catch (Throwable $e) {
            error_log('Admin::emailExiste error: ' . $e->getMessage());
            return false;
        }
    }

    public static function getAdminByEmail(string $email): ?array
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "SELECT * FROM admin WHERE Mail = :email LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (Throwable $e) {
            error_log('Admin::getAdminByEmail error: ' . $e->getMessage());
            return null;
        }
    }

    public static function setResetToken(string $email, string $token): bool
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $sql = "UPDATE admin
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
            error_log('Admin::setResetToken error: ' . $e->getMessage());
            return false;
        }
    }

    public static function verifyResetToken(string $token): ?array
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "SELECT * FROM admin 
                    WHERE token_reset = :token 
                    AND token_reset_expires > NOW()";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':token' => $token]);
            $user = $stmt->fetch();

            return $user ?: null;
        } catch (Throwable $e) {
            error_log('Admin::verifyResetToken error: ' . $e->getMessage());
            return null;
        }
    }

    public static function updatePasswordByToken(string $token, string $newPassword): bool
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "UPDATE admin 
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
            error_log('Admin::updatePasswordByToken error: ' . $e->getMessage());
            return false;
        }
    }
}