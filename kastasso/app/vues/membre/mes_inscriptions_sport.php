<?php
// variables du controleur : $evenements
$userName = $_SESSION['user_name'] ?? 'Membre';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes inscriptions sportives - Kast'Asso</title>
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
            <h1>Mes inscriptions aux événements sportifs</h1>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (empty($evenements)): ?>
            <div class="no-inscriptions">
                <p>Vous n'êtes inscrit à aucun événement sportif pour le moment.</p>
                <a href="<?= url('/membre/tableau_de_bord') ?>" class="btn btn-primary">Découvrir les événements</a>
            </div>
        <?php else: ?>
            <div class="inscriptions-list">
                <?php foreach ($evenements as $event): ?>
                    <div class="inscription-event-card">
                        <div class="event-header">
                            <h2><?= nettoyer($event['titre']) ?></h2>
                            <?php if ($event['adresse'] || $event['ville']): ?>
                                <p class="event-lieu">
                                    <?= nettoyer($event['adresse']) ?><?= $event['ville'] ? ', ' . nettoyer($event['ville']) : '' ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="event-actions">
                            <button class="btn btn-primary btn-toggle-creneaux" data-event-id="<?= $event['id_event'] ?>">
                                Mes créneaux (<?= count($event['creneaux']) ?>)
                            </button>
                            <a href="<?= url('/membre/inscription/sport?id=' . $event['id_event']) ?>" class="btn btn-secondary">
                                + Ajouter des créneaux
                            </a>
                            </a>
                            <form action="<?= url('/membre/desinscription/sport/complet') ?>" method="POST" style="display: inline;">
                                <?= champCSRF() ?>
                                <input type="hidden" name="id_event" value="<?= $event['id_event'] ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Voulez-vous vraiment vous désinscrire de tous les créneaux de cet événement ?')">
                                    Me désinscrire
                                </button>
                            </form>
                        </div>

                        <div class="creneaux-list" id="creneaux-<?= $event['id_event'] ?>" style="display: none;">
                            <h3>Mes créneaux</h3>
                            <?php foreach ($event['creneaux'] as $creneau): ?>
                                <div class="creneau-item">
                                    <div class="creneau-info">
                                        <span class="creneau-type <?= htmlspecialchars($creneau['type_creneau']) ?>">
                                            <?= ucfirst(str_replace('_', ' ', $creneau['type_creneau'])) ?>
                                        </span>
                                        <span class="creneau-date">
                                            <?= date('d/m/Y', strtotime($creneau['Date_creneau'])) ?>
                                        </span>
                                        <span class="creneau-horaire">
                                            <?= date('H:i', strtotime($creneau['Heure_Debut'])) ?> - <?= date('H:i', strtotime($creneau['Heure_Fin'])) ?>
                                        </span>
                                    </div>
                                    <form action="<?= url('/membre/desinscription/sport') ?>" method="POST">
                                        <?= champCSRF() ?>
                                        <input type="hidden" name="id_creneau" value="<?= $creneau['Id_creneau'] ?>">
                                        <input type="hidden" name="id_event" value="<?= $event['id_event'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Voulez-vous vraiment vous désinscrire de ce créneau ?')">
                                            Se désinscrire
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
// toggle affichage des creneaux
document.querySelectorAll('.btn-toggle-creneaux').forEach(btn => {
    btn.addEventListener('click', function() {
        const eventId = this.dataset.eventId;
        const creneauxDiv = document.getElementById('creneaux-' + eventId);
        if (creneauxDiv.style.display === 'none') {
            creneauxDiv.style.display = 'block';
            this.textContent = this.textContent.replace('Mes créneaux', 'Masquer les créneaux');
        } else {
            creneauxDiv.style.display = 'none';
            this.textContent = this.textContent.replace('Masquer les créneaux', 'Mes créneaux');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../gabarits/pied_de_page.php'; ?>
</body>
</html>
