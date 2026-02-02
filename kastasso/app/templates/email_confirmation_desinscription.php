<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de désinscription</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; line-height: 1.6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f4; padding: 30px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; padding: 30px 20px; text-align: center;">
                            <h1 style="margin: 0; font-size: 28px; font-weight: bold;">KAST'ASSO</h1>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px; color: #333;">
                            <h2 style="color: #6c757d; margin-top: 0; margin-bottom: 20px;">Désinscription confirmée</h2>
                            
                            <div style="background-color: #e2e3e5; border-left: 4px solid #6c757d; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <strong style="color: #383d41;">Votre désinscription a été prise en compte</strong>
                            </div>
                            
                            <p style="margin-bottom: 15px;">Bonjour <strong><?= htmlspecialchars($prenom ?? '') ?> <?= htmlspecialchars($nom ?? '') ?></strong>,</p>
                            
                            <p style="margin-bottom: 15px;">Nous confirmons votre désinscription <?= ($type_inscription ?? '') === 'creneau' ? 'du créneau bénévole' : 'de l\'événement associatif' ?> suivant :</p>
                            
                            <!-- Event Details -->
                            <div style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 20px; margin: 20px 0;">
                                <h3 style="color: #495057; margin-top: 0; margin-bottom: 15px; font-size: 20px;"><?= htmlspecialchars($titre_evenement ?? 'Événement') ?></h3>
                                
                                <?php if (($type_inscription ?? '') === 'creneau'): ?>
                                    <p style="margin: 8px 0;"><strong style="color: #6c757d;">Type de créneau :</strong> <?= htmlspecialchars($type_creneau ?? '') ?></p>
                                    <p style="margin: 8px 0;"><strong style="color: #6c757d;">Date :</strong> <?= htmlspecialchars($date_creneau ?? '') ?></p>
                                    <p style="margin: 8px 0;"><strong style="color: #6c757d;">Horaires :</strong> <?= htmlspecialchars($heure_debut ?? '') ?> - <?= htmlspecialchars($heure_fin ?? '') ?></p>
                                <?php else: ?>
                                    <p style="margin: 8px 0;"><strong style="color: #6c757d;">Date de l'événement :</strong> <?= htmlspecialchars($date_evenement ?? 'À confirmer') ?></p>
                                <?php endif; ?>
                                
                                <p style="margin: 8px 0;"><strong style="color: #6c757d;">Lieu :</strong> <?= htmlspecialchars($adresse ?? '') ?><?= !empty($adresse) && (!empty($code_postal) || !empty($ville)) ? ', ' : '' ?><?= htmlspecialchars($code_postal ?? '') ?> <?= htmlspecialchars($ville ?? '') ?></p>
                            </div>
                            
                            <div style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <strong style="color: #155724;">Envie de participer à un autre événement ?</strong>
                                <p style="margin: 10px 0 0 0; color: #155724;">
                                    Consultez notre liste d'événements à venir et inscrivez-vous à celui qui vous convient !
                                </p>
                            </div>
                            
                            <!-- Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="<?= htmlspecialchars($lien_evenements ?? '#') ?>" style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">Voir les événements</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin-top: 20px; color: #6c757d; font-size: 14px;">Nous espérons vous revoir bientôt !</p>
                            <p style="margin-top: 5px; color: #6c757d; font-size: 14px;">L'équipe KAST'ASSO</p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #343a40; color: #ffffff; padding: 20px; text-align: center; font-size: 12px;">
                            <p style="margin: 0 0 10px 0;">© 2025 KAST'ASSO - Tous droits réservés</p>
                            <p style="margin: 0;">Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
