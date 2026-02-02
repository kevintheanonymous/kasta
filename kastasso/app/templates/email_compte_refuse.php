<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande non acceptée</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; line-height: 1.6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f4; padding: 30px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 30px 20px; text-align: center;">
                            <h1 style="margin: 0; font-size: 28px; font-weight: bold;">KAST'ASSO</h1>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px; color: #333;">
                            <h2 style="color: #dc3545; margin-top: 0; margin-bottom: 20px;">Réponse à votre demande d'inscription</h2>
                            
                            <p style="margin-bottom: 15px;">Bonjour <strong><?= htmlspecialchars($prenom) ?> <?= htmlspecialchars($nom) ?></strong>,</p>
                            
                            <div style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <strong style="color: #721c24;">Votre demande d'inscription n'a pas pu être acceptée</strong>
                            </div>
                            
                            <p style="margin-bottom: 15px;">Nous avons bien reçu votre demande d'inscription à <strong>KAST'ASSO</strong>. Après examen de votre dossier, nous sommes au regret de vous informer que nous ne pouvons pas donner suite à votre demande pour le moment.</p>
                            
                            <div style="background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 5px; padding: 15px; margin: 20px 0;">
                                <p style="margin: 0 0 10px 0;"><strong>Motif :</strong></p>
                                <p style="margin: 0; color: #856404;"><?= nl2br(htmlspecialchars($motif)) ?></p>
                            </div>
                            
                            <p style="margin-bottom: 15px;">Nous vous encourageons vivement à :</p>
                            <ul style="margin-bottom: 20px; padding-left: 20px;">
                                <li style="margin-bottom: 8px;">Vérifier les informations fournies</li>
                                <li style="margin-bottom: 8px;">Compléter votre profil si nécessaire</li>
                                <li style="margin-bottom: 8px;">Soumettre une nouvelle demande d'inscription</li>
                            </ul>
                            
                            <!-- Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="<?= htmlspecialchars($lien_inscription) ?>" style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #1464F6 0%, #0d4db8 100%); color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">Nouvelle inscription</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin-bottom: 15px;">Si vous avez des questions concernant cette décision ou si vous souhaitez des précisions, n'hésitez pas à nous contacter directement.</p>
                            
                            <p style="margin-bottom: 15px;">Nous vous remercions de l'intérêt que vous portez à notre association.</p>
                            
                            <p style="margin-bottom: 5px;">Cordialement,</p>
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
