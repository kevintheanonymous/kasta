<?php
$type = $type ?? 'sport';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['titre']) ?> - Kast'Asso</title>
    <link rel="stylesheet" href="<?= asset('css/variables.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/gabarit.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/Accueil.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/evenements_public.css') ?>?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php require_once __DIR__ . '/../gabarits/barre_nav.php'; ?>

    <div class="detail-container">
        <div class="event-header">
            <h1 class="event-title"><?= htmlspecialchars($event['titre']) ?></h1>
            <div class="event-meta">
                <?php if($type === 'sport'): ?>
                    <span class="info-item">Catégorie : <?= htmlspecialchars($event['libelle']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="event-info-box">
            <div class="info-item">
                <span class="info-label">Lieu :</span> <?= htmlspecialchars($event['lieu_texte']) ?>
                <?php if(!empty($event['lieu_maps'])): ?>
                    <a href="<?= htmlspecialchars($event['lieu_maps']) ?>" target="_blank">(Voir sur la carte)</a>
                <?php endif; ?>
            </div>
            
            <div class="info-item">
                <span class="info-label">Date de clôture :</span> <?= date('d/m/Y', strtotime($event['date_cloture'])) ?>
            </div>

            <?php if($type === 'asso'): ?>
                <div class="info-item">
                    <span class="info-label">Date de l'événement :</span> <?= date('d/m/Y à H:i', strtotime($event['date_event_asso'])) ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Tarif :</span> <?= $event['tarif'] > 0 ? number_format($event['tarif'], 2) . ' €' : 'Gratuit' ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="event-body">
            <h3>Description</h3>
            <p><?= nl2br(htmlspecialchars($event['descriptif'])) ?></p>
        </div>

        <div class="actions">
            <?php if($type === 'sport'): ?>
                <a href="<?= url('/membre/inscription/sport?id=' . $event['id_event_sport']) ?>" class="btn btn-primary">S'inscrire aux créneaux</a>
            <?php elseif($type === 'asso'): ?>
                <a href="<?= url('/membre/inscription/asso?id=' . $event['id_event_asso']) ?>" class="btn btn-primary">S'inscrire</a>
                <?php if(!empty($event['url_helloasso'])): ?>
                    <a href="<?= htmlspecialchars($event['url_helloasso']) ?>" target="_blank" class="btn btn-secondary">Payer via HelloAsso</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php require_once __DIR__ . '/../gabarits/pied_de_page.php'; ?>
</body>
</html>
