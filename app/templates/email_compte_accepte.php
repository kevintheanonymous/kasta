<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte accepté</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; line-height: 1.6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f4; padding: 30px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white; padding: 30px 20px; text-align: center;">
                            <h1 style="margin: 0; font-size: 28px; font-weight: bold;">KAST'ASSO</h1>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px; color: #333;">
                            <h2 style="color: #28a745; margin-top: 0; margin-bottom: 20px;">Félicitations <?= htmlspecialchars($prenom) ?> !</h2>
                            
                            <div style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <strong style="color: #155724;">Votre compte a été validé avec succès</strong>
                            </div>
                            
                            <p style="margin-bottom: 15px;">Bonjour <strong><?= htmlspecialchars($prenom) ?> <?= htmlspecialchars($nom) ?></strong>,</p>
                            
                            <p style="margin-bottom: 15px;">Nous sommes ravis de vous annoncer que votre demande d'inscription à <strong>KAST'ASSO</strong> a été <strong>acceptée</strong> par notre équipe !</p>
                            
                            <p style="margin-bottom: 15px;">Vous pouvez désormais vous connecter à votre espace membre et profiter de tous nos services :</p>
                            
                            <ul style="margin-bottom: 20px; padding-left: 20px;">
                                <li style="margin-bottom: 8px;">Inscription aux événements sportifs</li>
                                <li style="margin-bottom: 8px;">Participation aux créneaux d'aide bénévole</li>
                                <li style="margin-bottom: 8px;">Accès aux événements associatifs</li>
                                <li style="margin-bottom: 8px;">Gestion de votre profil</li>
                            </ul>
                            
                            <!-- Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="<?= htmlspecialchars($lien_connexion) ?>" style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">Se connecter maintenant</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <div style="background-color: #e9ecef; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <p style="margin: 0 0 10px 0;"><strong>Vos identifiants de connexion :</strong></p>
                                <p style="margin: 5px 0;"><strong>Email :</strong> <?= htmlspecialchars($email) ?></p>
                                <p style="margin: 5px 0;"><strong>Mot de passe :</strong> Celui que vous avez choisi lors de l'inscription</p>
                            </div>
                            
                            <p style="margin-bottom: 15px;">Si vous avez des questions, n'hésitez pas à nous contacter.</p>
                            
                            <p style="margin-bottom: 15px;">À très bientôt !</p>
                            
                            <p><strong>L'équipe KAST'ASSO</strong></p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9f9f9; padding: 20px; text-align: center; border-top: 1px solid #eee;">
                            <p style="margin: 0; font-size: 12px; color: #777;">&copy; <?= date('Y') ?> KAST'ASSO. Tous droits réservés.</p>
                            <p style="margin: 10px 0 0 0; font-size: 12px; color: #777;">Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
