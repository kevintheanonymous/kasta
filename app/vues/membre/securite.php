<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sécurité - Kast'Asso</title>
    <link rel="stylesheet" href="<?= asset('css/variables.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/gabarit.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/tableau_de_bord_membre.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/profil_membre.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/formulaire-modular.css') ?>?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__ . '/../gabarits/barre_nav.php'; ?>

<main>
    <div class="container">
        <h1>Sécurité du compte</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <div class="profile-card">
            <h2>Modifier mon mot de passe</h2>
            <p class="description">Pour des raisons de sécurité, vous devez saisir votre mot de passe actuel avant de le modifier.</p>
            
            <form action="<?= url('/membre/securite/changer-mdp') ?>" method="POST" class="form-modular">
                <?= champCSRF() ?>
                
                <div class="form-group">
                    <label for="mdp_actuel">Mot de passe actuel *</label>
                    <input type="password" id="mdp_actuel" name="mdp_actuel" required>
                </div>
                
                <div class="form-group">
                    <label for="nouveau_mdp">Nouveau mot de passe *</label>
                    <input type="password" id="nouveau_mdp" name="nouveau_mdp" required>
                    <small>Minimum 8 caractères, avec majuscule, minuscule, chiffre et caractère spécial</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_mdp">Confirmer le nouveau mot de passe *</label>
                    <input type="password" id="confirm_mdp" name="confirm_mdp" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Modifier le mot de passe</button>
                </div>
            </form>
            
            <a href="<?= url('/membre/profil') ?>" class="back-link" style="margin-top: 1.5rem;">← Retour au profil</a>
        </div>
        
        <div class="security-info">
            <h3>Conseils de sécurité</h3>
            <ul>
                <li>Utilisez un mot de passe unique pour chaque service</li>
                <li>Ne partagez jamais votre mot de passe avec qui que ce soit</li>
                <li>Changez votre mot de passe régulièrement</li>
                <li>Utilisez un gestionnaire de mots de passe pour plus de sécurité</li>
            </ul>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../gabarits/pied_de_page.php'; ?>
</body>
</html>
