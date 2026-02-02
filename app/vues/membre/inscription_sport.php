<?php
// variables du controleur : $evenement, $creneaux, $creneauxInscrits, $inscriptionsClosed
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - <?= nettoyer($evenement['titre']) ?> - Kast'Asso</title>
    <link rel="stylesheet" href="<?= asset('css/variables.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/gabarit.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/inscription_membre.css') ?>?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__ . '/../gabarits/barre_nav.php'; ?>

<main>
    <div class="inscription-container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['info'])): ?>
            <div class="alert alert-info"><?= htmlspecialchars($_SESSION['info']) ?></div>
            <?php unset($_SESSION['info']); ?>
        <?php endif; ?>

        <div class="event-header">
            <span class="event-badge">Sportif</span>
            <h1><?= nettoyer($evenement['titre']) ?></h1>
            <div class="event-meta">
                <?php if (!empty($evenement['libelle'])): ?>
                    <p><strong>Catégorie :</strong> <?= nettoyer($evenement['libelle']) ?></p>
                <?php endif; ?>
                <p><strong>Clôture des inscriptions :</strong> <?= formaterDateHeure($evenement['date_cloture']) ?></p>
                <?php if ($evenement['adresse']): ?>
                    <p><strong>Adresse :</strong> <?= nettoyer($evenement['adresse']) ?></p>
                <?php endif; ?>
                <?php if ($evenement['code_postal']): ?>
                    <p><strong>Code postal :</strong> <?= nettoyer($evenement['code_postal']) ?></p>
                <?php endif; ?>
                <?php if ($evenement['ville']): ?>
                    <p><strong>Ville :</strong> <?= nettoyer($evenement['ville']) ?></p>
                <?php endif; ?>
                <?php if (!empty($evenement['lieu_maps'])): ?>
                    <a href="<?= nettoyer($evenement['lieu_maps']) ?>" target="_blank" class="maps-link">
                        Voir sur Google Maps
                    </a>
                <?php endif; ?>
            </div>
            <?php if (!empty($evenement['descriptif'])): ?>
                <div class="event-description">
                    <?= nl2br(nettoyer(html_entity_decode($evenement['descriptif']))) ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($inscriptionsClosed): ?>
            <div class="closed-banner">
                Les inscriptions sont closes pour cet événement.
            </div>
        <?php endif; ?>

        <div class="creneaux-section">
            <h2>Créneaux disponibles</h2>

            <?php if (empty($creneaux)): ?>
                <div class="no-creneaux">
                    <p>Aucun créneau n'est disponible pour cet événement.</p>
                </div>
            <?php else: ?>
                <?php foreach ($creneaux as $creneau): ?>
                    <?php $estInscrit = in_array($creneau['Id_creneau'], $creneauxInscrits); ?>
                    <div class="creneau-card <?= $estInscrit ? 'inscrit' : '' ?>">
                        <div class="creneau-info">
                            <span class="creneau-type <?= htmlspecialchars($creneau['Type']) ?>">
                                <?= ucfirst(str_replace('_', ' ', $creneau['Type'])) ?>
                            </span>
                            <p class="creneau-datetime">
                                <?= date('d/m/Y', strtotime($creneau['Date_creneau'])) ?>
                                de <?= date('H:i', strtotime($creneau['Heure_Debut'])) ?>
                                à <?= date('H:i', strtotime($creneau['Heure_Fin'])) ?>
                            </p>
                            <?php if (!empty($creneau['Commentaire'])): ?>
                                <p class="creneau-commentaire"><?= nettoyer($creneau['Commentaire']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($creneau['postes_disponibles'])): ?>
                                <div class="postes-disponibles">
                                    <strong>Postes disponibles :</strong>
                                    <?php foreach ($creneau['postes_disponibles'] as $index => $poste): ?>
                                        <?= nettoyer($poste['libelle']) ?><?= $index < count($creneau['postes_disponibles']) - 1 ? ', ' : '' ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="creneau-actions">
                            <?php if ($estInscrit): ?>
                                <a href="<?= url('/membre/mes_inscriptions_sport') ?>" class="btn-gerer-inscription">
                                    Gérer mon inscription
                                </a>
                            <?php else: ?>
                                <?php if (!$inscriptionsClosed): ?>
                                    <form action="<?= url('/membre/inscription/sport') ?>" method="POST" class="form-inscription-creneau">
                                        <?= champCSRF() ?>
                                        <input type="hidden" name="id_event" value="<?= $evenement['id_event_sport'] ?>">
                                        <input type="hidden" name="creneaux[]" value="<?= $creneau['Id_creneau'] ?>">
                                        <?php if (!empty($creneau['postes_disponibles'])): ?>
                                            <div class="preference-poste-container">
                                                <label>Mes préférences de poste :</label>
                                                <div class="checkbox-group postes-preferences">
                                                    <?php foreach ($creneau['postes_disponibles'] as $poste): ?>
                                                        <label class="checkbox-item">
                                                            <input type="checkbox" name="preferences_postes[]" value="<?= $poste['Id_Poste'] ?>">
                                                            <?= nettoyer($poste['libelle']) ?> (Niveau <?= $poste['niveau'] ?>)
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                                <small class="form-text">Cochez un ou plusieurs postes selon vos préférences</small>
                                            </div>
                                        <?php endif; ?>
                                        <button type="submit" class="btn-inscrire">M'inscrire</button>
                                    </form>
                                <?php else: ?>
                                    <span class="btn-disabled">Inscriptions fermées</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="form-actions">
                    <a href="<?= url('/membre/mes_inscriptions_sport') ?>" class="btn btn-secondary">Mes inscriptions</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../gabarits/pied_de_page.php'; ?>
</body>
</html>
