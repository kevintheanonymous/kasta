<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de votre mot de passe</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; line-height: 1.6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f4; padding: 30px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1464F6 0%, #0d4db8 100%); color: white; padding: 30px 20px; text-align: center;">
                            <h1 style="margin: 0; font-size: 28px; font-weight: bold;">KAST'ASSO</h1>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px; color: #333;">
                            <h2 style="color: #1464F6; margin-top: 0; margin-bottom: 20px;">Réinitialisation de mot de passe</h2>
                            
                            <p style="margin-bottom: 15px;">Bonjour <strong><?= htmlspecialchars($nomComplet) ?></strong>,</p>
                            
                            <p style="margin-bottom: 15px;">Vous avez demandé la réinitialisation de votre mot de passe pour votre compte <strong>KAST'ASSO</strong>.</p>
                            
                            <p style="margin-bottom: 25px;">Veuillez cliquer sur le bouton ci-dessous pour définir un nouveau mot de passe :</p>
                            
                            <!-- Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="<?= htmlspecialchars($lien_reset) ?>" style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #1464F6 0%, #0d4db8 100%); color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">Réinitialiser mon mot de passe</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 25px 0; border-radius: 4px;">
                                <p style="margin: 0; color: #856404;"><strong>Ce lien est valable pendant 1 heure.</strong></p>
                            </div>
                            
                            <p style="margin-bottom: 15px; color: #666;">Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email en toute sécurité.</p>
                            
                            <hr style="border: none; border-top: 1px solid #eee; margin: 25px 0;">
                            
                            <p style="font-size: 12px; color: #999; margin-bottom: 5px;">Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :</p>
                            <p style="font-size: 11px; color: #1464F6; word-break: break-all;"><?= htmlspecialchars($lien_reset) ?></p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9f9f9; padding: 20px; text-align: center; border-top: 1px solid #eee;">
                            <p style="margin: 0; font-size: 12px; color: #777;">Ceci est un email automatique, merci de ne pas y répondre.</p>
                            <p style="margin: 10px 0 0 0; font-size: 12px; color: #777;">&copy; <?= date('Y') ?> KAST'ASSO. Tous droits réservés.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
