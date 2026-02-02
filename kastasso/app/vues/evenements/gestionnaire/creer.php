<?php
$type = $type ?? 'sport';
// Récupérer les données du formulaire sauvegardées en cas d'erreur
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un événement</title>
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
        <h1>Créer un événement <?= $type === 'sport' ? 'Sportif' : 'Associatif' ?></h1>
        
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
        
       <form action="<?= url('/gestionnaire/events/store') ?>" method="post">
            <?= champCSRF() ?>
            <input type="hidden" name="type" value="<?= $type ?>">
            
            <div class="form-group">
                <label>Titre :</label>
                <input type="text" name="titre" required placeholder="Marathon de Toulouse 2025" value="<?= htmlspecialchars($formData['titre'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Description :</label>
                <textarea name="description" rows="5" placeholder="Décrivez l'événement en quelques lignes..."><?= htmlspecialchars($formData['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Adresse :</label>
                <input type="text" name="adresse" required placeholder="12 rue de la Paix" value="<?= htmlspecialchars($formData['adresse'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Code Postal :</label>
                <input type="text" name="code_postal" required pattern="[0-9]{5}" placeholder="75001" value="<?= htmlspecialchars($formData['code_postal'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Ville :</label>
                <input type="text" name="ville" required placeholder="Paris" value="<?= htmlspecialchars($formData['ville'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Lien Maps (optionnel) :</label>
                <input type="text" name="lieu_maps" placeholder="https://maps.google.com/..." value="<?= htmlspecialchars($formData['lieu_maps'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Date de visibilité :</label>
                <input type="date" name="date_visible" required class="js-date-fr" lang="fr" value="<?= htmlspecialchars($formData['date_visible'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Date de clôture des inscriptions :</label>
                <input type="date" name="date_cloture" required class="js-date-fr" lang="fr" value="<?= htmlspecialchars($formData['date_cloture'] ?? '') ?>">
            </div>

            <?php if($type === 'sport'): ?>
                <div class="form-group">
                    <label>Catégorie :</label>
                    <select name="id_categorie" required>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['Id_Categorie_evenement'] ?>" <?= ($formData['id_categorie'] ?? '') == $cat['Id_Categorie_evenement'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['libelle']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label>Date de l'événement :</label>
                    <input type="datetime-local" name="date_event_asso" required class="js-datetime-fr" lang="fr" value="<?= htmlspecialchars($formData['date_event_asso'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Tarif (€) :</label>
                    <input type="number" step="0.01" name="tarif" value="<?= htmlspecialchars($formData['tarif'] ?? '0') ?>" placeholder="9.99">
                </div>
                <div class="form-group">
                    <label>Lien HelloAsso :</label>
                    <input type="text" name="url_helloasso" placeholder="https://www.helloasso.com/..." value="<?= htmlspecialchars($formData['url_helloasso'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="prive" value="1" <?= !empty($formData['prive']) ? 'checked' : '' ?>> Événement privé (réservé aux adhérents)
                    </label>
                </div>
            <?php endif; ?>

    <div class="actions">
        <a href="<?= url('/gestionnaire/events&type=' . $type) ?>" class="btn btn-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">Créer</button>
    </div>
    </form>
    </div>
    <?php require_once __DIR__ . '/../../gabarits/pied_de_page.php'; ?>
    <script src="<?= asset('js/ev.js') ?>"></script>
</body>
</html>
