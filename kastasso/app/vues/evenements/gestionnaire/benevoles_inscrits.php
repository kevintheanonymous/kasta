<?php
/**
 * Vue : Liste des participants inscrits à un événement sportif (Gestionnaire)
 * Affiche tous les créneaux de l'événement avec leurs participants inscrits
 * Variables disponibles : $event, $nombreInscrits, $creneaux
 */
$pageTitle = "Participants inscrits - " . htmlspecialchars($event['titre']);
require_once __DIR__ . '/../../gabarits/en_tete.php';
require_once __DIR__ . '/../../gabarits/barre_nav.php';
?>

<link rel="stylesheet" href="<?= asset('css/admin_inscrits.css?v=' . time()) ?>">
<link rel="stylesheet" href="<?= asset('css/tableau_de_bord_admin.css?v=' . time()) ?>">
<link rel="stylesheet" href="<?= asset('css/gestionnaire_benevoles.css?v=' . time()) ?>">

<div class="admin-container">
    <div class="header-actions">
        <div>
            <h1>Participants inscrits à l'événement</h1>
            <div class="event-info">
                <p><strong>Événement :</strong> <?= htmlspecialchars($event['titre']) ?></p>
                <p><strong>Date de clôture :</strong> <?= date('d/m/Y', strtotime($event['date_cloture'])) ?></p>
                <p><strong>Lieu :</strong> <?= htmlspecialchars($event['ville']) ?></p>
                <p class="nb-inscrits-total">
                    <strong>Total participants inscrits :</strong>
                    <span class="badge badge-primary badge-large">
                        <?= $nombreInscrits ?> participant<?= $nombreInscrits > 1 ? 's' : '' ?>
                    </span>
                </p>
            </div>
        </div>
        <div class="actions">
            <a href="<?= url('/gestionnaire/events&type=sport') ?>" class="btn btn-secondary">← Retour aux événements</a>
            <a href="<?= url('/gestionnaire/events/pdf-participants&id=' . $event['id_event_sport']) ?>" class="btn btn-success btn-with-icon">
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

    <?php if (empty($creneaux)): ?>
        <div class="alert alert-info alert-centered">
            <strong>Aucun créneau créé</strong><br>
            Cet événement n'a pas encore de créneaux.
        </div>
    <?php else: ?>
        <?php foreach ($creneaux as $creneau): ?>
            <div class="creneau-section">
                <div class="creneau-header">
                    <h2>
                        <span class="creneau-type badge
                            <?php
                                $typeClass = 'badge-secondary';
                                if (stripos($creneau['type'], 'préparation') !== false) $typeClass = 'badge-primary';
                                if (stripos($creneau['type'], 'jour') !== false) $typeClass = 'badge-success';
                                if (stripos($creneau['type'], 'rangement') !== false) $typeClass = 'badge-warning';
                                echo $typeClass;
                            ?>">
                            <?= htmlspecialchars($creneau['type']) ?>
                        </span>
                        <?= date('d/m/Y', strtotime($creneau['date'])) ?>
                        <span class="creneau-horaires">
                            <?= date('H:i', strtotime($creneau['heure_debut'])) ?> - <?= date('H:i', strtotime($creneau['heure_fin'])) ?>
                        </span>
                    </h2>
                    <?php if (!empty($creneau['commentaire'])): ?>
                        <p class="creneau-commentaire">
                            <em><?= htmlspecialchars($creneau['commentaire']) ?></em>
                        </p>
                    <?php endif; ?>
                    <p class="creneau-nb-inscrits">
                        <strong><?= count($creneau['benevoles']) ?> participant<?= count($creneau['benevoles']) > 1 ? 's' : '' ?> inscrit<?= count($creneau['benevoles']) > 1 ? 's' : '' ?></strong>
                    </p>
                </div>

                <?php if (empty($creneau['benevoles'])): ?>
                    <p class="text-muted text-centered-italic">
                        Aucun participant inscrit à ce créneau.
                    </p>
                <?php else: ?>
                    <form method="POST" action="<?= url('/gestionnaire/creneaux/marquer-presences') ?>" class="form-presences-creneau">
                        <?= champCSRF() ?>
                        <input type="hidden" name="id_creneau" value="<?= $creneau['id_creneau'] ?>">
                        <input type="hidden" name="id_event" value="<?= $event['id_event_sport'] ?>">
                        <input type="hidden" name="retour_url" value="<?= url('/gestionnaire/events/benevoles&id=' . $event['id_event_sport']) ?>">

                        <div class="table-wrapper">
                            <table class="admin-table">
                            <thead>
                                <tr>
                                    <th class="th-checkbox">
                                        <input type="checkbox" class="select-all-creneau" data-creneau="<?= $creneau['id_creneau'] ?>" title="Tout cocher/décocher">
                                    </th>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Date d'inscription</th>
                                    <th>Préférence de poste</th>
                                    <th class="text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($creneau['benevoles'] as $benevole): ?>
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox"
                                                   name="presences[<?= $benevole['id_membre'] ?>]"
                                                   value="1"
                                                   class="presence-checkbox creneau-<?= $creneau['id_creneau'] ?>"
                                                   <?= $benevole['presence'] ? 'checked' : '' ?>>
                                        </td>
                                        <td><?= htmlspecialchars($benevole['nom']) ?></td>
                                        <td><?= htmlspecialchars($benevole['prenom']) ?></td>
                                        <td>
                                            <a href="mailto:<?= htmlspecialchars($benevole['mail']) ?>">
                                                <?= htmlspecialchars($benevole['mail']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if ($benevole['telephone']): ?>
                                                <a href="tel:<?= htmlspecialchars($benevole['telephone']) ?>">
                                                    <?= htmlspecialchars($benevole['telephone']) ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($benevole['date_inscription']): ?>
                                                <?= date('d/m/Y à H:i', strtotime($benevole['date_inscription'])) ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($benevole['preferences_postes'])): ?>
                                                <?php foreach ($benevole['preferences_postes'] as $poste): ?>
                                                    <span class="badge badge-niveau niveau-<?= $poste['niveau'] ?>">
                                                        <?= htmlspecialchars($poste['libelle']) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-muted">Aucune</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($benevole['presence']): ?>
                                                <span class="badge badge-success">Présent</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Non marqué</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            </table>
                        </div>

                        <div class="submit-presences-container">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-inline">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                Enregistrer les présences de ce créneau
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="actions actions-centered">
        <a href="<?= url('/gestionnaire/events&type=sport') ?>" class="btn btn-secondary">← Retour aux événements</a>
    </div>
</div>

<script>
// Script pour gérer les checkboxes "Tout cocher" par créneau
document.querySelectorAll('.select-all-creneau').forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
        const creneauId = this.getAttribute('data-creneau');
        const checkboxes = document.querySelectorAll('.creneau-' + creneauId);
        checkboxes.forEach(cb => {
            cb.checked = this.checked;
        });
    });
});
</script>

<?php require_once __DIR__ . '/../../gabarits/pied_de_page.php'; ?>
</body>
</html>
