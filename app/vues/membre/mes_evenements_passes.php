<?php
// variables du controleur : $evenementsPasses
$userName = $_SESSION['user_name'] ?? 'Membre';

// calcul du total de creneaux effectues
$totalCreneaux = 0;
foreach ($evenementsPasses as $event) {
    $totalCreneaux += (int)$event['nb_creneaux_effectues'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes événements passés - Kast'Asso</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/variables.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/gabarit.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/mes_inscriptions.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/mes_evenements_passes.css') ?>?v=<?= time() ?>">
</head>
<body>
<?php require_once __DIR__ . '/../gabarits/barre_nav.php'; ?>

<main>
    <div class="container">
        <div class="page-header">
            <h1>Mes événements passés</h1>
            <p class="subtitle">Historique des événements sportifs auxquels vous avez participé</p>
        </div>

        <?php if (empty($evenementsPasses)): ?>
            <div class="no-inscriptions">
                <p>Vous n'avez participé à aucun événement pour le moment.</p>
                <p class="info-text">Les événements apparaissent ici une fois que vous avez été marqué comme présent sur au moins un créneau.</p>
                <a href="<?= url('/membre/tableau_de_bord') ?>" class="btn btn-primary">Découvrir les événements</a>
            </div>
        <?php else: ?>
            <div class="stats-summary">
                <div class="stat-box">
                    <span class="stat-number"><?= count($evenementsPasses) ?></span>
                    <span class="stat-label">Événement<?= count($evenementsPasses) > 1 ? 's' : '' ?> participé<?= count($evenementsPasses) > 1 ? 's' : '' ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-number"><?= $totalCreneaux ?></span>
                    <span class="stat-label">Créneau<?= $totalCreneaux > 1 ? 'x' : '' ?> effectué<?= $totalCreneaux > 1 ? 's' : '' ?></span>
                </div>
            </div>

            <div class="evenements-list">
                <?php foreach ($evenementsPasses as $event): ?>
                    <div class="evenement-item">
                        <div class="evenement-info">
                            <div class="evenement-header">
                                <h3 class="evenement-titre"><?= htmlspecialchars($event['Titre']) ?></h3>
                                <?php if (!empty($event['categorie'])): ?>
                                    <span class="evenement-badge"><?= htmlspecialchars($event['categorie']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="evenement-details">
                                <p class="evenement-date">
                                    <strong>Date :</strong> <?= date('d/m/Y', strtotime($event['date_evenement'])) ?>
                                </p>
                                <?php if (!empty($event['Ville']) || !empty($event['Adresse'])): ?>
                                    <p class="evenement-lieu">
                                        <strong>Lieu :</strong>
                                        <?php if (!empty($event['Adresse'])): ?>
                                            <?= htmlspecialchars($event['Adresse']) ?>
                                        <?php endif; ?>
                                        <?php if (!empty($event['Ville'])): ?>
                                            <?= !empty($event['Adresse']) ? ', ' : '' ?><?= htmlspecialchars($event['Ville']) ?>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="evenement-creneaux">
                            <span class="creneaux-count"><?= (int)$event['nb_creneaux_effectues'] ?></span>
                            <span class="creneaux-label">créneau<?= (int)$event['nb_creneaux_effectues'] > 1 ? 'x' : '' ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../gabarits/pied_de_page.php'; ?>
</body>
</html>
