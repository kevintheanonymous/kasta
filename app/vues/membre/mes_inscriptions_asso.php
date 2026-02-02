<?php
// variables du controleur : $mes_inscriptions_asso
$userName = $_SESSION['user_name'] ?? 'Membre';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes inscriptions associatives - Kast'Asso</title>
    <link rel="stylesheet" href="<?= asset('css/variables.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/gabarit.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/mes_inscriptions.css') ?>?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__ . '/../gabarits/barre_nav.php'; ?>

<main>
    <div class="container">
        <div class="page-header">
            <h1>Mes inscriptions aux événements associatifs</h1>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (empty($mes_inscriptions_asso)): ?>
            <div class="no-inscriptions">
                <p>Vous n'êtes inscrit à aucun événement associatif pour le moment.</p>
                <a href="<?= url('/membre/tableau_de_bord') ?>" class="btn btn-primary">Découvrir les événements</a>
            </div>
        <?php else: ?>
            <div class="inscriptions-list">
                <?php foreach ($mes_inscriptions_asso as $insc): ?>
                    <div class="inscription-event-card">
                        <div class="event-header">
                            <h2><?= nettoyer($insc['titre']) ?></h2>
                            <p class="event-date">Le <?= date('d/m/Y', strtotime($insc['date_event'])) ?></p>
                            <?php if ($insc['adresse'] || $insc['ville']): ?>
                                <p class="event-lieu">
                                    <?= nettoyer($insc['adresse']) ?><?= $insc['ville'] ? ', ' . nettoyer($insc['ville']) : '' ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="accompagnateurs-section">
                            <h3>Accompagnateurs</h3>
                            <p class="info-text">Vous avez actuellement <strong><?= (int)($insc['nb_invites'] ?? 0) ?></strong> accompagnateur(s).</p>
                            <a href="<?= url('/membre/inscription/asso?id=' . $insc['id_event'] . '&mode=edition') ?>" class="btn btn-info">
                                Modifier mes accompagnateurs
                            </a>
                        </div>

                        <div class="event-actions">
                            <a href="<?= url('/membre/inscription/asso?id=' . $insc['id_event']) ?>" class="btn btn-primary">
                                Voir l'événement
                            </a>
                            <form action="<?= url('/membre/desinscription/asso') ?>" method="POST" style="display: inline;">
                                <?= champCSRF() ?>
                                <input type="hidden" name="id_event" value="<?= $insc['id_event'] ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Voulez-vous vraiment vous désinscrire de cet événement ?')">
                                    Me désinscrire
                                </button>
                            </form>
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
