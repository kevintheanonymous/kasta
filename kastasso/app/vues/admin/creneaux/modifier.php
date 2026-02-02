<?php
$pageTitle = "Modifier un créneau";
require_once __DIR__ . '/../../gabarits/en_tete.php';
require_once __DIR__ . '/../../gabarits/barre_nav.php';
?>
<link rel="stylesheet" href="<?= asset('css/admin_crud.css?v=' . time()) ?>">

<div class="admin-container">
    <h1>Modifier le créneau pour : <?= htmlspecialchars($event['titre']) ?></h1>

    <form action="<?= url('/admin/creneaux/update') ?>" method="POST" class="admin-form" data-date-cloture="<?= htmlspecialchars($event['date_cloture']) ?>">
        <?= champCSRF() ?>
        <input type="hidden" name="id_creneau" value="<?= $creneau['Id_creneau'] ?>">
        <input type="hidden" name="id_event_sportif" value="<?= $event['id_event_sport'] ?>">

        <div class="form-group">
            <label for="type">Type de créneau</label>
            <select name="type" id="type" required class="form-control">
                <option value="preparation" <?= $creneau['Type'] == 'preparation' ? 'selected' : '' ?>>Préparation</option>
                <option value="jour_j" <?= $creneau['Type'] == 'jour_j' ? 'selected' : '' ?>>Jour J</option>
                <option value="rangement" <?= $creneau['Type'] == 'rangement' ? 'selected' : '' ?>>Rangement</option>
            </select>
        </div>

        <div class="form-group">
            <label for="date_creneau">Date</label>
            <input type="date" id="date_creneau" name="date_creneau" value="<?= $creneau['Date_creneau'] ?>" required class="form-control js-date-fr" lang="fr">
        </div>

        <div class="form-row">
            <div class="form-group half">
                <label for="heure_debut">Heure de début</label>
                <input type="time" id="heure_debut" name="heure_debut" value="<?= $creneau['Heure_Debut'] ?>" required class="form-control js-time-fr" lang="fr">
            </div>
            <div class="form-group half">
                <label for="heure_fin">Heure de fin</label>
                <input type="time" id="heure_fin" name="heure_fin" value="<?= $creneau['Heure_Fin'] ?>" required class="form-control js-time-fr" lang="fr">
            </div>
        </div>

        <div class="form-group">
            <label for="commentaire">Commentaire (optionnel)</label>
            <textarea id="commentaire" name="commentaire" class="form-control" rows="3"><?= htmlspecialchars($creneau['Commentaire'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label>Postes disponibles pour ce créneau</label>
            <div class="checkbox-group">
                <?php foreach ($postes as $poste): ?>
                    <label class="checkbox-item">
                        <input type="checkbox" name="postes[]" value="<?= $poste['Id_Poste'] ?>" <?= in_array($poste['Id_Poste'], $postesActuelsIds) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($poste['libelle']) ?> (Niveau <?= $poste['niveau'] ?>)
                    </label>
                <?php endforeach; ?>
            </div>
            <small class="form-text">Cochez les postes à rendre disponibles pour ce créneau</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
            <a href="<?= url('/admin/creneaux&id_event=' . $event['id_event_sport']) ?>" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<script src="<?= asset('js/admin_creneaux.js?v=' . time()) ?>"></script>

<?php require_once __DIR__ . '/../../gabarits/pied_de_page.php'; ?>
</body>
</html>
