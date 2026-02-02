<?php
/**
 * Vue : Liste des participants inscrits à un événement associatif (Gestionnaire)
 * Affiche tous les participants avec leurs informations d'inscription
 * Variables disponibles : $event, $nombreInscrits, $participants
 */
$pageTitle = "Participants inscrits - " . htmlspecialchars($event['titre']);
require_once __DIR__ . '/../../gabarits/en_tete.php';
require_once __DIR__ . '/../../gabarits/barre_nav.php';
?>

<link rel="stylesheet" href="<?= asset('css/admin_inscrits.css?v=' . time()) ?>">
<link rel="stylesheet" href="<?= asset('css/tableau_de_bord_admin.css?v=' . time()) ?>">
<link rel="stylesheet" href="<?= asset('css/gestionnaire_participants.css?v=' . time()) ?>">

<div class="admin-container">
    <div class="header-actions">
        <div>
            <h1>Participants inscrits à l'événement</h1>
            <div class="event-info">
                <p><strong>Événement :</strong> <?= htmlspecialchars($event['titre']) ?></p>
                <p><strong>Date de l'événement :</strong> <?= date('d/m/Y à H:i', strtotime($event['date_event_asso'])) ?></p>
                <p><strong>Lieu :</strong> <?= htmlspecialchars($event['ville']) ?></p>
                <?php if ($event['tarif'] > 0): ?>
                    <p><strong>Tarif :</strong> <?= number_format($event['tarif'], 2, ',', ' ') ?> €</p>
                <?php endif; ?>
                <p class="nb-inscrits-total">
                    <strong>Total participants inscrits :</strong>
                    <span class="badge badge-primary badge-large">
                        <?= $nombreInscrits ?> participant<?= $nombreInscrits > 1 ? 's' : '' ?>
                    </span>
                </p>
            </div>
        </div>
        <div class="actions">
            <a href="<?= url('/gestionnaire/events&type=asso') ?>" class="btn btn-secondary">← Retour aux événements</a>
            <a href="<?= url('/gestionnaire/events/pdf-participants-asso&id=' . $event['id_event_asso']) ?>" class="btn btn-success btn-with-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="12" y1="18" x2="12" y2="12"></line>
                    <line x1="9" y1="15" x2="15" y2="15"></line>
                </svg>
                Télécharger la liste (PDF)
            </a>
        </div>
    </div>

    <?php if (empty($participants)): ?>
        <div class="alert alert-info alert-centered">
            <strong>Aucun participant inscrit</strong><br>
            Cet événement n'a pas encore de participants inscrits.
        </div>
    <?php else: ?>
        <div class="participants-section">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Date d'inscription</th>
                        <th>Accompagnateurs</th>
                        <th>Paiement</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participants as $participant): ?>
                        <tr>
                            <td><?= htmlspecialchars($participant['Nom']) ?></td>
                            <td><?= htmlspecialchars($participant['Prenom']) ?></td>
                            <td>
                                <a href="mailto:<?= htmlspecialchars($participant['Mail']) ?>">
                                    <?= htmlspecialchars($participant['Mail']) ?>
                                </a>
                            </td>
                            <td>
                                <?php if ($participant['Telephone']): ?>
                                    <a href="tel:<?= htmlspecialchars($participant['Telephone']) ?>">
                                        <?= htmlspecialchars($participant['Telephone']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($participant['Date_inscription']): ?>
                                    <?= date('d/m/Y à H:i', strtotime($participant['Date_inscription'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php
                                $nb_invites = $participant['nb_invites'] ?? 0;
                                echo $nb_invites . ' accompagnateur' . ($nb_invites > 1 ? 's' : '');
                                ?>
                            </td>
                            <td>
                                <form action="<?= url('/gestionnaire/events/paiement') ?>" method="post" style="display:inline;">
                                    <input type="hidden" name="id_event" value="<?= $event['id_event_asso'] ?>">
                                    <input type="hidden" name="id_membre" value="<?= $participant['Id_Membre'] ?>">
                                    <input type="hidden" name="nouveau_statut" value="<?= $participant['Paiement'] ? 0 : 1 ?>">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    
                                    <?php if ($participant['Paiement']): ?>
                                        <button type="submit" class="badge badge-success" style="border:none; cursor:pointer;" title="Cliquez pour marquer comme non payé">Payé</button>
                                    <?php else: ?>
                                        <button type="submit" class="badge badge-warning" style="border:none; cursor:pointer;" title="Cliquez pour marquer comme payé">En attente</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="actions actions-centered">
        <a href="<?= url('/gestionnaire/events&type=asso') ?>" class="btn btn-secondary">← Retour aux événements</a>
    </div>
</div>

<?php require_once __DIR__ . '/../../gabarits/pied_de_page.php'; ?>
</body>
</html>