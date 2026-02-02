<?php
// La variable $membre est transmise par le contrôleur
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Kast'Asso</title>
    <link rel="stylesheet" href="<?= asset('css/variables.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/gabarit.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/tableau_de_bord_membre.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/profil_membre.css') ?>?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__ . '/../gabarits/barre_nav.php'; ?>

<main>
    <div class="container">
        <h1>Mon Profil</h1>
        
        <div class="profile-card">
            <div class="profile-header">
                <?php 
                $photoUrl = !empty($membre['Url_Photo_Profil']) ? asset($membre['Url_Photo_Profil']) : asset('img/avatar.jpg');
                ?>
                <img src="<?= $photoUrl ?>" alt="Photo de profil">
            </div>
            <div class="profile-info">
                <p><strong>Nom :</strong> <?= htmlspecialchars($membre['Nom']) ?></p>
                <p><strong>Prénom :</strong> <?= htmlspecialchars($membre['Prenom']) ?></p>
                <p><strong>Sexe :</strong> <?= ($membre['Sexe'] === 'H') ? 'Homme' : (($membre['Sexe'] === 'F') ? 'Femme' : 'Non renseigné') ?></p>
                <p><strong>Email :</strong> <?= htmlspecialchars($membre['Mail']) ?></p>
                <p><strong>Téléphone :</strong> <?= htmlspecialchars($membre['Telephone']) ?></p>
                
                <hr>
                
                <h3>Informations Complémentaires</h3>
                <p><strong>Taille T-shirt :</strong> <?= htmlspecialchars($membre['Taille_Teeshirt'] ?: 'Non renseigné') ?></p>
                <p><strong>Taille Pull :</strong> <?= htmlspecialchars($membre['Taille_Pull'] ?: 'Non renseigné') ?></p>
                <p><strong>Régime Alimentaire :</strong>
                    <?php
                    $nomRegime = Membre::obtenirRegimeAlimentaire($membre['Id_Membre']);
                    echo $nomRegime ? htmlspecialchars($nomRegime) : 'Aucun';
                    ?>
                </p>
                <p><strong>Commentaires Alimentaires :</strong> <?= htmlspecialchars($membre['Commentaire_Alimentaire'] ?: 'Aucun') ?></p>

                <div class="adhesion-section">
                    <?php
                    $statutAdhesion = $membre['Statut_adhesion'] ?? '';
                    $estAdherent = !empty($membre['Adherent']);
                    ?>

                    <?php if ($estAdherent): ?>
                        <p class="adhesion-status-ok">
                            <span class="icon">Adhérent</span> Vous êtes adhérent de l'association
                        </p>
                    <?php elseif ($statutAdhesion === 'en_attente'): ?>
                        <p class="adhesion-status-pending">
                            Votre demande d'adhésion est en cours de traitement
                        </p>
                    <?php else: ?>
                        <p class="adhesion-info-text">Vous n'êtes pas adhérent.</p>

                        <p class="adhesion-description">
                            Pour devenir adhérent, téléchargez le formulaire d'adhésion, remplissez-le et déposez-le ici.
                        </p>

                        <div class="adhesion-actions">
                            <a href="<?= url('/documents/formulaire-adhesion') ?>" class="btn-download-adhesion">
                                Télécharger le formulaire d'adhésion
                            </a>

                            <button type="button" id="btn-deposer-adhesion" class="btn-deposer-adhesion">
                                Déposer mon formulaire
                            </button>

                            <button type="button" id="btn-soumettre-adhesion" class="btn-soumettre-adhesion" disabled>
                                Soumettre ma demande
                            </button>

                            <form action="<?= url('/membre/soumettre-adhesion') ?>" method="POST" enctype="multipart/form-data" id="form-adhesion" style="display: none;">
                                <?= champCSRF() ?>
                                <input type="file"
                                       name="formulaire_adhesion"
                                       id="formulaire-adhesion"
                                       accept=".pdf,.jpg,.jpeg"
                                       class="file-input-hidden">
                            </form>

                            <div id="file-status" class="file-status"></div>
                        </div>

                        <p class="adhesion-notice">
                            <strong>⚠️ Attention :</strong> En tant qu'adhérent, vous serez couvert par l'assurance de l'association en cas de problème survenant lors d'un événement organisé.
                        </p>
                    <?php endif; ?>
                </div>
                
                <hr class="large-margin">
                
                <div class="account-settings">
                    <h3><span class="icon">⚙️</span> Paramètres du compte</h3>
                    
                    <div class="settings-section">
                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Informations personnelles</h4>
                                <p>Modifier votre nom, email, téléphone et autres informations</p>
                            </div>
                            <a href="<?= url('/membre/profil/edit') ?>" class="btn-setting">Modifier</a>
                        </div>
                        
                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Sécurité</h4>
                                <p>Changer votre mot de passe</p>
                            </div>
                            <a href="<?= url('/membre/securite') ?>" class="btn-setting">Modifier</a>
                        </div>
                    </div>
                </div>
                
                <div class="danger-zone">
                    <div class="danger-zone-header">
                        <span class="danger-icon">⚠️</span>
                        <h3>Zone sensible</h3>
                    </div>
                    <p class="danger-zone-description">
                        La suppression de votre compte est définitive et irréversible. Toutes vos données seront effacées.
                    </p>
                    
                    <?php if (!empty($membre['Gestionnaire'])): ?>
                        <div class="gestionnaire-notice">
                            <span class="notice-icon">ℹ️</span>
                            <p>En tant que gestionnaire, veuillez contacter un administrateur pour supprimer votre compte.</p>
                        </div>
                        <button class="btn-danger-outline" disabled>Supprimer mon compte</button>
                    <?php else: ?>
                        <form action="<?= url('/membre/supprimer') ?>" method="POST" id="delete-account-form">
                            <?= champCSRF() ?>
                            <button type="button" class="btn-danger-outline" id="btn-delete-account">Supprimer mon compte</button>
                        </form>
                        
                        <div class="delete-confirmation" id="delete-confirmation" style="display: none;">
                            <p class="confirm-text">Êtes-vous absolument sûr ? Cette action est <strong>irréversible</strong>.</p>
                            <div class="confirm-actions">
                                <button type="button" class="btn-cancel" id="btn-cancel-delete">Annuler</button>
                                <button type="button" class="btn-danger-solid" id="btn-confirm-delete">Oui, supprimer mon compte</button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const btnDelete = document.getElementById('btn-delete-account');
                    const confirmation = document.getElementById('delete-confirmation');
                    const btnCancel = document.getElementById('btn-cancel-delete');
                    const btnConfirm = document.getElementById('btn-confirm-delete');
                    const form = document.getElementById('delete-account-form');
                    
                    if (btnDelete) {
                        btnDelete.addEventListener('click', function() {
                            this.style.display = 'none';
                            confirmation.style.display = 'block';
                        });
                    }
                    
                    if (btnCancel) {
                        btnCancel.addEventListener('click', function() {
                            confirmation.style.display = 'none';
                            btnDelete.style.display = 'inline-flex';
                        });
                    }
                    
                    if (btnConfirm) {
                        btnConfirm.addEventListener('click', function() {
                            form.submit();
                        });
                    }
                });
                </script>
                

            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../gabarits/pied_de_page.php'; ?>
<script src="<?= asset('js/profil_adhesion.js') ?>"></script>
</body>
</html>
