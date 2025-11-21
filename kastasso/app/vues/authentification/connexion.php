<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Kast'Asso</title>
    <link rel="stylesheet" href="/kastasso/public/css/formulaire.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>    
    <h1>CONNEXION</h1>
    
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
    
    <form action="/kastasso/public/index.php?path=/connexion" method="post">
        <label>Adresse e-mail :
            <input type="email" autocomplete = "email" id="email" name="email" required>
            <span id="message_email"></span>
        </label>
        
        <label>Mot de passe :
            <input type="password" id="mdp" name="mot_de_passe" required>
            <button type="button" id="voirmdp">👀️</button>
            <span id="message_mdp"></span>
        </label>
        
        <input type="submit" value="Suivant">
        <a href="/kastasso/public/index.php?path=/inscription">Pas encore de compte ? S'inscrire</a>
    </form>
    
    <script>
    // Visibilité mot de passe
    document.getElementById('voirmdp').addEventListener('click', function() {
        const mdp = document.getElementById('mdp');
        if (mdp.type === "password") {
            mdp.type = "text";
            this.textContent = '🚫️';
        } else {
            mdp.type = "password";
            this.textContent = '👀️';
        }
    });
    </script>
</body>
</html>