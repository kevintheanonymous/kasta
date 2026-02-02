<?php
$type = $type ?? 'sport';
$pageTitle = "Gestion des événements (Gestionnaire)";
require __DIR__ . '/../../gabarits/en_tete.php';
require __DIR__ . '/../../gabarits/barre_nav.php';
?>
    <div class="admin-container">
        <h1>Gestionnaire - Événements</h1>
        
        <div class="tabs">
            <a href="<?= url('/gestionnaire/events&type=sport') ?>" class="tab <?= $type === 'sport' ? 'active' : '' ?>">Sportifs</a>
            <a href="<?= url('/gestionnaire/events&type=asso') ?>" class="tab <?= $type === 'asso' ? 'active' : '' ?>">Associatifs</a>
        </div>

        <a href="<?= url('/gestionnaire/tableau_de_bord') ?>" class="back-link">
            ← Retour au tableau de bord
        </a>
        
        <div class="actions">
            <?php if ($type === 'sport'): ?>
                <a href="<?= url('/gestionnaire/events/create-with-slots&type=' . $type) ?>" class="btn btn-primary">Créer événement + créneaux</a>
            <?php else: ?>
                <a href="<?= url('/gestionnaire/events/create&type=' . $type) ?>" class="btn btn-primary">Créer un événement</a>
            <?php endif; ?>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach($_SESSION['errors'] as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>

        <table class="table table-mobile-cards">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Date</th>
                    <th>Lieu</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($events as $event): ?>
                <tr>
                    <td data-label="Titre"><strong><?= htmlspecialchars($event['titre']) ?></strong></td>
                    <td data-label="Date">
                        <?php if($type === 'sport'): ?>
                            Clôture : <?= date('d/m/Y H:i', strtotime($event['date_cloture'])) ?>
                        <?php else: ?>
                            Le <?= date('d/m/Y H:i', strtotime($event['date_event_asso'])) ?>
                        <?php endif; ?>
                    </td>
                    <td data-label="Lieu">
                        <?= htmlspecialchars($event['adresse'] ?? '') ?><?php if(!empty($event['code_postal']) || !empty($event['ville'])): ?>, <?= htmlspecialchars($event['code_postal'] ?? '') ?> <?= htmlspecialchars($event['ville'] ?? '') ?><?php endif; ?>
                        <?php if($type === 'sport' && isset($event['nb_inscrits'])): ?>
                            <br>
                            <span class="badge badge-inscrits" style="margin-top: 0.5rem;">
                                <?= $event['nb_inscrits'] ?> participant<?= $event['nb_inscrits'] > 1 ? 's' : '' ?>
                            </span>
                        <?php endif; ?>
                        <?php if($type === 'asso' && isset($event['nb_inscrits'])): ?>
                            <br>
                            <span class="badge badge-inscrits" style="margin-top: 0.5rem;">
                                <?= $event['nb_inscrits'] ?> participant<?= $event['nb_inscrits'] > 1 ? 's' : '' ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="td-actions" data-label="Actions">
                        <div class="actions-buttons">
                            <?php if($type === 'sport'): ?>
                                <a href="<?= url('/gestionnaire/events/benevoles&id=' . $event['id_event_sport']) ?>" class="btn btn-action btn-success" title="Participants">
                                    Participants
                                </a>
                                <a href="<?= url('/admin/creneaux&id_event=' . $event['id_event_sport']) ?>" class="btn btn-action btn-info" title="Créneaux">
                                    Créneaux
                                </a>
                            <?php endif; ?>

                            <?php if($type === 'asso'): ?>
                                <a href="<?= url('/gestionnaire/events/participants&id=' . $event['id_event_asso']) ?>" class="btn btn-action btn-success" title="Participants">
                                    Participants
                                </a>
                            <?php endif; ?>

                            <a href="<?= url('/gestionnaire/events/edit&type=' . $type . '&id=' . ($type === 'sport' ? $event['id_event_sport'] : $event['id_event_asso'])) ?>" class="btn btn-action btn-warning" title="Modifier">
                                Modifier
                            </a>

                            <form action="<?= url('/gestionnaire/events/delete') ?>" method="post" style="display: inline;" onsubmit="return confirm('Supprimer cet événement ?');">
                                <?= champCSRF() ?>
                                <input type="hidden" name="type" value="<?= $type ?>">
                                <input type="hidden" name="id_event" value="<?= $type === 'sport' ? $event['id_event_sport'] : $event['id_event_asso'] ?>">
                                <button type="submit" class="btn btn-action btn-danger" title="Supprimer">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php require __DIR__ . '/../../gabarits/pied_de_page.php'; ?>