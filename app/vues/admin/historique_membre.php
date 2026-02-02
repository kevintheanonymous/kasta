<?php
// Variables du controleur : $membre, $historique
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique de <?= htmlspecialchars($membre['Prenom'] . ' ' . $membre['Nom']) ?> - Admin</title>
    <link rel="stylesheet" href="<?= asset('css/variables.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/gabarit.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/mes_evenements_passes.css') ?>?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

<main>
    <div class="container">
        <div class="page-header">
            <h1>Historique de <?= htmlspecialchars($membre['Prenom'] . ' ' . $membre['Nom']) ?></h1>
            <p class="subtitle">Événements sportifs auxquels le membre a participé</p>
            <a href="<?= url('/admin/membres') ?>" class="btn-back">Retour à la gestion des membres</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['errors'])): ?>
            <?php foreach($_SESSION['errors'] as $error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>

        <?php if (empty($historique)): ?>
            <div class="no-inscriptions">
                <p>Ce membre n'a participé à aucun événement pour le moment.</p>
                <p class="info-text">Les événements apparaissent ici une fois que le membre a été marqué comme présent sur au moins un créneau.</p>
            </div>
        <?php else: ?>
            <div class="stats-summary">
                <div class="stat-box">
                    <span class="stat-number"><?= count($historique) ?></span>
                    <span class="stat-label">Événement<?= count($historique) > 1 ? 's' : '' ?> participé<?= count($historique) > 1 ? 's' : '' ?></span>
                </div>
            </div>

            <div class="evenements-list">
                <?php foreach ($historique as $event): ?>
                    <div class="evenement-item">
                        <div class="evenement-info">
                            <div class="evenement-header">
                                <h3 class="evenement-titre"><?= htmlspecialchars($event['Titre']) ?></h3>
                                <?php if (!empty($event['Categorie'])): ?>
                                    <span class="evenement-badge"><?= htmlspecialchars($event['Categorie']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="evenement-details">
                                <p class="evenement-date">
                                    <strong>Date :</strong> <?= date('d/m/Y', strtotime($event['Date_Evenement'])) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

</body>
</html>
