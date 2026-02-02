<?php
$type = $type ?? 'sport';
$formAction = $formAction ?? url('/admin/events/store-with-slots');
$cancelUrl = $cancelUrl ?? url('/admin/events&type=' . $type);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un événement et ses créneaux</title>
    <link rel="stylesheet" href="<?= asset('css/variables.css?v=' . time()) ?>">
    <link rel="stylesheet" href="<?= asset('css/gabarit.css?v=' . time()) ?>">
    <link rel="stylesheet" href="<?= asset('css/tableau_de_bord_admin.css?v=' . time()) ?>">
    <link rel="stylesheet" href="<?= asset('css/formulaire_event.css?v=' . time()) ?>">
    <link rel="stylesheet" href="<?= asset('css/admin_crud.css?v=' . time()) ?>">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
        <script src="<?= asset('js/date_fr.js?v=' . time()) ?>" defer></script>
    <script>
        // Rendre les postes disponibles pour le JavaScript
        window.postesDisponibles = <?= json_encode($postes ?? []) ?>;
    </script>
</head>
<body>
<?php require_once __DIR__ . '/../../gabarits/barre_nav.php'; ?>
<div class="container">
    <h1>Créer un événement sportif et ses créneaux</h1>

    <?php if (!empty($_SESSION['errors'])): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach($_SESSION['errors'] as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']); ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="<?= $formAction ?>" method="post" id="event-with-slots-form">
        <?= champCSRF() ?>
        <input type="hidden" name="type" value="sport">

        <div class="card">
            <h2>Informations de l'événement</h2>
            <div class="form-group">
                <label>Titre :</label>
                <input type="text" name="titre" required placeholder="Marathon de Paris 2025">
            </div>

            <div class="form-group">
                <label>Description :</label>
                <textarea name="description" rows="4" placeholder="Décrivez l'événement..."></textarea>
            </div>

            <div class="form-group">
                <label>Adresse :</label>
                <input type="text" name="adresse" required placeholder="12 rue de la Paix">
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <label>Code Postal :</label>
                    <input type="text" name="code_postal" required pattern="[0-9]{5}" placeholder="75001">
                </div>
                <div class="form-group half">
                    <label>Ville :</label>
                    <input type="text" name="ville" required placeholder="Paris">
                </div>
            </div>

            <div class="form-group">
                <label>Lien Maps (optionnel) :</label>
                <input type="text" name="lieu_maps" placeholder="https://maps.google.com/...">
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <label>Date de visibilité :</label>
                    <input type="date" name="date_visible" required class="js-date-fr" lang="fr">
                </div>
                <div class="form-group half">
                    <label>Date de clôture des inscriptions :</label>
                    <input type="date" name="date_cloture" required class="js-date-fr" lang="fr">
                </div>
            </div>

            <div class="form-group">
                <label>Catégorie :</label>
                <select name="id_categorie" required>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['Id_Categorie_evenement'] ?>"><?= htmlspecialchars($cat['libelle']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="card" id="creneaux-card">
            <h2>Créneaux</h2>
            <p>Ajoutez un ou plusieurs créneaux liés à l'événement.</p>
            <div id="creneaux-container" data-date-cloture-input="date_cloture">
                <!-- lignes dynamiques injectées par JS -->
            </div>
            <div class="slot-actions">
                <button type="button" class="btn btn-secondary" id="add-slot">Ajouter un créneau</button>
            </div>
        </div>

        <div class="actions">
            <a href="<?= $cancelUrl ?>" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Créer l'événement et les créneaux</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../gabarits/pied_de_page.php'; ?>
<script src="<?= asset('js/ev.js?v=' . time()) ?>"></script>
<script src="<?= asset('js/creneaux_dynamiques.js?v=' . time()) ?>"></script>
</body>
</html>
