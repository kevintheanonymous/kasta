<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Événement annulé</title>
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
                            <h2 style="color: #dc3545; margin-top: 0; margin-bottom: 20px;">Événement annulé</h2>
                            
                            <div style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <strong style="color: #721c24;">Un événement auquel vous étiez inscrit(e) a été annulé</strong>
                            </div>
                            
                            <p style="margin-bottom: 15px;">Bonjour <strong><?= htmlspecialchars($prenom) ?> <?= htmlspecialchars($nom) ?></strong>,</p>
                            
                            <p style="margin-bottom: 15px;">Nous sommes au regret de vous informer que l'événement suivant a été <strong>annulé</strong> :</p>
                            
                            <!-- Event Details -->
                            <div style="background-color: #f8f9fa; border: 2px solid #dc3545; border-radius: 8px; padding: 25px; margin: 25px 0;">
                                <h3 style="color: #495057; margin-top: 0; margin-bottom: 15px; font-size: 22px;"><?= htmlspecialchars($titre_evenement ?? '') ?></h3>
                                
                                <?php if ($type_inscription === 'creneau'): ?>
                                    <p style="margin: 8px 0;"><strong style="color: #6c757d;">Type de créneau :</strong> <?= htmlspecialchars($type_creneau ?? '') ?></p>
                                    <p style="margin: 8px 0;"><strong style="color: #6c757d;">Date prévue :</strong> <?= htmlspecialchars($date_creneau ?? '') ?></p>
                                    <p style="margin: 8px 0;"><strong style="color: #6c757d;">Horaires prévus :</strong> <?= htmlspecialchars($heure_debut ?? '') ?> - <?= htmlspecialchars($heure_fin ?? '') ?></p>
                                <?php else: ?>
                                    <p style="margin: 8px 0;"><strong style="color: #6c757d;">Date prévue :</strong> <?= htmlspecialchars($date_evenement ?? '') ?></p>
                                <?php endif; ?>
                                
                                <p style="margin: 8px 0;"><strong style="color: #6c757d;">Lieu :</strong> <?= htmlspecialchars($adresse ?? '') ?>, <?= htmlspecialchars($code_postal ?? '') ?> <?= htmlspecialchars($ville ?? '') ?></p>
                            </div>
                            
                            <?php if (!empty($raison_annulation)): ?>
                                <div style="background-color: #e7f3ff; border-left: 4px solid #007bff; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                    <strong style="color: #004085;">Raison de l'annulation :</strong>
                                    <p style="margin: 10px 0 0 0; color: #004085;"><?= nl2br(htmlspecialchars($raison_annulation)) ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <div style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <strong style="color: #155724;">Votre inscription a été automatiquement annulée</strong>
                                <p style="margin: 10px 0 0 0; color: #155724;">
                                    Vous n'avez aucune démarche à effectuer. Nous vous présenterons prochainement de nouveaux événements.
                                </p>
                            </div>
                            
                            <?php if (!empty($evenements_similaires) && count($evenements_similaires) > 0): ?>
                                <div style="margin: 30px 0;">
                                    <h4 style="color: #495057; margin: 0 0 15px 0;">Événements similaires à venir :</h4>
                                    <?php foreach ($evenements_similaires as $event): ?>
                                        <div style="background-color: #f8f9fa; border-left: 3px solid #28a745; padding: 12px; margin: 10px 0; border-radius: 4px;">
                                            <p style="margin: 0; font-weight: bold; color: #495057;"><?= htmlspecialchars($event['titre']) ?></p>
                                            <p style="margin: 5px 0 0 0; font-size: 14px; color: #6c757d;">
                                                <?= htmlspecialchars($event['date']) ?> - <?= htmlspecialchars($event['ville']) ?>
                                            </p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding: 25px 0;">
                                        <a href="<?= htmlspecialchars($lien_evenements) ?>" style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">Voir d'autres événements</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin-top: 20px; color: #6c757d; font-size: 14px;">Nous nous excusons pour ce désagrément et espérons vous revoir très prochainement.</p>
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
