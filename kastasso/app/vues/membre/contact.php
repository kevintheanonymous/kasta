<?php require __DIR__ . '/../gabarits/en_tete.php'; ?>
<link rel="stylesheet" href="<?= asset('css/contact.css') ?>?v=<?= time() ?>">
<?php require __DIR__ . '/../gabarits/barre_nav.php'; ?>

<main class="main-content contact-page">
    <div class="container">
        <h1>Nous contacter</h1>
        
        <div class="contact-card">
            
            <?php if (isset($_SESSION['errors'])): ?>
                <div class="alert alert-danger">
                    <div>
                        <?php foreach ($_SESSION['errors'] as $error): ?>
                            <p style="margin: 0;"><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; unset($_SESSION['errors']); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <span><?= htmlspecialchars($_SESSION['success']) ?></span>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <p class="contact-intro">
               Une question sur vos entraînements ou votre adhésion ?<br>
               <strong>Envoyez-nous un message directement via ce formulaire.</strong>
            </p>

            <?php 
            // Déterminer l'URL du formulaire selon la connexion
            $formAction = isset($_SESSION['user']) ? url('/membre/contact/send') : url('/contact/send');
            // Récupérer l'email pré-rempli
            $defaultEmail = '';
            if (isset($_SESSION['form_data']['email'])) {
                $defaultEmail = $_SESSION['form_data']['email'];
            } elseif (isset($_SESSION['user']['mail'])) {
                $defaultEmail = $_SESSION['user']['mail'];
            }
            $defaultSujet = $_SESSION['form_data']['sujet'] ?? '';
            $defaultMessage = $_SESSION['form_data']['message'] ?? '';
            unset($_SESSION['form_data']);
            ?>

            <form action="<?= $formAction ?>" method="POST" class="contact-form" id="contactForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="form-group">
                    <label for="email">
                        Votre adresse email
                    </label>
                    <input type="email" id="email" name="email" required class="form-control" 
                           placeholder="exemple@email.com" 
                           value="<?= htmlspecialchars($defaultEmail) ?>">
                    <span class="error-message" id="email-error"></span>
                </div>

                <div class="form-group">
                    <label for="sujet">
                        Sujet
                    </label>
                    <select id="sujet" name="sujet" required class="form-control">
                        <option value=""> Choisir un sujet </option>
                        <option value="Question générale" <?= $defaultSujet === 'Question générale' ? 'selected' : '' ?>>Question générale</option>
                        <option value="Problème technique" <?= $defaultSujet === 'Problème technique' ? 'selected' : '' ?>>Problème technique / Site web</option>
                        <option value="Adhésion / Cotisation" <?= $defaultSujet === 'Adhésion / Cotisation' ? 'selected' : '' ?>>Adhésion / Cotisation</option>
                        <option value="Événement" <?= $defaultSujet === 'Événement' ? 'selected' : '' ?>>Événement</option>
                        <option value="Autre" <?= $defaultSujet === 'Autre' ? 'selected' : '' ?>>Autre</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="message">
                        
                        Votre message
                    </label>
                    <textarea id="message" name="message" rows="8" required class="form-control" placeholder="Décrivez votre demande en détail..."><?= htmlspecialchars($defaultMessage) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <span>Envoyer le message</span>
                    <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</main>

<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('email-error');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    emailError.textContent = '';
    emailInput.classList.remove('input-error');
    
    if (!emailRegex.test(emailInput.value)) {
        e.preventDefault();
        emailError.textContent = "Veuillez entrer une adresse email valide.";
        emailInput.classList.add('input-error');
        emailInput.focus();
        return false;
    }
});

// Validation en temps réel
document.getElementById('email').addEventListener('blur', function() {
    const emailError = document.getElementById('email-error');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (this.value && !emailRegex.test(this.value)) {
        emailError.textContent = "Veuillez entrer une adresse email valide.";
        this.classList.add('input-error');
    } else {
        emailError.textContent = '';
        this.classList.remove('input-error');
    }
});
</script>

<?php require __DIR__ . '/../gabarits/pied_de_page.php'; ?>