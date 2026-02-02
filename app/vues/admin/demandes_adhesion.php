<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demandes d'Adhésion - Kast'Asso</title>
    <link rel="stylesheet" href="<?= asset('css/variables.css?v=' . time()) ?>">
    <link rel="stylesheet" href="<?= asset('css/gabarit.css?v=' . time()) ?>">
    <link rel="stylesheet" href="<?= asset('css/tableau_de_bord_admin.css?v=' . time()) ?>">
    <link rel="stylesheet" href="<?= asset('css/demandes_adhesion.css?v=' . time()) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php
    require_once __DIR__ . '/../gabarits/barre_nav.php';
    // determiner le prefix d'URL selon le role
    $urlPrefix = ($_SESSION['user_type'] === 'gestionnaire') ? '/gestionnaire' : '/admin';
    $dashboardUrl = ($_SESSION['user_type'] === 'gestionnaire') ? '/gestionnaire/tableau_de_bord' : '/admin/tableau_de_bord';
    ?>

    <main>
        <h1>Demandes d'Adhésion en Attente</h1>
        <p>Gestion des demandes d'adhésion pour les membres validés</p>

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
            <a href="<?= url($dashboardUrl) ?>" class="back-link">
                ← Retour au tableau de bord
            </a>

            <?php if ($_SESSION['user_type'] === 'admin'): ?>
            <div class="quick-actions">
                <div class="actions-grid">
                    <a href="<?= url('/admin/membres') ?>" class="btn btn-info">Gérer les membres</a>
                </div>
            </div>
            <?php endif; ?>

            <div class="table-wrapper">
                <table class="table table-dashboard">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Date demande</th>
                            <th>Formulaire</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($demandesAdhesion) > 0):
                            foreach($demandesAdhesion as $demande):
                                $compteEnAttente = ($demande['Statut_compte'] === 'en_attente');
                                $compteValide = ($demande['Statut_compte'] === 'valide');
                            ?>
                                <tr>
                                    <td data-label="Nom">
                                        <?= htmlspecialchars($demande['Nom']) ?>
                                        <?php if ($compteEnAttente): ?>
                                            <span class="badge badge-warning">Compte en attente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Prénom"><?= htmlspecialchars($demande['Prenom']) ?></td>
                                    <td data-label="Email"><?= htmlspecialchars($demande['Mail']) ?></td>
                                    <td data-label="Date demande"><?= $demande['Date_demande_adhesion'] ? date('d/m/Y H:i', strtotime($demande['Date_demande_adhesion'])) : 'N/A' ?></td>
                                    <td data-label="Formulaire">
                                        <?php if (!empty($demande['Url_Adhesion'])): ?>
                                            <a href="<?= url('/documents/visualiser-adhesion?file=' . urlencode(basename($demande['Url_Adhesion']))) ?>" target="_blank" class="btn btn-sm btn-info">
                                                📄 Visualiser
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Aucun fichier</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Actions">
                                        <?php if ($compteEnAttente): ?>
                                            <div class="compte-bloque-info">
                                                <small>⚠️ Validez d'abord le compte</small>
                                            </div>
                                        <?php else: ?>
                                            <div class="flex-gap-5">
                                                <form method="post" action="<?= url($urlPrefix . '/adhesion/accepter') ?>">
                                                    <?= champCSRF() ?>
                                                    <input type="hidden" name="id_membre" value="<?= $demande['Id_Membre'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-success">Accepter</button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="ouvrirModalRefusAdhesion(<?= $demande['Id_Membre'] ?>, '<?= htmlspecialchars($demande['Prenom'] . ' ' . $demande['Nom'], ENT_QUOTES) ?>')">Refuser</button>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Aucune demande d'adhésion en attente</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="modalRefusAdhesion" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fermerModalRefusAdhesion()">&times;</span>
            <h2>Refuser la demande d'adhésion</h2>
            <p>Êtes-vous sûr de vouloir refuser la demande d'adhésion de <strong id="nomMembreRefusAdhesion"></strong> ?</p>

            <form method="post" action="<?= url($urlPrefix . '/adhesion/refuser') ?>" id="formRefusAdhesion">
                <?= champCSRF() ?>
                <input type="hidden" name="id_membre" id="idMembreRefusAdhesion">

                <label for="motif_refus_adhesion">Motif du refus <span class="required">*</span> :</label>
                <textarea name="motif_refus" id="motif_refus_adhesion" rows="4" cols="50" required placeholder="Expliquez pourquoi vous refusez cette adhésion..."></textarea>

                <div class="flex-gap-5 modal-buttons">
                    <button type="button" class="btn btn-secondary" onclick="fermerModalRefusAdhesion()">Annuler</button>
                    <button type="submit" class="btn btn-danger">Confirmer le refus</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ouvrir modal refus adhesion
        function ouvrirModalRefusAdhesion(idMembre, nomMembre) {
            document.getElementById('modalRefusAdhesion').style.display = 'block';
            document.getElementById('idMembreRefusAdhesion').value = idMembre;
            document.getElementById('nomMembreRefusAdhesion').textContent = nomMembre;
        }

        // fermer modal refus adhesion
        function fermerModalRefusAdhesion() {
            document.getElementById('modalRefusAdhesion').style.display = 'none';
            document.getElementById('motif_refus_adhesion').value = '';
        }

        // fermer modal si clic en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('modalRefusAdhesion');
            if (event.target === modal) {
                fermerModalRefusAdhesion();
            }
        }
    </script>

    <?php require_once __DIR__ . '/../gabarits/pied_de_page.php'; ?>
</body>
</html>