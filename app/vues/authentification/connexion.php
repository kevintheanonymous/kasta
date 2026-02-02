<?php
$pageTitle = "Connexion - Kast'Asso";

$hide_navbar = true; 
require_once __DIR__ . '/../gabarits/en_tete.php';
?>

<link rel="stylesheet" href="<?= asset('css/formulaires.css') ?>?v=<?= time() ?>">

<div class="auth-page">
    <div class="auth-container">
        
        <div class="auth-left">
            <a href="<?= url('/') ?>" class="auth-logo">KAST'ASSO</a>
        </div>

        <div class="auth-right">
            <h2>Connexion</h2>
            <p class="auth-subtitle">Connectez-vous pour accéder à votre espace.</p>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['errors'])): ?>
                <div class="alert alert-danger">
                    <?php foreach($_SESSION['errors'] as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>

            <form action="<?= url('/connexion') ?>" method="post" class="auth-form">
                <?= champCSRF() ?>
                
                <div class="form-group">
                    <label for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email" class="form-input" 
                           autocomplete="email" required placeholder="nomprenom@gmail.com">
                    <span id="message_email"></span>
                </div>

                <div class="form-group">
                    <label for="mdp">Mot de passe</label>
                    <div class="password-wrapper">
                        <input type="password" id="mdp" name="mot_de_passe" class="form-input" 
                               required placeholder="Votre mot de passe">
                        
                        <button type="button" class="password-toggle-btn" onclick="togglePassword()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    <span id="message_mdp"></span>
                </div>

                <div class="form-options">
                    <label class="checkbox-container">
                        <input type="checkbox" name="remember">
                        <span>Se souvenir de moi</span>
                    </label>
                    <a href="<?= url('/mot_de_passe_oublie') ?>">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="btn-submit">Se connecter</button>
            </form>

            <div class="auth-footer">
                Pas encore de compte ? <a href="<?= url('/inscription') ?>">S'inscrire</a>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('js/auth.js') ?>"></script>

<?php require_once __DIR__ . '/../gabarits/pied_de_page.php'; ?>