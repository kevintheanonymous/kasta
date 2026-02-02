<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation d'inscription</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; line-height: 1.6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f4; padding: 30px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; padding: 30px 20px; text-align: center;">
                            <h1 style="margin: 0; font-size: 28px; font-weight: bold;">KAST'ASSO</h1>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px; color: #333;">
                            <h2 style="color: #007bff; margin-top: 0; margin-bottom: 20px;">Inscription confirmée !</h2>
                            
                            <div style="background-color: #d1ecf1; border-left: 4px solid #007bff; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <strong style="color: #0c5460;">Vous êtes inscrit(e) avec succès</strong>
                            </div>
                            
                            <p style="margin-bottom: 15px;">Bonjour <strong><?= htmlspecialchars($prenom) ?> <?= htmlspecialchars($nom) ?></strong>,</p>
                            
                            <p style="margin-bottom: 15px;">Nous confirmons votre inscription <?= $type_inscription === 'creneau' ? 'au créneau bénévole' : 'à l\'événement associatif' ?> suivant :</p>
                            
                            <!-- Event Details -->
                            <div style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 20px; margin: 20px 0;">
                                <h3 style="color: #495057; margin-top: 0; margin-bottom: 15px; font-size: 20px;"><?= htmlspecialchars($titre_evenement ?? 'Événement') ?></h3>
                                
                                <?php if ($type_inscription === 'creneau'): ?>
                                    <p style="margin: 8px 0;"><strong style="color: #6c757d;">Type de créneau :</strong> <?= htmlspecialchars($type_creneau ?? '') ?></p>
                                    <p style="margin: 8px 0;"><strong style="color: #6c757d;">Date :</strong> <?= htmlspecialchars($date_creneau ?? '') ?></p>
                                    <p style="margin: 8px 0;"><strong style="color: #6c757d;">Horaires :</strong> <?= htmlspecialchars($heure_debut ?? '') ?> - <?= htmlspecialchars($heure_fin ?? '') ?></p>
                                    <?php if (!empty($commentaire_creneau)): ?>
                                        <p style="margin: 8px 0;"><strong style="color: #6c757d;">Informations :</strong> <?= htmlspecialchars($commentaire_creneau) ?></p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p style="margin: 8px 0;"><strong style="color: #6c757d;">Date de l'événement :</strong> <?= htmlspecialchars($date_evenement ?? 'À confirmer') ?></p>
                                    <?php if (!empty($nb_invites) && $nb_invites > 0): ?>
                                        <p style="margin: 8px 0;"><strong style="color: #6c757d;">Accompagnateurs :</strong>
                                            <?= $nb_invites ?> accompagnateur<?= $nb_invites > 1 ? 's' : '' ?>
                                        </p>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <p style="margin: 8px 0;"><strong style="color: #6c757d;">Lieu :</strong> <?= htmlspecialchars($adresse ?? '') ?><?= !empty($adresse) && (!empty($code_postal) || !empty($ville)) ? ', ' : '' ?><?= htmlspecialchars($code_postal ?? '') ?> <?= htmlspecialchars($ville ?? '') ?></p>
                                
                                <?php if (!empty($lieu_maps)): ?>
                                    <p style="margin: 15px 0 8px 0;">
                                        <a href="<?= htmlspecialchars($lieu_maps) ?>" style="display: inline-block; padding: 8px 15px; background-color: #17a2b8; color: #ffffff; text-decoration: none; border-radius: 4px; font-size: 14px;">Voir sur Google Maps</a>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <strong style="color: #856404;">N'oubliez pas :</strong>
                                <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #856404;">
                                    <li>Notez bien la date et l'heure dans votre agenda</li>
                                    <li>Vous recevrez un rappel 24h avant l'événement</li>
                                    <li>En cas d'empêchement, pensez à vous désinscrire depuis votre profil</li>
                                </ul>
                            </div>
                            
                            <!-- Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="<?= htmlspecialchars($lien_profil ?? '#') ?>" style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">Voir mon profil</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin-top: 20px; color: #6c757d; font-size: 14px;">Merci de votre engagement associatif !</p>
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
