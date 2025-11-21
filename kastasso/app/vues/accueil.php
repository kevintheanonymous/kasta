<?php

require_once dirname(__DIR__) . '/fonctions_commun.php';

$config = dirname(__DIR__, 2) . '/config/config.php';
if ($config && file_exists($config)) {
    require_once $config;
}


$evenements_sport = obtenirTousEvenementsSport();
$evenements_asso  = obtenirTousEvenementsAsso();

include 'gabarits/en_tete.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link rel="stylesheet" href="/kastasso/public/css/Accueil.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet"> 
</head>
<div class="container">
    <!-- Hero Section -->
    <div class="hero-section">
        <h1>Bienvenue chez KASTA CROSSFIT</h1>
        <p class="hero-subtitle">Découvrez nos événements sportifs et associatifs</p>
    </div>

    <!-- Tabs pour filtrer Sport / Asso / Tous -->
    <div class="tabs">
        <button class="tab active" data-filter="tous">Tous les événements</button>
        <button class="tab" data-filter="sport">Événements Sportifs</button>
        <button class="tab" data-filter="asso">Événements Associatifs</button>
    </div>

    <!-- Grille d'événements -->
    <div class="row" id="events-container">
        <?php
        $total_events = count($evenements_sport) + count($evenements_asso);

        if($total_events === 0): ?>
            <div class="col-md-12">
                <div class="no-events-message">
                    <p>Aucun événement disponible pour le moment.</p>
                    <p>Revenez bientôt pour découvrir nos prochains événements !</p>
                </div>
            </div>
        <?php else: ?>

            <?php foreach($evenements_sport as $e): ?>
                <div class="col-md-4" data-type="sport">
                    <div class="event-card-public">
                        <span class="event-type-badge sport">Sportif</span>
                        <h3><?= nettoyer($e['titre']) ?></h3>
                        <div class="event-description">
                            <?php
                            $description = strip_tags($e['descriptif']);
                            echo nettoyer(mb_strlen($description) > 150 ? mb_substr($description, 0, 150) . '...' : $description);
                            ?>
                        </div>
                        <div class="event-meta">
                            <p><strong>Clôture inscriptions :</strong> <?= formaterDateHeure($e['date_cloture']) ?></p>
                            <?php if(isset($e['categorie'])): ?>
                                <p><strong>Catégorie :</strong> <?= nettoyer($e['categorie']) ?></p>
                            <?php endif; ?>
                            <?php if($e['lieu_texte']): ?>
                                <p><strong>Lieu :</strong> <?= nettoyer($e['lieu_texte']) ?></p>
                            <?php endif; ?>
                        </div>
                        <a href="connexion.php" class="btn btn-secondary btn-view-details">Se connecter pour participer</a>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php foreach($evenements_asso as $e): ?>
                <div class="col-md-4" data-type="asso">
                    <div class="event-card-public">
                        <span class="event-type-badge asso">Associatif</span>
                        <h3><?= nettoyer($e['titre']) ?></h3>
                        <div class="event-description">
                            <?php
                            $description = strip_tags($e['descriptif']);
                            echo nettoyer(mb_strlen($description) > 150 ? mb_substr($description, 0, 150) . '...' : $description);
                            ?>
                        </div>
                        <div class="event-meta">
                            <p><strong>Date événement :</strong> <?= formaterDateHeure($e['date_event_asso']) ?></p>
                            <p><strong>Clôture inscriptions :</strong> <?= formaterDateHeure($e['date_cloture']) ?></p>
                            <?php if($e['tarif'] > 0): ?>
                                <p><strong>Tarif :</strong> <?= number_format($e['tarif'], 2, ',', ' ') ?> € - Paiement sur place</p>
                            <?php endif; ?>
                            <?php if($e['lieu_texte']): ?>
                                <p><strong>Lieu :</strong> <?= nettoyer($e['lieu_texte']) ?></p>
                            <?php endif; ?>
                        </div>
                        <a href="connexion.php" class="btn btn-secondary btn-view-details">Se connecter pour participer</a>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab');
    const eventCards = document.querySelectorAll('[data-type]');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');

            // Mettre à jour les onglets actifs
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // Filtrer les événements
            eventCards.forEach(card => {
                const eventType = card.getAttribute('data-type');

                if(filter === 'tous') {
                    card.classList.remove('hidden');
                } else if(eventType === filter) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        });
    });
});
</script>

<?php include 'gabarits/pied_de_page.php'; ?>
