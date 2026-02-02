<?php
$pageTitle = "Gestion des Créneaux - " . htmlspecialchars($event['titre']);
require_once __DIR__ . '/../../gabarits/en_tete.php';
require_once __DIR__ . '/../../gabarits/barre_nav.php';
?>
<link rel="stylesheet" href="<?= asset('css/admin_crud.css?v=' . time()) ?>">

<div class="admin-container">
    <div class="header-actions">
        <h1>Créneaux pour : <?= htmlspecialchars($event['titre']) ?></h1>
        <div class="actions">
            <a href="<?= url('/admin/events') ?>" class="btn btn-secondary">Retour aux événements</a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger">
            <?php foreach ($_SESSION['errors'] as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
            <?php unset($_SESSION['errors']); ?>
        </div>
    <?php endif; ?>

    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Type</th>
                    <th>Commentaire</th>
                    <th>Inscrits</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($creneaux)): ?>
                    <tr>
                        <td colspan="6" class="text-center">Aucun créneau défini pour cet événement.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($creneaux as $creneau): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($creneau['Date_creneau'])) ?></td>
                            <td><?= date('H:i', strtotime($creneau['Heure_Debut'])) ?> - <?= date('H:i', strtotime($creneau['Heure_Fin'])) ?></td>
                            <td>
                                <?php
                                $types = [
                                    'preparation' => 'Préparation',
                                    'jour_j' => 'Jour J',
                                    'rangement' => 'Rangement'
                                ];
                                echo $types[$creneau['Type']] ?? $creneau['Type'];
                                ?>
                            </td>
                            <td><?= htmlspecialchars($creneau['Commentaire'] ?? '') ?></td>
                            <td>
                                <a href="<?= url('/admin/creneaux/inscrits&id=' . $creneau['Id_creneau']) ?>">
                                    <?= $creneau['nb_inscrits'] ?> inscrit(s)
                                </a>
                            </td>
                            <td class="actions-cell">
                                <a href="<?= url('/admin/creneaux/edit&id=' . $creneau['Id_creneau']) ?>" class="btn btn-sm btn-warning">Modifier</a>

                                <form action="<?= url('/admin/creneaux/delete') ?>" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce créneau ?');" class="inline-form">
                                    <?= champCSRF() ?>
                                    <input type="hidden" name="id_creneau" value="<?= $creneau['Id_creneau'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card card-spaced">
        <h2>Ajouter un créneau</h2>
        <form action="<?= url('/admin/creneaux/store') ?>" method="POST" class="admin-form" data-date-cloture="<?= htmlspecialchars($event['date_cloture']) ?>">
            <?= champCSRF() ?>
            <input type="hidden" name="id_event_sportif" value="<?= $event['id_event_sport'] ?>">

            <div class="form-row">
                <div class="form-group half">
                    <label for="type">Type de créneau</label>
                    <select name="type" id="type" required class="form-control">
                        <option value="preparation">Préparation</option>
                        <option value="jour_j">Jour J</option>
                        <option value="rangement">Rangement</option>
                    </select>
                </div>
                <div class="form-group half">
                    <label for="date_creneau">Date</label>
                        <input type="date" id="date_creneau" name="date_creneau" required class="form-control js-date-fr" lang="fr">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <label for="heure_debut">Heure de début</label>
                        <input type="time" id="heure_debut" name="heure_debut" required class="form-control js-time-fr" lang="fr">
                </div>
                <div class="form-group half">
                    <label for="heure_fin">Heure de fin</label>
                        <input type="time" id="heure_fin" name="heure_fin" required class="form-control js-time-fr" lang="fr">
                </div>
            </div>

            <div class="form-group">
                <label for="commentaire">Commentaire (optionnel)</label>
                <textarea id="commentaire" name="commentaire" class="form-control" rows="2"></textarea>
            </div>

            <div class="form-group">
                <label>Postes disponibles pour ce créneau</label>
                <div class="checkbox-group">
                    <?php foreach ($postes as $poste): ?>
                        <label class="checkbox-item">
                            <input type="checkbox" name="postes[]" value="<?= $poste['Id_Poste'] ?>">
                            <?= htmlspecialchars($poste['libelle']) ?> (Niveau <?= $poste['niveau'] ?>)
                        </label>
                    <?php endforeach; ?>
                </div>
                <small class="form-text">Cochez les postes à rendre disponibles pour ce créneau</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Ajouter ce créneau</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../gabarits/pied_de_page.php'; ?>
<script src="<?= asset('js/admin_creneaux.js?v=' . time()) ?>"></script>
</body>
</html>
