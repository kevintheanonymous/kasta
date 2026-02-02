<?php
$pageTitle = "Réinitialisation du mot de passe";
require __DIR__ . '/../gabarits/en_tete.php';
require __DIR__ . '/../gabarits/barre_nav.php';
?>

<link rel="stylesheet" href="<?= asset('css/formulaire-modular.css') ?>">

<main>
    <div class="auth-wrapper">
        <div class="auth-container">
            <h1>RÉINITIALISATION DU MOT DE PASSE</h1>
            
            <?php if (isset($_SESSION['errors'])): ?>
                <div class="alert alert-danger">
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                    <?php unset($_SESSION['errors']); ?>
                </div>
            <?php endif; ?>

            <form action="<?= url('/reinitialisation_mdp') ?>" method="POST" class="auth-form">
                <?= champCSRF() ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
                
                <label>Nouveau mot de passe :
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required minlength="8">
                </label>

                <label>Confirmer le mot de passe :
                    <input type="password" id="confirmer_mdp" name="confirmer_mdp" required minlength="8">
                </label>

                <input type="submit" value="Réinitialiser le mot de passe">
            </form>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../gabarits/pied_de_page.php'; ?>
