<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adhésion acceptée</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; line-height: 1.6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f4; padding: 30px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0288d1 0%, #01579b 100%); color: white; padding: 30px 20px; text-align: center;">
                            <h1 style="margin: 0; font-size: 28px; font-weight: bold;">KAST'ASSO</h1>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px; color: #333;">
                            <h2 style="color: #0288d1; margin-top: 0; margin-bottom: 20px;">Félicitations <?= htmlspecialchars($prenom) ?> !</h2>

                            <div style="background-color: #e3f2fd; border-left: 4px solid #0288d1; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <strong style="color: #01579b;">Votre adhésion à KAST'ASSO a été acceptée</strong>
                            </div>

                            <p style="margin-bottom: 15px;">Bonjour <strong><?= htmlspecialchars($prenom) ?> <?= htmlspecialchars($nom) ?></strong>,</p>

                            <p style="margin-bottom: 15px;">Nous sommes ravis de vous annoncer que votre demande d'adhésion à <strong>KAST'ASSO</strong> a été <strong>acceptée</strong> par notre équipe !</p>

                            <p style="margin-bottom: 15px;"><strong>Vous êtes maintenant adhérent(e) de l'association</strong> et bénéficiez de tous les avantages :</p>

                            <ul style="margin-bottom: 20px; padding-left: 20px;">
                                <li style="margin-bottom: 8px;">✅ Couverture par l'assurance de l'association lors des événements</li>
                                <li style="margin-bottom: 8px;">✅ Accès prioritaire aux événements sportifs</li>
                                <li style="margin-bottom: 8px;">✅ Participation aux décisions de l'association</li>
                                <li style="margin-bottom: 8px;">✅ Tarifs préférentiels sur certaines activités</li>
                            </ul>

                            <!-- Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="<?= htmlspecialchars($lien_connexion) ?>" style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #0288d1 0%, #01579b 100%); color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">Accéder à mon espace</a>
                                    </td>
                                </tr>
                            </table>

                            <div style="background-color: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <p style="margin: 0;"><strong>⚠️ Important :</strong> Votre adhésion est valable pour l'année en cours. Pensez à la renouveler chaque année pour continuer à bénéficier de la couverture assurance.</p>
                            </div>

                            <p style="margin-bottom: 15px;">Si vous avez des questions, n'hésitez pas à nous contacter.</p>

                            <p style="margin-bottom: 15px;">Bienvenue parmi les adhérents de KAST'ASSO !</p>

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
