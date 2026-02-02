<?php
// La session est déjà gérée par le contrôleur
$userName = $_SESSION['user_name'] ?? 'Membre';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Kast'Asso</title>
    <link rel="stylesheet" href="<?= asset('css/variables.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/gabarit.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/Accueil.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/tableau_de_bord_membre.css') ?>?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__ . '/../gabarits/barre_nav.php'; ?>

<main>
    <div class="welcome-box">
        <h1>Bonjour <?= htmlspecialchars($userName) ?></h1>
        <p>Heureux de vous revoir sur Kast'Asso. Découvrez les événements à venir et inscrivez-vous !</p>
        </div>

    <div class="container">
        <div class="tabs">
            <button class="tab active" data-filter="tous">Tous les événements</button>
            <button class="tab" data-filter="sport">Événements Sportifs</button>
            <button class="tab" data-filter="asso">Événements Associatifs</button>
        </div>

        <div class="row" id="events-container">
            <?php if(empty($evenements_sport) && empty($evenements_asso)): ?>
                <div class="col-md-12">
                    <div class="no-events-message">
                        <p>Aucun événement disponible pour le moment.</p>
                        <p>Revenez bientôt pour découvrir nos prochains événements !</p>
                    </div>
                </div>
            <?php else: ?>

                <?php foreach($evenements_sport as $e): ?>
                    <div class="col-md-4" data-type="sport">
                        <a href="<?= url('/membre/inscription/sport?id=' . $e['id_event_sport']) ?>" class="event-card-link">
                            <div class="event-card-public">
                                <span class="event-type-badge sport">Sportif</span>
                                <h3><?= nettoyer($e['titre']) ?></h3>
                                <div class="event-description">
                                    <?php
                                    $description = strip_tags(html_entity_decode($e['descriptif']));
                                    echo nettoyer(mb_strlen($description) > 150 ? mb_substr($description, 0, 150) . '...' : $description);
                                    ?>
                                </div>
                                <div class="event-meta">
                                    <p><strong>Clôture inscriptions :</strong> <?= formaterDateHeure($e['date_cloture']) ?></p>
                                    <?php if(isset($e['libelle'])): ?>
                                        <p><strong>Catégorie :</strong> <?= nettoyer($e['libelle']) ?></p>
                                    <?php endif; ?>
                                    <?php if($e['adresse'] || $e['ville']): ?>
                                        <p><strong>Lieu :</strong> <?= nettoyer($e['adresse']) ?><?php if($e['code_postal'] || $e['ville']): ?>, <?= nettoyer($e['code_postal']) ?> <?= nettoyer($e['ville']) ?><?php endif; ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php
                                $isClosed = strtotime($e['date_cloture']) < time();
                                if ($isClosed): ?>
                                    <span class="btn btn-secondary btn-closed">Inscriptions fermées</span>
                                <?php else: ?>
                                    <span class="btn btn-primary">S'inscrire</span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>

                <?php foreach($evenements_asso as $e): ?>
                    <div class="col-md-4" data-type="asso">
                        <a href="<?= url('/membre/inscription/asso?id=' . $e['id_event_asso']) ?>" class="event-card-link">
                            <div class="event-card-public">
                                <span class="event-type-badge asso">Associatif</span>
                                <h3><?= nettoyer($e['titre']) ?></h3>
                                <div class="event-description">
                                    <?php
                                    $description = strip_tags(html_entity_decode($e['descriptif']));
                                    echo nettoyer(mb_strlen($description) > 150 ? mb_substr($description, 0, 150) . '...' : $description);
                                    ?>
                                </div>
                                <div class="event-meta">
                                    <p><strong>Date événement :</strong> <?= formaterDateHeure($e['date_event_asso']) ?></p>
                                    <p><strong>Clôture inscriptions :</strong> <?= formaterDateHeure($e['date_cloture']) ?></p>
                                    <?php if($e['tarif'] > 0): ?>
                                        <p><strong>Tarif :</strong> <?= number_format($e['tarif'], 2, ',', ' ') ?> € - Paiement sur place</p>
                                    <?php endif; ?>
                                    <?php if($e['adresse'] || $e['ville']): ?>
                                        <p><strong>Lieu :</strong> <?= nettoyer($e['adresse']) ?><?php if($e['code_postal'] || $e['ville']): ?>, <?= nettoyer($e['code_postal']) ?> <?= nettoyer($e['ville']) ?><?php endif; ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php
                                $isClosed = strtotime($e['date_cloture']) < time();
                                if ($isClosed): ?>
                                    <span class="btn btn-secondary btn-closed">Inscriptions fermées</span>
                                <?php else: ?>
                                    <span class="btn btn-primary">S'inscrire</span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>

            <?php endif; ?>
        </div>
    </div>
</main>

<script src="<?= asset('js/accueil.js') ?>"></script>
<?php require_once __DIR__ . '/../gabarits/pied_de_page.php'; ?>
</body>
</html>