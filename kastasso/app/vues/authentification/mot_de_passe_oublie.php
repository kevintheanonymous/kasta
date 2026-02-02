<?php
$pageTitle = "Mot de passe oublié - Kast'Asso";
$hide_navbar = true;
require_once __DIR__ . '/../gabarits/en_tete.php';
?>

<link rel="stylesheet" href="<?= asset('css/formulaires.css') ?>?v=<?= time() ?>">

<div class="auth-page">
    <div class="auth-container auth-container-single">
        <div class="auth-right">
            <a href="<?= url('/') ?>" class="auth-back-home">← Retour à l'accueil</a>
            <h2>Mot de passe oublié</h2>
            <p class="auth-subtitle">Entrez votre adresse email pour recevoir un lien de réinitialisation.</p>
            
            <?php if (isset($_SESSION['errors'])): ?>
                <div class="alert alert-danger">
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <p style="margin: 0;"><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                    <?php unset($_SESSION['errors']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <form action="<?= url('/mot_de_passe_oublie') ?>" method="POST" class="auth-form">
                <?= champCSRF() ?>
                
                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" class="form-input" placeholder="exemple@email.com" required>
                </div>

                <button type="submit" class="btn-submit">Envoyer le lien</button>
            </form>
            
            <div class="auth-footer">
                <a href="<?= url('/connexion') ?>">← Retour à la connexion</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../gabarits/pied_de_page.php'; ?>

