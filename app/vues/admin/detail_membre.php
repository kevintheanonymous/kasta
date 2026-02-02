<?php
$pageTitle = "Détail Membre - Kast'Asso";
require_once __DIR__ . '/../gabarits/en_tete.php';
require_once __DIR__ . '/../gabarits/barre_nav.php';
?>
<link rel="stylesheet" href="<?= asset('css/admin_detail_membre.css?v=' . time()) ?>">

    <main class="container detail-container">
        <h1>Détails du membre</h1>
        <p><a href="<?= url('/admin/membres') ?>" class="btn btn-secondary">← Retour à la liste</a></p>
        
        <?php
        if (isset($_SESSION['errors'])):
            foreach($_SESSION['errors'] as $error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endforeach;
            unset($_SESSION['errors']);
        endif;
        ?>
        
        <div class="detail-card">
            <h2><?= htmlspecialchars($membre['Prenom'] . ' ' . $membre['Nom']) ?></h2>
            
            <?php if ($membre['Url_Photo_Profil']): ?>
                <img src="<?= htmlspecialchars($membre['Url_Photo_Profil']) ?>" alt="Photo" class="detail-avatar">
            <?php endif; ?>
            
            <div class="grid-details">
                <div>
                    <p><strong>Statut du compte :</strong> 
                        <span class="badge <?= $membre['Statut_compte'] === 'valide' ? 'badge-success' : ($membre['Statut_compte'] === 'refuse' ? 'badge-danger' : 'badge-warning') ?>">
                            <?= htmlspecialchars($membre['Statut_compte']) ?>
                        </span>
                    </p>
                    <p><strong>Email :</strong> <?= htmlspecialchars($membre['Mail']) ?></p>
                    <p><strong>Téléphone :</strong> <?= htmlspecialchars($membre['Telephone']) ?></p>
                    <p><strong>Date d'inscription :</strong> <?= date('d/m/Y H:i', strtotime($membre['Date_statut_compte'])) ?></p>
                </div>
                <div class="right-column">
                    <p><strong>Taille T-shirt :</strong> <?= htmlspecialchars($membre['Taille_Teeshirt']) ?></p>
                    <p><strong>Taille Pull :</strong> <?= htmlspecialchars($membre['Taille_Pull']) ?></p>
                    <p><strong>Adhérent :</strong> <span class="adherent-inline"><span class="badge <?= $membre['Adherent'] ? 'badge-success' : 'badge-warning' ?>"><?= $membre['Adherent'] ? 'Oui' : 'Non' ?></span><?php if ($membre['Statut_compte'] === 'valide'): ?><form method="post" action="<?= url('/admin/membre/modifier-adherent') ?>" class="inline-form"><?= champCSRF() ?><input type="hidden" name="id_membre" value="<?= $membre['Id_Membre'] ?>"><?php if ($membre['Adherent']): ?><button type="submit" name="action" value="retirer" class="btn btn-sm btn-warning" onclick="return confirm('Retirer le statut adhérent de ce membre ?')" title="Retirer le statut adhérent">Retirer</button><?php else: ?><button type="submit" name="action" value="ajouter" class="btn btn-sm btn-success" onclick="return confirm('Rendre ce membre adhérent ?')" title="Rendre adhérent">Rendre adhérent</button><?php endif; ?></form><?php endif; ?></span></p>
                    <p><strong>Régime alimentaire :</strong>
                        <?php
                        $nomRegime = Membre::obtenirRegimeAlimentaire($membre['Id_Membre']);
                        echo $nomRegime ? htmlspecialchars($nomRegime) : 'Aucun';
                        ?>
                    </p>
                </div>
            </div>
            
            <?php if ($membre['Commentaire_Alimentaire']): ?>
                <div class="comment-box">
                    <p><strong>Commentaires alimentaires :</strong></p>
                    <p class="comment-content"><?= nl2br(htmlspecialchars($membre['Commentaire_Alimentaire'])) ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($membre['Statut_compte'] === 'en_attente'): ?>
                <hr class="separator">
                
                <h3>Actions de validation</h3>
                <div class="actions-container">
                    <!-- Formulaire de validation -->
                    <form method="post" action="<?= url('/admin/valider') ?>">
                        <?= champCSRF() ?>
                        <input type="hidden" name="id_membre" value="<?= $membre['Id_Membre'] ?>">
                        <button type="submit" class="btn btn-success" onclick="return confirm('Confirmer la validation de ce membre ?')">
                            Accepter l'inscription
                        </button>
                    </form>
                    
                    <!-- Formulaire de refus -->
                    <form method="post" action="<?= url('/admin/refuser') ?>" class="refus-form">
                        <?= champCSRF() ?>
                        <input type="hidden" name="id_membre" value="<?= $membre['Id_Membre'] ?>">
                        
                        <div class="form-group">
                            <label for="motif_refus">Motif du refus * :</label>
                            <textarea name="motif_refus" id="motif_refus" rows="3" required class="form-control refus-textarea" 
                                      placeholder="Expliquez pourquoi cette inscription est refusée..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Confirmer le refus de ce membre ?')">
                            Rejeter l'inscription
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php require_once __DIR__ . '/../gabarits/pied_de_page.php'; ?>