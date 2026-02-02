<?php
// variables du controleur : $evenement, $inscription, $inscriptionsClosed, $userEmail, $userNom, $userPrenom
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
    <link rel="stylesheet" href="<?= asset('css/inscription_asso_dynamique.css') ?>?v=<?= time() ?>">
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
            <span class="event-badge">Associatif</span>
            <?php if ($evenement['prive']): ?>
                <span class="event-badge prive">Privé (Adhérents)</span>
            <?php endif; ?>
            <h1><?= nettoyer($evenement['titre']) ?></h1>
            <div class="event-meta">
                <p><strong>Date de l'événement :</strong> <?= formaterDateHeure($evenement['date_event_asso']) ?></p>
                <p><strong>Clôture des inscriptions :</strong> <?= formaterDateHeure($evenement['date_cloture']) ?></p>
                <?php if ($evenement['adresse'] || $evenement['ville']): ?>
                    <p><strong>Lieu :</strong> <?= nettoyer($evenement['adresse']) ?><?php if($evenement['code_postal'] || $evenement['ville']): ?>, <?= nettoyer($evenement['code_postal']) ?> <?= nettoyer($evenement['ville']) ?><?php endif; ?></p>
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

            <div class="tarif-box <?= $evenement['tarif'] == 0 ? 'gratuit' : '' ?>">
                <?php if ($evenement['tarif'] > 0): ?>
                    <h3>Tarif : <?= number_format($evenement['tarif'], 2, ',', ' ') ?> €</h3>
                    <p>Le tarif dépend de votre participation aux événements sportifs au cours des 12 derniers mois</p>
                <?php else: ?>
                    <h3>Événement gratuit</h3>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($inscriptionsClosed): ?>
            <div class="closed-banner">
                Les inscriptions sont closes pour cet événement.
            </div>
        <?php endif; ?>

        <div class="inscription-section">
            <?php if ($inscription && $mode !== 'edition'): ?>
                <!-- Déjà inscrit - redirection vers page gestion -->
                <div class="inscription-existante">
                    <h3>Vous êtes déjà inscrit à cet événement</h3>
                    <p>Gérez votre inscription et modifiez le nombre d'accompagnateurs depuis votre espace de gestion.</p>
                    <div class="form-actions">
                        <a href="<?= url('/membre/mes_inscriptions_asso') ?>" class="btn-gerer-inscription">Gérer mon inscription</a>
                    </div>
                </div>

            <?php else: ?>
                <!-- Pas encore inscrit ou mode edition -->
                <?php if (!$inscriptionsClosed): ?>
                    <h2><?= $mode === 'edition' ? 'Modifier mon inscription' : "S'inscrire à cet événement" ?></h2>

                    <form action="<?= url('/membre/inscription/asso') ?>" method="POST" id="inscription-form"
                          data-mode="<?= isset($mode) ? htmlspecialchars($mode) : 'creation' ?>"
                          data-tarif-membre="<?= isset($tarifMembre) ? htmlspecialchars($tarifMembre) : '0' ?>"
                          data-accompagnateurs='<?= isset($accompagnateurs) ? htmlspecialchars(json_encode($accompagnateurs), ENT_QUOTES, 'UTF-8') : '[]' ?>'>
                        <?= champCSRF() ?>
                        <input type="hidden" name="id_event" value="<?= $evenement['id_event_asso'] ?>">
                        <input type="hidden" name="tarif_event" value="<?= $evenement['tarif'] ?>">
                        <input type="hidden" name="nb_invites" id="nb_invites_hidden" value="0">
                        <input type="hidden" name="accompagnateurs_data" id="accompagnateurs_data" value="[]">

                        <!-- Section membre principal -->
                        <div class="participant-section membre-principal">
                            <h3>Vos informations</h3>
                            <div class="participant-row">
                                <div class="participant-info">
                                    <div class="form-group">
                                        <label>Nom</label>
                                        <input type="text" value="<?= nettoyer($userNom) ?>" disabled>
                                    </div>
                                    <div class="form-group">
                                        <label>Prénom</label>
                                        <input type="text" value="<?= nettoyer($userPrenom) ?>" disabled>
                                    </div>
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" value="<?= nettoyer($userEmail) ?>" disabled>
                                    </div>
                                </div>
                                <div class="participant-actions">
                                    <div class="tarif-display tarif-membre-principal" data-index="membre" data-email="<?= nettoyer($userEmail) ?>">
                                        <span class="tarif-loading">Calcul du tarif en cours...</span>
                                        <span class="tarif-result" style="display: none;">
                                            <span class="tarif-montant">—</span>
                                            <span class="tarif-raison"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section accompagnateurs -->
                        <div class="accompagnateurs-section">
                            <h3>Accompagnateurs</h3>
                            <p class="info-text">Si vous venez accompagné, ajoutez vos accompagnateurs ci-dessous. Leur tarif sera calculé individuellement.</p>

                            <div id="accompagnateurs-container">
                                <!-- Les accompagnateurs seront ajoutés ici dynamiquement -->
                            </div>

                            <button type="button" id="btn-ajouter-accompagnateur" class="btn-ajouter">
                                + Ajouter un accompagnateur
                            </button>
                        </div>

                        <!-- Récapitulatif du montant total -->
                        <div class="recapitulatif-tarif">
                            <div class="total-row">
                                <span class="total-label">Montant total à payer :</span>
                                <span class="total-montant" id="montant-total">—</span>
                            </div>
                        </div>

                        <!-- Section HelloAsso pour paiement en ligne -->
                        <?php if (!empty($evenement['url_helloasso'])): ?>
                        <div class="helloasso-section">
                            <h3>Paiement en ligne</h3>
                            <p class="helloasso-info">
                                Pour régler votre participation, utilisez le lien ci-dessous :
                            </p>
                            <a href="<?= htmlspecialchars($evenement['url_helloasso']) ?>"
                               target="_blank"
                               class="btn btn-helloasso"
                               rel="noopener noreferrer">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-helloasso">
                                    <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                                    <line x1="2" y1="10" x2="22" y2="10"></line>
                                </svg>
                                Lien HelloAsso afin de régler les participations de <span id="nb-personnes-helloasso">0</span> personne(s)
                            </a>
                        </div>
                        <?php endif; ?>

                        <div class="form-actions">
                            <button type="submit" class="btn-submit" id="btn-submit" disabled>
                                M'inscrire
                            </button>
                            <a href="<?= url('/membre/mes_inscriptions_asso') ?>" class="btn btn-secondary">Mes inscriptions</a>
                        </div>
                    </form>
                <?php else: ?>
                    <h2>Inscription impossible</h2>
                    <p>Les inscriptions sont closes pour cet événement.</p>
                    <div class="form-actions">
                        <a href="<?= url('/membre/mes_inscriptions_asso') ?>" class="btn btn-secondary">Mes inscriptions</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../gabarits/pied_de_page.php'; ?>
<script src="<?= asset('js/inscription_asso_dynamique.js') ?>?v=<?= time() ?>"></script>
</body>
</html>
