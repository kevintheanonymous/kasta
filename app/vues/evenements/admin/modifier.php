<?php
$type = $type ?? 'sport';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un événement</title>
    <link rel="stylesheet" href="<?= asset('css/variables.css?v=' . time()) ?>">
    <link rel="stylesheet" href="<?= asset('css/gabarit.css?v=' . time()) ?>">
    <link rel="stylesheet" href="<?= asset('css/tableau_de_bord_admin.css?v=' . time()) ?>">
    <link rel="stylesheet" href="<?= asset('css/formulaire_event.css?v=' . time()) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
    <script src="<?= asset('js/date_fr.js?v=' . time()) ?>" defer></script>
</head>
<body>
    <?php require_once __DIR__ . '/../../gabarits/barre_nav.php'; ?>
    <div class="container">
        <h1>Modifier l'événement <?= $type === 'sport' ? 'Sportif' : 'Associatif' ?></h1>
        
        <form action="<?= url('/admin/events/update') ?>" method="post">
            <?= champCSRF() ?>
            <input type="hidden" name="type" value="<?= $type ?>">
            <input type="hidden" name="id_event" value="<?= $type === 'sport' ? $event['id_event_sport'] : $event['id_event_asso'] ?>">
            
            <div class="form-group">
                <label>Titre :</label>
                <input type="text" name="titre" value="<?= htmlspecialchars($event['titre']) ?>" required>
            </div>

            <div class="form-group">
                <label>Description :</label>
                <textarea name="description" rows="5"><?= htmlspecialchars($event['descriptif']) ?></textarea>
            </div>

            <div class="form-group">
                <label>Adresse :</label>
                <input type="text" name="adresse" value="<?= htmlspecialchars($event['adresse'] ?? '') ?>" required placeholder="12 rue de la Paix">
            </div>

            <div class="form-group">
                <label>Code Postal :</label>
                <input type="text" name="code_postal" value="<?= htmlspecialchars($event['code_postal'] ?? '') ?>" required pattern="[0-9]{5}" placeholder="75001">
            </div>

            <div class="form-group">
                <label>Ville :</label>
                <input type="text" name="ville" value="<?= htmlspecialchars($event['ville'] ?? '') ?>" required placeholder="Paris">
            </div>

            <div class="form-group">
                <label>Lien Maps (optionnel) :</label>
                <input type="text" name="lieu_maps" value="<?= htmlspecialchars($event['lieu_maps']) ?>">
            </div>

            <div class="form-group">
                <label>Date de visibilité :</label>
                <input type="date" name="date_visible" value="<?= substr($event['date_visible'], 0, 10) ?>" required class="js-date-fr" lang="fr">
            </div>

            <div class="form-group">
                <label>Date de clôture des inscriptions :</label>
                <input type="date" name="date_cloture" value="<?= substr($event['date_cloture'], 0, 10) ?>" required class="js-date-fr" lang="fr">
            </div>

            <?php if($type === 'sport'): ?>
                <div class="form-group">
                    <label>Catégorie :</label>
                    <select name="id_categorie" required>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['Id_Categorie_evenement'] ?>" <?= $cat['Id_Categorie_evenement'] == $event['id_cat_event'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['libelle']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label>Date de l'événement :</label>
                    <input type="datetime-local" name="date_event_asso" value="<?= date('Y-m-d\TH:i', strtotime($event['date_event_asso'])) ?>" required class="js-datetime-fr" lang="fr">
                </div>
                <div class="form-group">
                    <label>Tarif (€) :</label>
                    <input type="number" step="0.01" name="tarif" value="<?= $event['tarif'] ?>">
                </div>
                <div class="form-group">
                    <label>Lien HelloAsso :</label>
                    <input type="text" name="url_helloasso" value="<?= htmlspecialchars($event['url_helloasso']) ?>">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="prive" value="1" <?= $event['prive'] == 1 ? 'checked' : '' ?>> Événement privé (réservé aux adhérents)
                    </label>
                </div>
            <?php endif; ?>

            <div class="actions">
                <a href="<?= url('/admin/events&type=' . $type) ?>" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            </div>
        </form>
    </div>
    <?php require_once __DIR__ . '/../../gabarits/pied_de_page.php'; ?>
    <script src="<?= asset('js/ev.js') ?>"></script>
</body>
</html>
