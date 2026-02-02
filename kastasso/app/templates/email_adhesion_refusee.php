<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adhésion refusée</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; line-height: 1.6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f4; padding: 30px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #d32f2f 0%, #b71c1c 100%); color: white; padding: 30px 20px; text-align: center;">
                            <h1 style="margin: 0; font-size: 28px; font-weight: bold;">KAST'ASSO</h1>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px; color: #333;">
                            <h2 style="color: #d32f2f; margin-top: 0; margin-bottom: 20px;">Réponse à votre demande d'adhésion</h2>

                            <div style="background-color: #ffebee; border-left: 4px solid #d32f2f; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <strong style="color: #b71c1c;">Votre demande d'adhésion n'a pas pu être acceptée</strong>
                            </div>

                            <p style="margin-bottom: 15px;">Bonjour <strong><?= htmlspecialchars($prenom) ?> <?= htmlspecialchars($nom) ?></strong>,</p>

                            <p style="margin-bottom: 15px;">Nous avons bien reçu votre demande d'adhésion à <strong>KAST'ASSO</strong>.</p>

                            <p style="margin-bottom: 15px;">Après examen de votre dossier, nous sommes au regret de vous informer que nous ne pouvons pas donner suite favorablement à votre demande pour le moment.</p>

                            <?php if (!empty($motif)): ?>
                            <div style="background-color: #f5f5f5; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <p style="margin: 0 0 10px 0;"><strong>Motif du refus :</strong></p>
                                <p style="margin: 0; color: #555;"><?= htmlspecialchars($motif) ?></p>
                            </div>
                            <?php endif; ?>

                            <div style="background-color: #e3f2fd; border-left: 4px solid #0288d1; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <p style="margin: 0;"><strong>ℹ️ Important :</strong> Vous pouvez toujours participer aux événements de l'association en tant que membre non-adhérent. Notez cependant que vous ne bénéficierez pas de la couverture assurance lors des événements sportifs.</p>
                            </div>

                            <!-- Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="<?= htmlspecialchars($lien_connexion) ?>" style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #0288d1 0%, #01579b 100%); color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">Accéder à mon espace</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin-bottom: 15px;">Si vous souhaitez plus d'informations ou renouveler votre demande d'adhésion ultérieurement, n'hésitez pas à nous contacter.</p>

                            <p style="margin-bottom: 15px;">Cordialement,</p>

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
