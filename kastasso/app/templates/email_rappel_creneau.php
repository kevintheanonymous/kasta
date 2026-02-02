<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rappel événement demain</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; line-height: 1.6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f4; padding: 30px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; padding: 30px 20px; text-align: center;">
                            <h1 style="margin: 0; font-size: 28px; font-weight: bold;">RAPPEL KAST'ASSO</h1>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px; color: #333;">
                            <h2 style="color: #ff6b6b; margin-top: 0; margin-bottom: 20px;">C'est demain !</h2>
                            
                            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <strong style="color: #856404;">Votre événement a lieu dans 24 heures</strong>
                            </div>
                            
                            <p style="margin-bottom: 15px;">Bonjour <strong><?= htmlspecialchars($prenom) ?> <?= htmlspecialchars($nom) ?></strong>,</p>
                            
                            <p style="margin-bottom: 15px;">Nous vous rappelons que vous êtes inscrit(e) <?= $type_inscription === 'creneau' ? 'au créneau bénévole' : 'à l\'événement associatif' ?> suivant qui a lieu <strong>demain</strong> :</p>
                            
                            <!-- Event Details -->
                            <div style="background-color: #f8f9fa; border: 2px solid #ff6b6b; border-radius: 8px; padding: 25px; margin: 25px 0;">
                                <h3 style="color: #495057; margin-top: 0; margin-bottom: 15px; font-size: 22px;"><?= htmlspecialchars($titre_evenement) ?></h3>
                                
                                <?php if ($type_inscription === 'creneau'): ?>
                                    <p style="margin: 10px 0; font-size: 16px;"><strong style="color: #6c757d;">Type de créneau :</strong> <?= htmlspecialchars($type_creneau) ?></p>
                                    <p style="margin: 10px 0; font-size: 18px; color: #ff6b6b;"><strong>📆 Date :</strong> <span style="font-weight: bold;"><?= htmlspecialchars($date_creneau) ?></span></p>
                                    <p style="margin: 10px 0; font-size: 18px; color: #ff6b6b;"><strong>Horaires :</strong> <span style="font-weight: bold;"><?= htmlspecialchars($heure_debut) ?> - <?= htmlspecialchars($heure_fin) ?></span></p>
                                    <?php if (!empty($commentaire_creneau)): ?>
                                        <p style="margin: 10px 0; font-size: 16px;"><strong style="color: #6c757d;">Informations :</strong> <?= htmlspecialchars($commentaire_creneau) ?></p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p style="margin: 10px 0; font-size: 18px; color: #ff6b6b;"><strong>📆 Date :</strong> <span style="font-weight: bold;"><?= htmlspecialchars($date_evenement) ?></span></p>
                                <?php endif; ?>
                                
                                <p style="margin: 10px 0; font-size: 16px;"><strong style="color: #6c757d;">Lieu :</strong> <?= htmlspecialchars($adresse) ?>, <?= htmlspecialchars($code_postal) ?> <?= htmlspecialchars($ville) ?></p>
                                
                                <?php if (!empty($descriptif)): ?>
                                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #dee2e6;">
                                        <p style="margin: 5px 0; color: #6c757d; font-size: 14px;"><strong>Description :</strong></p>
                                        <p style="margin: 5px 0; color: #495057; font-size: 14px;"><?= nl2br(htmlspecialchars($descriptif)) ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($lieu_maps)): ?>
                                    <p style="margin: 20px 0 0 0;">
                                        <a href="<?= htmlspecialchars($lieu_maps) ?>" style="display: inline-block; padding: 12px 25px; background-color: #17a2b8; color: #ffffff; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: bold;">Itinéraire Google Maps</a>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($nb_inscrits)): ?>
                                <div style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                    <p style="margin: 0; color: #155724;">
                                        <strong>Nombre de participants inscrits :</strong> <?= htmlspecialchars($nb_inscrits) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($contact_organisateur)): ?>
                                <div style="background-color: #e7f3ff; border-left: 4px solid #007bff; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                    <p style="margin: 0; color: #004085;">
                                        <strong>Contact organisateur :</strong> <?= htmlspecialchars($contact_organisateur) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <div style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <strong style="color: #721c24;">Empêché(e) ?</strong>
                                <p style="margin: 10px 0 0 0; color: #721c24;">
                                    Si vous ne pouvez plus participer, merci de vous désinscrire rapidement depuis votre profil pour permettre à d'autres bénévoles de prendre votre place.
                                </p>
                            </div>
                            
                            <!-- Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding: 25px 0;">
                                        <a href="<?= htmlspecialchars($lien_profil) ?>" style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">Gérer mes inscriptions</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin-top: 20px; color: #6c757d; font-size: 14px;">À très bientôt !</p>
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
