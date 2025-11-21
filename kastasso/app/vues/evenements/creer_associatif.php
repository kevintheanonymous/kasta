<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un événement associatif - Kast'Asso</title>
    <link rel="stylesheet" href="/kastasso/public/css/formulaire_event.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav>
        <ul>
            <li><a href="index.php?path=/deconnexion">Déconnexion</a></li>
            <li><a href="index.php?path=/admin/tableau_de_bord">Dashboard</a></li>
            <div id="logo">
                <li id="KASTA"><a>KASTA</a></li>
                <li id="CROSSFIT"><a class="color-orange">CROSSFIT</a></li>
            </div>
        </ul>
    </nav>

    <h1>Créer un événement associatif</h1>

    <?php if (isset($_SESSION['erreurs']) && !empty($_SESSION['erreurs'])): ?>
        <div class="message-error">
            <?php foreach ($_SESSION['erreurs'] as $erreur): ?>
                <p>❌ <?= htmlspecialchars($erreur) ?></p>
            <?php endforeach; ?>
        </div>
        <?php unset($_SESSION['erreurs']); ?>
    <?php endif; ?>

    <form action="index.php?path=/admin_event_associatif_traiter" method="post" enctype="multipart/form-data">
        <label>Titre * :
            <input type="text" name="titre" required value="<?= htmlspecialchars($_SESSION['form_data']['titre'] ?? '') ?>">
        </label>

        <label>Date de l'événement * :
            <input type="date" name="date_evenement" required value="<?= $_SESSION['form_data']['date_evenement'] ?? '' ?>">
        </label>

        <label>Image/Bannière de l'événement :
            <input type="file" name="image" accept="image/*">
        </label>

        <label>Adresse * :
            <input type="text" name="adresse" required value="<?= htmlspecialchars($_SESSION['form_data']['adresse'] ?? '') ?>">
        </label>

        <label>Lien Maps :
            <input type="url" name="lien_maps" placeholder="https://maps.google.com/..." value="<?= htmlspecialchars($_SESSION['form_data']['lien_maps'] ?? '') ?>">
        </label>

        <label>Date de visibilité :
            <input type="datetime-local" name="date_visibilite" value="<?= $_SESSION['form_data']['date_visibilite'] ?? date('Y-m-d\TH:i') ?>">
        </label>

        <label>Date de clôture des inscriptions :
            <input type="datetime-local" name="date_cloture" value="<?= $_SESSION['form_data']['date_cloture'] ?? '' ?>">
        </label>

        <label>Tarif (pour non-bénévoles) :
            <input type="number" name="tarif" step="0.01" value="<?= $_SESSION['form_data']['tarif'] ?? '9.99' ?>" min="0">
        </label>

        <label>URL HelloAsso (paiement) :
            <input type="url" name="url_helloasso" placeholder="https://www.helloasso.com/..." value="<?= htmlspecialchars($_SESSION['form_data']['url_helloasso'] ?? '') ?>">
        </label>

        <label>
            <input type="checkbox" name="prive" value="1" <?= isset($_SESSION['form_data']['prive']) ? 'checked' : '' ?>>
            Événement privé (réservé aux membres)
        </label>

        <label>Descriptif :
            <textarea name="descriptif" rows="5" placeholder="Description de l'événement..."><?= htmlspecialchars($_SESSION['form_data']['descriptif'] ?? '') ?></textarea>
        </label>

        <input type="reset" value="Vider le formulaire">
        <input type="submit" value="Créer l'événement associatif">
    </form>

    <script src="js/validation.js"></script>
</body>
</html>
<?php unset($_SESSION['form_data']); ?>
