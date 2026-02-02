<?php
$pageTitle = "Inscrits au créneau";
require_once __DIR__ . '/../../gabarits/en_tete.php';
require_once __DIR__ . '/../../gabarits/barre_nav.php';
?>

<link rel="stylesheet" href="<?= asset('css/admin_inscrits.css') ?>">

<div class="admin-container">
    <div class="header-actions">
        <div>
            <h1>Inscrits au créneau - Marquage des présences</h1>
            <p>
                <strong>Événement :</strong> <?= htmlspecialchars($event['titre']) ?><br>
                <strong>Date :</strong> <?= date('d/m/Y', strtotime($creneau['Date_creneau'])) ?><br>
                <strong>Heure :</strong> <?= date('H:i', strtotime($creneau['Heure_Debut'])) ?> - <?= date('H:i', strtotime($creneau['Heure_Fin'])) ?><br>
                <strong>Type :</strong> <?= htmlspecialchars($creneau['Type']) ?>
            </p>
        </div>
        <div class="actions">
            <a href="<?= url('/admin/creneaux&id_event=' . $event['id_event_sport']) ?>" class="btn btn-secondary">Retour aux créneaux</a>
        </div>
    </div>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if(isset($_SESSION['errors'])): ?>
        <?php foreach($_SESSION['errors'] as $error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <?php if (empty($inscrits)): ?>
        <div class="alert alert-info" style="text-align: center;">
            Aucun inscrit pour ce créneau.
        </div>
    <?php else: ?>
        <form method="POST" action="<?= url('/admin/creneaux/marquer-presences') ?>" id="form-presences">
            <?= champCSRF() ?>
            <input type="hidden" name="id_creneau" value="<?= $creneau['Id_creneau'] ?>">
            <input type="hidden" name="id_event" value="<?= $event['id_event_sport'] ?>">
            <input type="hidden" name="retour_url" value="<?= url('/admin/creneaux/inscrits&id=' . $creneau['Id_creneau']) ?>">

            <div class="table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">
                                <input type="checkbox" id="select-all" title="Tout cocher/décocher">
                            </th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th style="text-align: center;">Statut actuel</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inscrits as $inscrit): ?>
                            <tr>
                                <td style="text-align: center;">
                                    <input type="checkbox"
                                           name="presences[<?= $inscrit['Id_Membre'] ?>]"
                                           value="1"
                                           class="presence-checkbox"
                                           <?= $inscrit['Presence'] ? 'checked' : '' ?>>
                                </td>
                                <td><?= htmlspecialchars($inscrit['Nom']) ?></td>
                                <td><?= htmlspecialchars($inscrit['Prenom']) ?></td>
                                <td><?= htmlspecialchars($inscrit['Mail']) ?></td>
                                <td><?= htmlspecialchars($inscrit['Telephone']) ?></td>
                                <td style="text-align: center;">
                                    <?php if ($inscrit['Presence']): ?>
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

            <div class="form-actions" style="margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: center;">
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Enregistrer les présences
                </button>
                <a href="<?= url('/admin/creneaux&id_event=' . $event['id_event_sport']) ?>" class="btn btn-secondary">Annuler</a>
            </div>
        </form>

        <script>
        // Script pour cocher/décocher toutes les checkboxes
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.presence-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        </script>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../gabarits/pied_de_page.php'; ?>
</body>
</html>
