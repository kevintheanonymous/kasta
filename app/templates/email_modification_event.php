<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modification d'événement</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; line-height: 1.6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f4; padding: 30px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: #212529; padding: 30px 20px; text-align: center;">
                            <h1 style="margin: 0; font-size: 28px; font-weight: bold;">KAST'ASSO</h1>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px; color: #333;">
                            <h2 style="color: #ff9800; margin-top: 0; margin-bottom: 20px;">Événement modifié</h2>
                            
                            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <strong style="color: #856404;">Important : Des modifications ont été apportées à un événement auquel vous êtes inscrit(e)</strong>
                            </div>
                            
                            <p style="margin-bottom: 15px;">Bonjour <strong><?= htmlspecialchars($prenom) ?> <?= htmlspecialchars($nom) ?></strong>,</p>
                            
                            <p style="margin-bottom: 15px;">Nous vous informons que l'événement suivant a été modifié :</p>
                            
                            <!-- Event Details -->
                            <div style="background-color: #f8f9fa; border: 2px solid #ffc107; border-radius: 8px; padding: 25px; margin: 25px 0;">
                                <h3 style="color: #495057; margin-top: 0; margin-bottom: 15px; font-size: 22px;"><?= htmlspecialchars($titre_evenement) ?></h3>
                                
                                <?php if (!empty($modifications)): ?>
                                    <div style="margin: 20px 0;">
                                        <h4 style="color: #ff9800; margin: 0 0 10px 0; font-size: 16px;">Modifications apportées :</h4>
                                        <ul style="margin: 0; padding-left: 20px; color: #495057;">
                                            <?php foreach ($modifications as $modification): ?>
                                                <li style="margin: 8px 0;"><?= htmlspecialchars($modification) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                                
                                <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #dee2e6;">
                                    <h4 style="color: #28a745; margin: 0 0 15px 0; font-size: 16px;">Nouvelles informations :</h4>
                                    
                                    <?php if ($type_inscription === 'creneau'): ?>
                                        <p style="margin: 8px 0;"><strong style="color: #6c757d;">Type de créneau :</strong> <?= htmlspecialchars($type_creneau) ?></p>
                                        <p style="margin: 8px 0;"><strong style="color: #6c757d;">Date :</strong> <?= htmlspecialchars($date_creneau) ?></p>
                                        <p style="margin: 8px 0;"><strong style="color: #6c757d;">Horaires :</strong> <?= htmlspecialchars($heure_debut) ?> - <?= htmlspecialchars($heure_fin) ?></p>
                                        <?php if (!empty($commentaire_creneau)): ?>
                                            <p style="margin: 8px 0;"><strong style="color: #6c757d;">Informations :</strong> <?= htmlspecialchars($commentaire_creneau) ?></p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p style="margin: 8px 0;"><strong style="color: #6c757d;">Date de l'événement :</strong> <?= htmlspecialchars($date_evenement) ?></p>
                                    <?php endif; ?>
                                    
                                    <p style="margin: 8px 0;"><strong style="color: #6c757d;">Lieu :</strong> <?= htmlspecialchars($adresse) ?>, <?= htmlspecialchars($code_postal) ?> <?= htmlspecialchars($ville) ?></p>
                                    
                                    <?php if (!empty($descriptif)): ?>
                                        <div style="margin-top: 10px;">
                                            <p style="margin: 5px 0; color: #6c757d; font-size: 14px;"><strong>Description :</strong></p>
                                            <p style="margin: 5px 0; color: #495057; font-size: 14px;"><?= nl2br(htmlspecialchars($descriptif)) ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($lieu_maps)): ?>
                                        <p style="margin: 15px 0 0 0;">
                                            <a href="<?= htmlspecialchars($lieu_maps) ?>" style="display: inline-block; padding: 10px 20px; background-color: #17a2b8; color: #ffffff; text-decoration: none; border-radius: 4px; font-size: 14px;">Voir sur Google Maps</a>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($message_organisateur)): ?>
                                <div style="background-color: #e7f3ff; border-left: 4px solid #007bff; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                    <strong style="color: #004085;">Message de l'organisateur :</strong>
                                    <p style="margin: 10px 0 0 0; color: #004085;"><?= nl2br(htmlspecialchars($message_organisateur)) ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <div style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <strong style="color: #721c24;">Vous ne pouvez plus participer ?</strong>
                                <p style="margin: 10px 0 0 0; color: #721c24;">
                                    Si ces modifications ne vous conviennent pas, vous pouvez vous désinscrire depuis votre profil.
                                </p>
                            </div>
                            
                            <!-- Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding: 25px 0;">
                                        <a href="<?= htmlspecialchars($lien_profil) ?>" style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: #212529; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">Gérer mes inscriptions</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin-top: 20px; color: #6c757d; font-size: 14px;">Merci de votre compréhension.</p>
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
