<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Kast'Asso</title>
    <link rel="stylesheet" href="<?= asset('css/variables.css?v=' . time()) ?>">
    <link rel="stylesheet" href="<?= asset('css/gabarit.css?v=' . time()) ?>">
    <link rel="stylesheet" href="<?= asset('css/tableau_de_bord_admin.css?v=' . time()) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php require_once __DIR__ . '/../gabarits/barre_nav.php'; ?>

    <main>
        <h1>Tableau de Gestion</h1>
        <p>Bienvenue <?= htmlspecialchars($_SESSION['user_name'] ?? 'Administrateur') ?></p>
        
        <?php
        if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']);
        endif;
        
        if (isset($_SESSION['errors'])):
            foreach($_SESSION['errors'] as $error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endforeach;
            unset($_SESSION['errors']);
        endif;
        ?>

         <div class="admin-container">
            
            <h2 class="section-title">Inscriptions en attente</h2>
            
            <?php if (count($membresEnAttente) > 0): ?>
            <div class="table-wrapper">
                <table class="table table-dashboard">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Date demande</th>
                            <th>Adhérent</th>
                            <th>Action</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($membresEnAttente as $membre): ?>
                                <tr>
                                    <td data-label="Nom"><?= htmlspecialchars($membre['Nom']) ?></td>
                                    <td data-label="Prénom"><?= htmlspecialchars($membre['Prenom']) ?></td>
                                    <td data-label="Email"><?= htmlspecialchars($membre['Mail']) ?></td>
                                    <td data-label="Téléphone"><?= htmlspecialchars($membre['Telephone']) ?></td>
                                    <td data-label="Date"><?= date('d/m/Y H:i', strtotime($membre['Date_statut_compte'])) ?></td>
                                    <td data-label="Adhérent"><?= $membre['Adherent'] ? 'Oui' : 'Non' ?></td>
                                    <td data-label="Actions">
                                        <div class="flex-gap-5">
                                            <form method="post" action="<?= url('/admin/valider') ?>">
                                                <?= champCSRF() ?>
                                                <input type="hidden" name="id_membre" value="<?= $membre['Id_Membre'] ?>">
                                                <button type="submit" name="action" value="accepter" class="btn btn-sm btn-success">Accepter</button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="ouvrirModalRefus(<?= $membre['Id_Membre'] ?>, '<?= htmlspecialchars($membre['Prenom'] . ' ' . $membre['Nom'], ENT_QUOTES) ?>')">Refuser</button>
                                        </div>
                                    </td>
                                    <td data-label="Statut">
                                        <span class="badge badge-warning">En attente</span>
                                    </td>
                                </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <p>Aucune inscription en attente</p>
            </div>
            <?php endif; ?>

            <section class="section">
                <h2 class="section-title">Aperçu des Événements</h2>
                
                <div class="admin-events-list">
                    <p class="events-summary">
                        Il y a actuellement <strong><?= $countSport ?></strong> événements sportifs et <strong><?= $countAsso ?></strong> événements associatifs actifs.
                    </p>
                    <a href="<?= url('/admin/events') ?>" class="btn btn-primary">Voir tous les événements</a>
                </div>
            </section>

            <section class="section">
                <h2 class="section-title">Actions rapides</h2>
                <div class="actions-grid">
                    <a href="<?= url('/admin/postes') ?>" class="btn btn-secondary">Gérer les postes</a>
                    <a href="<?= url('/admin/regimes-alimentaires') ?>" class="btn btn-secondary">Gérer les régimes alimentaires</a>
                    <a href="<?= url('/admin/membres') ?>" class="btn btn-secondary">Gérer les membres</a>
                    <a href="<?= url('/admin/categories') ?>" class="btn btn-secondary">Gérer les catégories</a>
                    <a href="<?= url('/admin/template-adhesion') ?>" class="btn btn-secondary">Template adhésion</a>
                </div>
            </section>
        </div>
    </main>

    <div id="modalRefus" class="modal">
        <div class="modal-content">
            <h3>Refuser l'inscription</h3>
            <p id="modalMembreNom"></p>
            
            <form method="post" action="<?= url('/admin/refuser') ?>" id="formRefus">
                <?= champCSRF() ?>
                <input type="hidden" name="id_membre" id="modalIdMembre" value="">
                
                <div class="modal-form-group">
                    <label for="motif_refus">Motif du refus <span class="required">*</span></label>
                    <textarea name="motif_refus" id="motif_refus" rows="4" required 
                              placeholder="Expliquez pourquoi cette inscription est refusée..."></textarea>
                    <small>Ce message sera envoyé par email au membre.</small>
                </div>
                
                <div class="modal-actions">
                    <button type="button" onclick="fermerModalRefus()" class="btn btn-secondary">Annuler</button>
                    <button type="submit" class="btn btn-danger">Confirmer le refus</button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= asset('js/admin_modal.js') ?>"></script>

    <?php require_once __DIR__ . '/../gabarits/pied_de_page.php'; ?>
</body>
</html>