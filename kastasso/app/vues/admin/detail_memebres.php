<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail Membre - Kast'Asso</title>
    <link rel="stylesheet" href="/public/css/formulaire.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <h1>Détails de l'inscription</h1>
    <p><a href="/admin/dashboard">← Retour au dashboard</a></p>
    
    <?php
    if (isset($_SESSION['errors'])):
        foreach($_SESSION['errors'] as $error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endforeach;
        unset($_SESSION['errors']);
    endif;
    ?>
    
    <div class="member-details">
        <h2><?= htmlspecialchars($membre['prenom'] . ' ' . $membre['nom']) ?></h2>
        
        <?php if ($membre['url_photo']): ?>
            <img src="<?= htmlspecialchars($membre['url_photo']) ?>" alt="Photo" class="avatar">
        <?php endif; ?>
        
        <p><strong>Email :</strong> <?= htmlspecialchars($membre['mail']) ?></p>
        <p><strong>Téléphone :</strong> <?= htmlspecialchars($membre['telephone']) ?></p>
        <p><strong>Taille T-shirt :</strong> <?= htmlspecialchars($membre['taille_teeshirt']) ?></p>
        <p><strong>Taille Pull :</strong> <?= htmlspecialchars($membre['taille_pull']) ?></p>
        <p><strong>Adhérent :</strong> <?= $membre['adherent'] ? 'Oui' : 'Non' ?></p>
        
        <?php if ($membre['commentaire_alim']): ?>
            <p><strong>Commentaires alimentaires :</strong><br>
            <?= nl2br(htmlspecialchars($membre['commentaire_alim'])) ?></p>
        <?php endif; ?>
        
        <p><strong>Date d'inscription :</strong> <?= date('d/m/Y H:i', strtotime($membre['date_statut_compte'])) ?></p>
        
        <hr>
        
        <h3>Actions</h3>
        
        <!-- Formulaire de validation -->
        <form method="post" action="/admin/valider" style="display: inline-block; margin-right: 20px;">
            <input type="hidden" name="id_membre" value="<?= $membre['id_membre'] ?>">
            <button id="accepter" type="submit" onclick="return confirm('Confirmer la validation de ce membre ?')">
                Accepter
            </button>
        </form>
        
        <!-- Formulaire de refus -->
        <form method="post" action="/admin/refuser" id="formRefus">
            <input type="hidden" name="id_membre" value="<?= $membre['id_membre'] ?>">
            
            <label>Motif du refus * :
                <textarea name="motif_refus" rows="4" required 
                          placeholder="Expliquez pourquoi cette inscription est refusée..."></textarea>
            </label>
            
            <button id="supprimer" type="submit" onclick="return confirm('Confirmer le refus de ce membre ?')">
                Rejeter
            </button>
        </form>
        
    </div>
</body>
</html>